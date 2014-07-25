<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\ExtensionLibraryModule\Manager;

use CarlosIO\Jenkins\Build;
use CarlosIO\Jenkins\Job;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Github\HttpClient\Message\ResponseMediator;
use Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity;
use Zikula\Module\ExtensionLibraryModule\Util;


/**
 * Class ReleaseManager.
 */
class ReleaseManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $repo;

    private $client;

    private $jenkinsClient;

    public function __construct($em)
    {
        $this->client = Util::getGitHubClient();
        $this->jenkinsClient = Util::getJenkinsClient();
        $this->em = $em;
        $this->repo = \ModUtil::getVar('ZikulaExtensionLibraryModule', 'github_core_repo', 'zikula/core');
    }

    public function reloadAllReleases($includeJenkinsBuilds = false)
    {
        // GitHub releases
        $repo = explode('/', $this->repo);
        $releases = $this->client->api('repo')->releases()->all($repo[0], $repo[1]);
        /** @var CoreReleaseEntity[] $dbReleases */
        $_dbReleases = $this->em->getRepository('Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity')->findAll();
        $dbReleases = array();
        foreach ($_dbReleases as $_dbRelease) {
            $dbReleases[$_dbRelease->getId()] = $_dbRelease;
        }
        unset($_dbReleases, $_dbRelease);

        // Make sure to always have at least the id "0" in the array, as the IN() SQL statement fails otherwise.
        $ids = array(0);
        foreach ($releases as $release) {
            $ids[] = $release['id'];
            $this->updateRelease($release, $dbReleases);
        }

        /** @var QueryBuilder $qb */
        $qb = $this->em->createQueryBuilder();
        $removedReleases = $qb->select('r')
            ->from('ZikulaExtensionLibraryModule:CoreReleaseEntity', 'r')
            ->where($qb->expr()->not($qb->expr()->in('r.id', implode(', ', $ids))))
            ->getQuery()->execute();

        foreach ($removedReleases as $removedRelease) {
            $this->em->remove($removedRelease);
        }

        $this->em->flush();

        // Jenkins builds
        if ($includeJenkinsBuilds && $this->jenkinsClient) {
            $oldJenkinsBuilds = $this->em->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findBy(array('status' => CoreReleaseEntity::STATE_DEVELOPMENT));
            foreach ($oldJenkinsBuilds as $oldJenkinsBuild) {
                $this->em->remove($oldJenkinsBuild);
            }
            $this->em->flush();

            /** @var Job $job */
            foreach ($this->jenkinsClient->getJobs() as $job) {
                if (!$job->isDisabled()) {
                    $name = $job->getName();
                    if (!preg_match('#Zikula(_Core|)-([0-9]\.[0-9]\.[0-9])#', $name, $matches)) {
                        continue;
                    }
                    $version = $matches[2];

                    /** @var Build[] $builds */
                    $builds = $job->getBuilds();
                    foreach ($builds as $key => $build) {
                        if ($build->isBuilding() || $build->getResult() != "SUCCESS") {
                            unset($builds[$key]);
                        }
                    }
                    usort($builds, function (Build $a, Build $b) {
                        $a = $a->getNumber();
                        $b = $b->getNumber();
                        if ($a === $b) {
                            return 0;
                        }

                        return ($a > $b) ? -1 : 1;
                    });

                    $build = $builds[0];

                    $jenkinsBuild = new CoreReleaseEntity($job->getName() . '#' . $build->getNumber());
                    $jenkinsBuild->setName($job->getDisplayName() . '#' . $build->getNumber());
                    $jenkinsBuild->setStatus(CoreReleaseEntity::STATE_DEVELOPMENT);
                    $jenkinsBuild->setSemver($version);

                    $description = $job->getDescription() ? $job->getDescription() : $job->getDisplayName();
                    $changeSet = $build->getChangeSet()->toArray();
                    if ($changeSet['kind'] == 'git' && !empty($changeSet['items'][0]->msg)) {
                        $description .= "<br /><br />" . $this->markdown($changeSet['items'][0]->msg);
                    }
                    $jenkinsBuild->setDescription($description);

                    $assets = array();
                    $server = \ModUtil::getVar('ZikulaExtensionLibraryModule', 'jenkins_server');
                    foreach ($build->getArtifacts() as $artifact) {
                        $downloadUrl = $server . '/job/' . urlencode($job->getName()) . '/' . $build->getNumber() . '/artifact/' . $artifact->relativePath;
                        $assets[] = array (
                            'name' => $artifact->fileName,
                            'download_url' => $downloadUrl,
                            'size' => null,
                            'content_type' => null
                        );
                    }
                    $jenkinsBuild->setAssets($assets);

                    $sourceUrls = array();
                    if ($changeSet['kind'] == 'git') {
                        $sha = $changeSet['items'][0]->commitId;
                        $sourceUrls['zip'] = 'https://github.com/' . urlencode($this->repo) . "/archive/$sha.zip";
                        $sourceUrls['tar'] = 'https://github.com/' . urlencode($this->repo) . "/archive/$sha.tar";
                    }
                    $jenkinsBuild->setSourceUrls($sourceUrls);

                    $this->em->persist($jenkinsBuild);
                }
            }

            $this->em->flush();
        }

        return true;
    }

    /**
     * Update or add one specific release.
     *
     * @param array               $release    The release data from the GitHub api.
     * @param CoreReleaseEntity[] $dbReleases INTERNAL: used in self::reloadAllReleases()
     *
     * @return bool
     */
    public function updateRelease($release, $dbReleases = null)
    {
        if ($release['draft']) {
            // Ignore drafts.
            return true;
        }
        $id = $release['id'];

        if ($release['prerelease']) {
            $status = CoreReleaseEntity::STATE_PRERELEASE;
        } else {
            $status = CoreReleaseEntity::STATE_SUPPORTED;
        }

        if ($dbReleases === null) {
            $dbReleases = $this->em->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findOneBy(array('id' => $id));
            if ($dbReleases) {
                $dbReleases[$id] = $dbReleases;
            } else {
                $dbReleases = array();
            }
        }

        if (!array_key_exists($id, $dbReleases)) {
            // This is a new release.
            $dbRelease = new CoreReleaseEntity($id);
            $mode = 'new';
        } else {
            $dbRelease = $dbReleases[$id];
            $mode = 'edit';
            if ($dbRelease->getStatus() === CoreReleaseEntity::STATE_OUTDATED) {
                // Make sure not to override the status if it has been set to "outdated".
                $status = CoreReleaseEntity::STATE_OUTDATED;
            }
        }

        $dbRelease->setName($release['name']);
        $dbRelease->setDescription($this->markdown($release['body']));
        $dbRelease->setSemver($release['tag_name']);
        $dbRelease->setSourceUrls(array (
            'zip' => $release['zipball_url'],
            'tar' => $release['tarball_url']
        ));
        $dbRelease->setStatus($status);

        $assets = array();
        $htmlUrl = $release['html_url'];
        $downloadUrl = str_replace('releases/tag', 'releases/download', $htmlUrl) . '/';
        foreach ($release['assets'] as $asset) {
            if ($asset['state'] != 'uploaded') {
                continue;
            }
            $assets[] = array (
                'name' => $asset['name'],
                'download_url' => $downloadUrl . $asset['name'],
                'size' => $asset['size'],
                'content_type' => $asset['content_type']
            );
        }
        $dbRelease->setAssets($assets);

        if ($mode == 'new') {
            $this->em->persist($dbRelease);
        } else {
            $this->em->merge($dbRelease);
        }


        $this->em->flush();

        return true;
    }

    private function markdown($body)
    {
        $settings = array(
            'text' => $body,
            'mode' => 'gfm',
            'context' => $this->repo
        );

        $response = $this->client->getHttpClient()->post('markdown', json_encode($settings));

        return ResponseMediator::getContent($response);
    }
}
