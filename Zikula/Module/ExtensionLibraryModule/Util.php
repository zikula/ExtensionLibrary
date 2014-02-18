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
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionVersionEntity;

class Util
{
    /**
     * constants defining log paths
     */
    const LOG_DEV = './app/logs/el_dev.log';
    const LOG_PROD = './app/logs/el.log';

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
        // open file
        $fd = fopen($logpath, "a");
        // prepend date/time to message
        $str = "[" . date("Y/m/d h:i:s", time()) . "] " . $msg;
        // write string
        fwrite($fd, $str . "\n");
        // close file
        fclose($fd);
    }

    /**
     * Saves the chosen core version to a cookie. The cookie will be deleted after 24 hours.
     * 
     * @param string $version The core version, can be anything matching SemVer or 'all'.
     */
    public static function setChosenCore($version)
    {
        \CookieUtil::setCookie('zikulaextensionslibrarymodule_chosenCore', $version, time() + 60*60*24, '/');
    }

    /**
     * Returns the chosen core version from cookie. If no cookie is set, it returns 'all'.
     * 
     * @return string The core version, can be anything matching SemVer or 'all'.
     */
    public static function getChosenCore()
    {
        return \CookieUtil::getCookie('zikulaextensionslibrarymodule_chosenCore', true, 'all');
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
        $client = new GitHubClient();
        $token = \ModUtil::getVar('ZikulaExtensionLibraryModule', 'github_token', null);
        if (!empty($token)) {
            $client->authenticate($token, null, GitHubClient::AUTH_HTTP_TOKEN);
            try {
                $client->getHttpClient()->get('rate_limit');
            } catch (\RuntimeException $e) {
                // Authentication failed!
                if ($fallBackToNonAuthenticatedClient) {
                    // Replace client with one not using authentication.
                    $client = new GitHubClient();
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
     * @param string|null                       $filter The core version to filter. Defaults to the cookie value.
     *
     * @return ExtensionEntity[]|ArrayCollection
     */
    public static function filterExtensionsByCore($extensions, $filter = null)
    {
        if (!isset($filter)) {
            $filter = self::getChosenCore();
        }
        if ($filter === 'all') {
            return $extensions;
        }

        $userSelectedCoreVersion = new version($filter);

        foreach ($extensions as $key => $extension) {
            if ($extension->getVersions()->filter(function (ExtensionVersionEntity $version) use ($userSelectedCoreVersion) {
                $requiredCoreVersion = new expression($version->getCompatibility());
                return $requiredCoreVersion->satisfiedBy($userSelectedCoreVersion);
            })->isEmpty()) {
                unset($extensions[$key]);
            }
        }

        return $extensions;
    }
} 
