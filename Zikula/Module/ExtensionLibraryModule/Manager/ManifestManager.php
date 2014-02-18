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

use Zikula\Module\ExtensionLibraryModule\Util;

class ManifestManager extends RemoteJsonManager {
    /**
     * Constructor
     *
     * @param string $owner
     * @param string $repo
     * @param string $ref
     */
    public function __construct($owner, $repo, $ref)
    {
        $this->schema = 'schema.manifest.json';
        parent::__construct($owner, $repo, $ref, 'zikula.manifest.json');

        if ($this->valid) {
            $this->validateVersion($ref);
            $this->appendLinks($owner, $repo);
        }
    }

    /**
     * Check if version in $ref is the same as the version in the manifest
     *
     * @param $ref
     * @return boolean
     */
    private function validateVersion($ref)
    {
        list(, , $semver) = explode('/', $ref);
        if (version_compare($semver, $this->content->version->semver, '!=')) {
            $this->valid = false;
            $this->validationErrors[] = array('property' => 'version.semver', 'message' => 'manifest version.semver does not match tagged version');
        }
        Util::log("The version is valid.");
    }

    /**
     * append download links to content object
     */
    private function appendLinks($owner, $repo)
    {
        // append download links
        $tags = $this->client->api('repo')->tags($owner, $repo);
        foreach ($tags as $tag) {
            if (version_compare($tag['name'], $this->content->version->semver, '==')) {
                if (!isset($this->content->version->urls)) {
                    $this->content->version->urls = new \stdClass();
                }
                $this->content->version->urls->zipball_url = $tag['zipball_url'];
                $this->content->version->urls->tarball_url = $tag['tarball_url'];
                break; // exit foreach loop
            }
        }
    }
}