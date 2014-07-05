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

namespace Zikula\Module\ExtensionLibraryModule;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Github\HttpClient\Message\ResponseMediator;
use Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity;


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

    public function __construct($em)
    {
        $this->client = Util::getGitHubClient();
        $this->em = $em;
        $this->repo = \ModUtil::getVar('ZikulaExtensionLibraryModule', 'github_core_repo', 'zikula/core');
    }

    public function reloadAllReleases()
    {
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
