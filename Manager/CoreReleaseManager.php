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

namespace Zikula\Module\ExtensionLibraryModule\Manager;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Module\CoreManagerModule\Api\ReleasesV1Api;

class CoreReleaseManager
{
    /**
     * @var ReleasesV1Api
     */
    protected $api;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(RequestStack $requestStack, ReleasesV1Api $api = null)
    {
        $this->requestStack = $requestStack;
        $this->api = $api;
    }

    /**
     * Get all available core versions.
     *
     * @return array An array of arrays providing "outdated", "supported" and "dev" core versions.
     */
    public function getAvailableCoreVersions($indexBy = 'stateText')
    {
        if ($this->api === null) {
            return array();
        }
        $releases = $this->api->getSignificantReleases(false);
        $states = $this->api->getReleaseStates();

        $return = array();
        foreach ($releases as $release) {
            if ($indexBy == 'stateText') {
                $key = $states[$release['state']]['textPlural'];
            } else {
                $key = $release['state'];
            }
            $return[$key][] = $release['semver'];
        }

        return $releases;
    }

    /**
     * Saves the chosen core version to a session variable.
     *
     * @param string $filter The core version, can be anything matching SemVer or 'all'.
     *
     * @throws \InvalidArgumentException If $filter is invalid.
     */
    public function setCoreFilter($filter)
    {
        $coreVersions = $this->getAvailableCoreVersions('state');

        foreach ($coreVersions as $state => $coreVersion) {
            foreach ($coreVersion as $version) {
                if ($version == $filter) {
                    $this->requestStack->getCurrentRequest()->getSession()->set('zikulaextensionslibrarymodule_chosenCore', $filter);
                    return;
                }
            }
        }
        throw new \InvalidArgumentException();
    }

    /**
     * Returns the chosen core version from session variable. Defaults to 'all'.
     *
     * @return string The core version, can be anything matching SemVer or 'all'.
     */
    public function getCoreVersionFilter()
    {
        return $this->requestStack->getCurrentRequest()->getSession()->get('zikulaextensionslibrarymodule_chosenCore', 'all');
    }
}
