<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\ExtensionLibraryModule;

use Doctrine\Common\Collections\ArrayCollection;
use Github\Client as GitHubClient;
use Github\HttpClient\Cache\FilesystemCache;
use Github\HttpClient\CachedHttpClient;
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;
use Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionVersionEntity;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Util
{
    /**
     * constants defining log paths
     */
    const LOG_DEV = 'app/logs/el_dev.log';
    const LOG_PROD = 'app/logs/el.log';

    /**
     * Log a message to a file
     *
     * @param $msg
     * @param $logpath
     */
    public static function log($msg, $logpath = self::LOG_DEV)
    {
        if (!in_array($logpath, array(self::LOG_DEV, self::LOG_PROD))) {
            return;
        }
        $logger = new Logger('extensionLibrary');
        $logger->pushHandler(new StreamHandler($logpath, Logger::INFO));
        $logger->addInfo($msg);
    }

    /**
     * Get all available core versions.
     *
     * @return array An array of arrays providing "outdated", "supported" and "dev" core versions.
     *
     * @todo Fetch from GitHub.
     */
    public static function getAvailableCoreVersions()
    {
        $em = \ServiceUtil::get('doctrine.orm.entity_manager');
        /** @var \Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity[] $dbReleases */
        $dbReleases = $em->getRepository('Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity')->findAll();
        $releases = array();
        foreach ($dbReleases as $dbRelease) {
            $releases[CoreReleaseEntity::statusToText($dbRelease->getStatus())][$dbRelease->getSemver()] = '';
        }
        krsort($releases);

        return $releases;
    }

    /**
     * Get all available extension types.
     *
     * @return array An array of allowed extension types.
     */
    public static function getAvailableExtensionTypes()
    {
        $dom = \ZLanguage::getModuleDomain('ZikulaExtensionLibraryModule');

        return array(
            ExtensionEntity::TYPE_MODULE => __('Modules', $dom),
            ExtensionEntity::TYPE_THEME => __('Themes', $dom),
            ExtensionEntity::TYPE_PLUGIN => __('Plugins', $dom)
        );
    }

    /**
     * Saves the chosen core version to a session variable.
     *
     * @param string $filter The core version, can be anything matching SemVer or 'all'.
     *
     * @throws \InvalidArgumentException If $filter is invalid.
     */
    public static function setCoreFilter($filter)
    {
        $coreVersions = self::getAvailableCoreVersions();

        // @todo Simplify this :/
        if (!(
            $filter === 'all'
            || (
                isset($coreVersions[CoreReleaseEntity::statusToText(CoreReleaseEntity::STATE_SUPPORTED)])
                &&
                array_key_exists($filter, $coreVersions[CoreReleaseEntity::statusToText(CoreReleaseEntity::STATE_SUPPORTED)])
            )
            || (
                isset($coreVersions[CoreReleaseEntity::statusToText(CoreReleaseEntity::STATE_OUTDATED)])
                &&
                array_key_exists($filter, $coreVersions[CoreReleaseEntity::statusToText(CoreReleaseEntity::STATE_OUTDATED)])
            )
            || (
                isset($coreVersions[CoreReleaseEntity::statusToText(CoreReleaseEntity::STATE_PRERELEASE)])
                &&
                array_key_exists($filter, $coreVersions[CoreReleaseEntity::statusToText(CoreReleaseEntity::STATE_PRERELEASE)])
            )
            || (
                isset($coreVersions[CoreReleaseEntity::statusToText(CoreReleaseEntity::STATE_DEVELOPMENT)])
                &&
                array_key_exists($filter, $coreVersions[CoreReleaseEntity::statusToText(CoreReleaseEntity::STATE_DEVELOPMENT)])
            )
        )) {
            throw new \InvalidArgumentException();
        }

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = \ServiceUtil::get('request');
        $request->getSession()->set('zikulaextensionslibrarymodule_chosenCore', $filter);
    }

    /**
     * Returns the chosen core version from session variable. Defaults to 'all'.
     * 
     * @return string The core version, can be anything matching SemVer or 'all'.
     */
    public static function getCoreVersionFilter()
    {
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = \ServiceUtil::get('request');
        return $request->getSession()->get('zikulaextensionslibrarymodule_chosenCore', 'all');
    }

    /**
     * Saves the chosen extension type to a session variable.
     *
     * @param string $filter The extension type to filter.
     *
     * @throws \InvalidArgumentException If $filter is invalid.
     */
    public static function setExtensionTypeFilter($filter)
    {
        $extensionTypes = self::getAvailableExtensionTypes();

        if (!($filter === 'all' || array_key_exists($filter, $extensionTypes))) {
            throw new \InvalidArgumentException();
        }

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = \ServiceUtil::get('request');
        $request->getSession()->set('zikulaextensionslibrarymodule_extensionType', $filter);
    }

    /**
     * Returns the chosen extension type from session variable. Defaults to 'all'.
     *
     * @return string The chosen extension type.
     */
    public static function getExtensionTypeFilter()
    {
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = \ServiceUtil::get('request');
        return $request->getSession()->get('zikulaextensionslibrarymodule_extensionType', 'all');
    }

    /**
     * Get an instance of the GitHub Client, authenticated with the admin's authentication token.
     *
     * @param bool $fallBackToNonAuthenticatedClient Whether or not to fall back to a non-authenticated client if
     * authentication fails, default true.
     *
     * @param bool $log Whether to log errors or not, default true.
     *
     * @return GitHubClient|bool The authenticated GitHub client, or false if $fallBackToNonAuthenticatedClient
     * is false and the client could not be authenticated.
     */
    public static function getGitHubClient($fallBackToNonAuthenticatedClient = true, $log = true)
    {
        $cacheDir = \CacheUtil::getLocalDir('el/github-api');

        $httpClient = new CachedHttpClient();
        $httpClient->setCache(new FilesystemCache($cacheDir));
        $client = new GitHubClient($httpClient);

        $token = \ModUtil::getVar('ZikulaExtensionLibraryModule', 'github_token', null);
        if (!empty($token)) {
            $client->authenticate($token, null, GitHubClient::AUTH_HTTP_TOKEN);
            try {
                $client->getHttpClient()->get('rate_limit');
            } catch (\RuntimeException $e) {
                // Authentication failed!
                if ($fallBackToNonAuthenticatedClient) {
                    // Replace client with one not using authentication.
                    $httpClient = new CachedHttpClient();
                    $httpClient->setCache(new FilesystemCache($cacheDir));
                    $client = new GitHubClient($httpClient);
                } else {
                    $client = false;
                }
                if ($log) {
                    self::log('GitHub token is invalid, authorization failed!');
                }
            }
        }

        return $client;
    }

    /**
     * Filter the given extensions by core filter.
     *
     * @param ExtensionEntity[]|ArrayCollection $extensions
     * @param null|string $coreVersion   The core version to filter, defaults to the core selected by the user.
     * @param null|string $extensionType The extension type to filter, defaults to the extension type selected by the
     * user.
     *
     * @return ExtensionEntity[]|ArrayCollection
     */
    public static function filterExtensions($extensions, $coreVersion = null, $extensionType = null)
    {
        if (!isset($coreVersion)) {
            $coreVersion = Util::getCoreVersionFilter();
        }
        if (!isset($extensionType)) {
            $extensionType = Util::getExtensionTypeFilter();
        }
        if ($coreVersion === 'all' && $extensionType === 'all') {
            return $extensions;
        }

        if ($coreVersion !== 'all') {
            $userSelectedCoreVersion = new version($coreVersion);
        }

        foreach ($extensions as $key => $extension) {
            if ($extensionType !== 'all') {
                if ($extension->getType() !== $extensionType) {
                    unset ($extensions[$key]);
                    continue;
                }
            }
            if ($coreVersion !== 'all' && $extension->getVersions()->filter(function (ExtensionVersionEntity $version) use ($userSelectedCoreVersion) {
                $requiredCoreVersion = new expression($version->getCompatibility());
                return $requiredCoreVersion->satisfiedBy($userSelectedCoreVersion);
            })->isEmpty()) {
                unset($extensions[$key]);
            }
        }

        return $extensions;
    }
} 
