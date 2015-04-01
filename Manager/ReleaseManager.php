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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use vierbergenlars\SemVer\version;
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

    private $router;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param                 $em
     * @param RouterInterface $router
     */
    public function __construct($em, RouterInterface $router, EventDispatcherInterface $eventDispatcher)
    {
        $this->client = Util::getGitHubClient();
        $this->jenkinsClient = Util::getJenkinsClient();
        $this->em = $em;
        $this->repo = \ModUtil::getVar('ZikulaExtensionLibraryModule', 'github_core_repo', 'zikula/core');
        $this->dom = \ZLanguage::getModuleDomain('ZikulaExtensionLibraryModule');
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * This returns "significant" releases only. They are sorted by (1) state ASC and (2) version DESC.
     *
     * Example given there is
     * - a prerelease 1.3.5-rc1
     * - an outdated release 1.3.5
     *
     * Only the outdated release will be returned as it "overweights" the prerelease.
     */
    public function getSignificantReleases($onlyNewestVersion = true)
    {
        // Get all the releases.
        $releases = $this->em->getRepository('Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity')->findAll();

        // Create a version map. This makes it possible to check what kind of releases are available for one specific
        // version.
        $versionMap = array();
        foreach ($releases as $release) {
            // As the version could be 1.3.5-rc1, we need to transform them into x.y.z to be able to compare.
            $version = new version($release->getSemver());
            $version = $version->getMajor() . "." . $version->getMinor() . "." . $version->getPatch();
            $versionMap[$version][$release->getState()][] = $release->getId();
        }

        // This array will hold all the ids of versions we want to return.
        $ids = array();
        foreach ($versionMap as $version => $stateReleaseMap) {
            // Now check if there is a supported version. If so, ignore all the outdated versions, prereleases and
            // development versions. We only want to serve the supported version. If there isn't a supported version
            // but an outdated version, serve the outdated version but ignore all prereleases and development versions
            // and so on.
            // Make sure to show the development version even if there is a prerelease.
            $showDev = true;
            if (isset($stateReleaseMap[CoreReleaseEntity::STATE_SUPPORTED])) {
                // There is a supported core release for version x.y.z
                $ids[CoreReleaseEntity::STATE_SUPPORTED][$version][] = $stateReleaseMap[CoreReleaseEntity::STATE_SUPPORTED][0];
                $showDev = false;
            } else if (isset($stateReleaseMap[CoreReleaseEntity::STATE_OUTDATED])) {
                // There is an outdated core release for version x.y.z
                $ids[CoreReleaseEntity::STATE_OUTDATED][$version][] = $stateReleaseMap[CoreReleaseEntity::STATE_OUTDATED][0];
                $showDev = false;
            } else if (isset($stateReleaseMap[CoreReleaseEntity::STATE_PRERELEASE])) {
                // There is at least one prerelease core for version x.y.z
                // There might be multiple prereleases. Sort them by id and use the latest one.
                rsort($stateReleaseMap[CoreReleaseEntity::STATE_PRERELEASE]);
                $ids[CoreReleaseEntity::STATE_PRERELEASE][$version][] = $stateReleaseMap[CoreReleaseEntity::STATE_PRERELEASE][0];
            }
            if (isset($stateReleaseMap[CoreReleaseEntity::STATE_DEVELOPMENT]) && $showDev) {
                // There is at least one development core for version x.y.z
                // There might be multiple development cores. Sort them by id and use the latest one.
                rsort($stateReleaseMap[CoreReleaseEntity::STATE_DEVELOPMENT]);
                $ids[CoreReleaseEntity::STATE_DEVELOPMENT][$version][] = $stateReleaseMap[CoreReleaseEntity::STATE_DEVELOPMENT][0];
            }
        }

        if ($onlyNewestVersion) {
            // Make sure the newest core versions are at the first position in the arrays.
            foreach ($ids as $state => $versions) {
                krsort($ids[$state]);
            }
        }

        // Now filter out all the releases.
        $releases = array_filter($releases, function (CoreReleaseEntity $release) use ($ids, $onlyNewestVersion) {
            // Check if we want core releases with the state of the current release.
            if (!isset($ids[$release->getState()])) {
                return false;
            }
            // This is all the ids of releases we want for that specific state.
            $idList = $ids[$release->getState()];

            if ($onlyNewestVersion) {
                // We only want the newest version.
                $idList = current($idList);

                return in_array($release->getId(), $idList);
            }

            foreach ($idList as $version => $ids) {
                if (in_array($release->getId(), $ids)) {
                    return true;
                }
            }

            return false;
        });

        // Finally, sort all releases by (1) state ASC (meaning supported first, development last) and (2) by version
        // DESC  and (3) by release DESC.
        usort($releases, function (CoreReleaseEntity $a, CoreReleaseEntity $b) {
            $states = array($a->getState(), $b->getState());
            if ($states[0] !== $states[1]) {
                return ($states[0] > $states[1]) ? 1 : -1;
            }
            $v1 = new version($a->getSemver());
            $v2 = new version($b->getSemver());
            $v1 = $v1->getMajor() . "." . $v1->getMinor() . "." . $v1->getPatch();
            $v2 = $v2->getMajor() . "." . $v2->getMinor() . "." . $v2->getPatch();
            if ($v1 !== $v2) {
                return version_compare($v2, $v1);
            }
            $ids = array($a->getId(), $b->getId());
            if ($ids[0] !== $ids[1]) {
                return ($ids[0] > $ids[1]) ? -1 : 1;
            }

            return 0;
        });

        return $releases;
    }

    public function reloadReleases($source = 'all', $createNewsArticles = true)
    {
        // GitHub releases
        if ($source == 'all' || $source == 'github') {
            $this->reloadReleasesFromGitHub($createNewsArticles);
        }

        // Jenkins builds
        if ($this->jenkinsClient && ($source == 'all' || $source == 'jenkins')) {
            $this->reloadReleasesFromJenkins();
        }

        return true;
    }

    /**
     * Update or add one specific release.
     *
     * @param array               $release           The release data from the GitHub api.
     * @param bool                $createNewsArticle Whether or not to create pending news articles for new releases.
     * @param CoreReleaseEntity[] $dbReleases        INTERNAL: used in self::reloadAllReleases()
     *
     * @return bool|CoreReleaseEntity False if it's a draft; true if a release is edited; the release itself if it's new.
     */
    public function updateGitHubRelease($release, $createNewsArticle = true, $dbReleases = null)
    {
        if ($release['draft']) {
            // Ignore drafts.
            return false;
        }
        $id = $release['id'];

        if ($release['prerelease']) {
            $state = CoreReleaseEntity::STATE_PRERELEASE;
        } else {
            $state = CoreReleaseEntity::STATE_SUPPORTED;
        }

        if ($dbReleases === null) {
            $dbRelease = $this->em->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findOneBy(array('id' => $id));
            if ($dbRelease) {
                $dbReleases[$id] = $dbRelease;
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
            if ($dbRelease->getState() === CoreReleaseEntity::STATE_OUTDATED) {
                // Make sure not to override the state if it has been set to "outdated".
                $state = CoreReleaseEntity::STATE_OUTDATED;
            }
        }

        $dbRelease->setName($release['name']);
        // Make sure to cast null to string if description is empty!
        $dbRelease->setDescription((string)$this->markdown($release['body']));
        $dbRelease->setSemver($release['tag_name']);
        $dbRelease->setSourceUrls(array (
            'zip' => $release['zipball_url'],
            'tar' => $release['tarball_url']
        ));
        $dbRelease->setState($state);

        if ($mode == 'new' && count($release['assets']) == 0 && $this->jenkinsClient && Util::hasGitHubClientPushAccess($this->client)) {
            // Jenkins Build files are not yet uploaded to GitHub. Try to upload them manually.
            $this->moveAssetsFromJenkinsToGitHubRelease($release);
        }

        $assets = array();
        foreach ($release['assets'] as $asset) {
            if ($asset['state'] != 'uploaded') {
                continue;
            }
            $assets[] = array (
                'name' => $asset['name'],
                'download_url' => $asset['browser_download_url'],
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

        if ($mode == 'new' && $createNewsArticle) {
            $this->createNewsArticle($dbRelease);
        } else if($dbRelease->getNewsId() !== null) {
            $this->updateNewsArticle($dbRelease);
        }

        return true;
    }

    /**
     * @return CoreReleaseEntity[]
     */
    private function reloadReleasesFromGitHub($createNewsArticles)
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
            $this->updateGitHubRelease($release, $createNewsArticles, $dbReleases);
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
    }

    /**
     * First, delete all Jenkins builds and then reload the newest ones.
     */
    private function reloadReleasesFromJenkins()
    {
        $oldJenkinsBuilds = $this->em->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findBy(array('state' => CoreReleaseEntity::STATE_DEVELOPMENT));
        $oldJenkinsBuildIds = array();
        foreach ($oldJenkinsBuilds as $oldJenkinsBuild) {
            $oldJenkinsBuildIds[] = $oldJenkinsBuild->getId();
            $this->em->remove($oldJenkinsBuild);
        }
        $this->em->flush();

        /** @var Job $job */
        foreach ($this->jenkinsClient->getJobs() as $job) {
            if ($job->isDisabled()) {
                // Ignore disabled = old jobs.
                continue;
            }
            $name = $job->getName();
            if (!preg_match('#^Zikula(?:_Core|)-([0-9]+\.[0-9]+\.[0-9]+)$#', $name, $matches)) {
                // Ignore jobs not matching the standard pattern.
                continue;
            }
            $version = $matches[1];

            /** @var Build[] $builds */
            $builds = $job->getBuilds();
            foreach ($builds as $key => $build) {
                if ($build->isBuilding() || $build->getResult() != "SUCCESS") {
                    unset($builds[$key]);
                }
            }

            if (count($builds) == 0) {
                continue;
            }

            // Sort builds by build number DESC.
            usort($builds, function (Build $a, Build $b) {
                $a = $a->getNumber();
                $b = $b->getNumber();
                if ($a === $b) {
                    return 0;
                }

                return ($a > $b) ? -1 : 1;
            });

            // Get latest build.
            $build = $builds[0];

            $jenkinsBuild = new CoreReleaseEntity($job->getName() . '#' . $build->getNumber());
            $jenkinsBuild->setName($job->getDisplayName() . ' #' . $build->getNumber());
            $jenkinsBuild->setState(CoreReleaseEntity::STATE_DEVELOPMENT);
            $jenkinsBuild->setSemver($version);

            $description = $job->getDescription();
            $sourceUrls = array();
            $changeSet = $build->getChangeSet()->toArray();
            if ($changeSet['kind'] == 'git' && count($changeSet['items']) > 0) {
                if (!empty($description)) {
                    $description .= "<br /><br />";
                }
                $description .= '<h4>' . __('Latest changes:', $this->dom) . '</h4><ul>';

                foreach ($changeSet['items'] as $item) {
                    $description .= '<li><p>' . $this->markdown($item['msg']) . ' <a href="https://github.com/' . $this->repo . '/commit/' . urlencode($item['commitId']) . '">view at GitHub <i class="fa fa-github"></i></a></p></li>';
                }
                $description .= "</ul>";
                $sha = $this->getShaFromJenkinsBuild($build);
                if ($sha) {
                    $sourceUrls['zip'] = 'https://github.com/' . $this->repo . "/archive/{$sha}.zip";
                    $sourceUrls['tar'] = 'https://github.com/' . $this->repo . "/archive/{$sha}.tar";
                }
            }
            $jenkinsBuild->setSourceUrls($sourceUrls);
            $jenkinsBuild->setDescription($description);
            $jenkinsBuild->setAssets($this->getAssetsFromJenkinsBuild($job, $build));

            $this->em->persist($jenkinsBuild);

            if (!in_array($jenkinsBuild->getId(), $oldJenkinsBuildIds)) {
                $this->notifyBuildAdded($build);
            }
        }

        $this->em->flush();
    }

    /**
     * Get all assets ready to be saved to the database from a specific Jenkins build.
     * The content type is guessed based on the filename.
     *
     * @param Job   $job
     * @param Build $build
     *
     * @return array
     */
    private function getAssetsFromJenkinsBuild(Job $job, Build $build)
    {
        $server = \ModUtil::getVar('ZikulaExtensionLibraryModule', 'jenkins_server');
        $assets = array();
        foreach ($build->getArtifacts() as $artifact) {
            $downloadUrl = $server . '/job/' . urlencode($job->getName()) . '/' . $build->getNumber() . '/artifact/' . $artifact->relativePath;
            $fileExtension = pathinfo($artifact->fileName, PATHINFO_EXTENSION);
            $contentType = null;
            switch ($fileExtension) {
                case 'zip':
                    $contentType = 'application/zip';
                    break;
                case 'gz':
                    $contentType = 'application/gzip';
                    break;
                case 'txt':
                    $contentType = 'text/plain';
                    break;
                default:
                    $contentType = null;

            }
            $assets[] = array (
                'name' => $artifact->fileName,
                'download_url' => $downloadUrl,
                'size' => null,
                'content_type' => $contentType
            );
        }

        return $assets;
    }

    /**
     * Get the corresponding git SHA of a Jenkins build.
     *
     * @param Build $build
     *
     * @return bool|string False if SHA could not be determined; string otherwise.
     */
    private function getShaFromJenkinsBuild(Build $build, $excludeMergeSha = false)
    {
        $buildArr = $build->toArray();
        if (!$excludeMergeSha) {
            foreach ($buildArr['actions'] as $action) {
                if (isset($action['lastBuiltRevision']['SHA1'])) {
                    return $action['lastBuiltRevision']['SHA1'];
                }
            }
        } else {
            foreach ($buildArr['changeSet']['items'] as $item) {
                if (isset($item['commitId'])) {
                    return $item['commitId'];
                }
            }
        }

        return false;
    }

    /**
     * "Markdownify" a text using GitHub's flavoured markdown (resulting in @cmfcmf and zikula/core#123 links).
     *
     * @param string $text The text to "markdownify".
     *
     * @return string
     */
    private function markdown($text)
    {
        $settings = array(
            'text' => $text,
            'mode' => 'gfm',
            'context' => $this->repo
        );

        $response = $this->client->getHttpClient()->post('markdown', json_encode($settings));

        return ResponseMediator::getContent($response);
    }

    /**
     * This adds a little message to GitHub (if the build is based on a PR) showing that it has been added to the
     * ExtensionLibrary.
     *
     * @param Build $build
     */
    private function notifyBuildAdded($build)
    {
        if (!Util::hasGitHubClientPushAccess($this->client)) {
            return;
        }
        $sha = $this->getShaFromJenkinsBuild($build, true);
        if (!$sha) {
            return;
        }
        // Catch everything as the api is still in preview mode and can change without further notice.
        try {
            $response = $this->client->getHttpClient()->post(
                'repos/' . $this->repo . '/deployments',
                json_encode(array (
                    'ref' => $sha,
                    'auto_merge' => false,
                    'required_contexts' => array(),
                    'environment' => 'Extension Library',
                    'description' => 'Updating the Extension Library.'
                ))
            );
            $response = ResponseMediator::getContent($response);
            $this->client->getHttpClient()->post(
                'repos/' . $this->repo . '/deployments/' . $response['id'] . '/statuses',
                json_encode(array (
                    'state' => 'success',
                    'target_url' => $this->router->generate('zikulaextensionlibrarymodule_user_viewcorereleases', array(), RouterInterface::ABSOLUTE_URL),
                    'description' => 'Build has been added to the Extension Library.'
                ))
            );
        } catch (\Exception $e) {
            Util::log("Deployment API failed:" . $e->getMessage(), Util::LOG_PROD);
        }
    }

    /**
     * Try to upload the Jenkins assets to a GitHub release.
     *
     * @param $release
     *
     * @return array
     */
    private function moveAssetsFromJenkinsToGitHubRelease($release)
    {
        // First, get the sha of the release's tag.
        $tagName = $release['tag_name'];
        $tags = $this->client->getHttpClient()->get('repos/' . $this->repo . '/git/refs/tags');
        $tags = ResponseMediator::getContent($tags);
        $sha = false;
        foreach ($tags as $tag) {
            if ($tag['ref'] == "refs/tags/$tagName") {
                $sha = $tag['object']['sha'];
                break;
            }
        }
        if (!$sha) {
            return;
        }
        // We got the release's sha. Now check the latest builds on jenkins for that sha.
        $correspondingBuild = false;
        /** @var Job $job */
        foreach ($this->jenkinsClient->getJobs() as $job) {
            /** @var Build $build */
            foreach ($job->getBuilds() as $build) {
                if ($sha == $this->getShaFromJenkinsBuild($build)) {
                    $correspondingBuild = $build;
                }
            }
        }
        if (!$correspondingBuild) {
            // We did not find the corresponding build for that sha.
            return;
        }
        // Gotcha! The current Jenkins build has the same sha as the GitHub release.
        // Now extract the assets from the Jenkins build.
        list ($repoOwner, $repoName) = explode('/', $this->repo);
        $assets = $this->getAssetsFromJenkinsBuild($job, $correspondingBuild);

        $client = $this->client;
        $releaseManager = $this;
        $this->eventDispatcher->addListener(KernelEvents::TERMINATE, function (PostResponseEvent $event) use ($release, $assets, $repoOwner, $repoName, $client, $releaseManager) {
            foreach ($assets as $asset) {
                if (!$asset['content_type']) {
                    // GitHub won't allow us to upload files without specifying the content type.
                    // Skip those files (but there shouldn't be any).
                    continue;
                }
                try {
                    $client->api('repo')->releases()->assets()->create($repoOwner, $repoName, $release['id'], $asset['name'], $asset['content_type'], file_get_contents($asset['download_url']));
                } catch (\Exception $e) {
                    Util::log("Error while uploading assets to GitHub: " . $e->getMessage());
                }
            }

            $release = $client->api('repo')->releases()->show($repoOwner, $repoName, $release['id']);
            $releaseManager->updateGitHubRelease($release);
        });


    }

    /**
     * Creates a news article about a new release.
     *
     * @param CoreReleaseEntity $newRelease
     */
    private function createNewsArticle(CoreReleaseEntity $newRelease)
    {
        Util::log("Creating News article for new release", Util::LOG_PROD);
        if (!\ModUtil::available('News')) {
            Util::log("News module not available!", Util::LOG_PROD);
            return;
        }
        switch ($newRelease->getState()) {
            case CoreReleaseEntity::STATE_SUPPORTED:
                $title = __f('%s released!', array($newRelease->getNameI18n()), $this->dom);
                $teaser = '<p>' . __f('The core development team is proud to announce the availabilty of %s.', array($newRelease->getNameI18n())) . '</p>';
                break;
            case CoreReleaseEntity::STATE_PRERELEASE:
                $title = __f('%s ready for testing!', array($newRelease->getNameI18n()), $this->dom);
                $teaser = '<p>' . __f('The core development team is proud to announce a pre-release of %s. Please help testing and report bugs!', array($newRelease->getNameI18n())) . '</p>';
                break;
            case CoreReleaseEntity::STATE_DEVELOPMENT:
            case CoreReleaseEntity::STATE_OUTDATED:
            default:
                // Do not create news post.
                return;
        }

        $args = array();
        $now = \DateUtil::getDatetime();
        $args['title'] = $title;
        $args['hometext'] = $teaser;
        $args['hometextcontenttype'] = 0;
        $args['bodytextcontenttype'] = 0;
        $args['bodytext'] = $newRelease->getNewsText();
        $args['notes'] = '';
        $args['published_status'] = \News_Api_User::STATUS_PENDING;
        $args['displayonindex'] = 1;
        $args['allowcomments'] = 1;
        $args['from'] = $now;
        $args['cr_date'] = $now;
        $args['tonolimit'] = true;

        Util::log("Calling News Api: " . print_r($args, true), Util::LOG_PROD);
        $id = \ModUtil::apiFunc('News', 'user', 'create', $args);

        if (is_numeric($id) && $id > 0) {
            Util::log("News article successfully create, id = $id", Util::LOG_PROD);
            $newRelease->setNewsId($id);
            $this->em->merge($newRelease);
            $this->em->flush();
            Util::log("Release entity updated with news id", Util::LOG_PROD);
        } else {
            Util::log("News article NOT created, invalid id.", Util::LOG_PROD);
        }
    }

    /**
     * Updates download links of a news article.
     *
     * @param CoreReleaseEntity $release
     */
    private function updateNewsArticle(CoreReleaseEntity $release)
    {
        if ($release->getNewsId() === null || !\ModUtil::available('News')) {
            return;
        }

        $article = \ModUtil::apiFunc('News', 'user', 'get', array ('sid' => $release->getNewsId()));
        if (!$article) {
            return;
        }

        $article['bodytext'] = preg_replace('#' . preg_quote(CoreReleaseEntity::NEWS_DESCRIPTION_START) . '.*?' . preg_quote(CoreReleaseEntity::NEWS_DESCRIPTION_END) . '#',
            $release->getNewsText(),
            $article['bodytext']
        );
        $article['hometextcontenttype'] = 0;
        $article['bodytextcontenttype'] = 0;
        $article['unlimited'] = 1;
        $article['to'] = 1;
        \ModUtil::apiFunc('News', 'admin', 'update', $article);
    }
}
