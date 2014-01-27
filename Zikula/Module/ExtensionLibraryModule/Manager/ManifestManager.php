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

use ModUtil;
use Zikula\Module\ExtensionLibraryModule\Util;

class ManifestManager {
    /**
     * The module name
     * @var string
     */
    private $name = 'ZikulaExtensionLibraryModule';
    /**
     * The raw manifest request response
     * @var json
     */
    private $manifest;
    /**
     * The decoded content of the manifest
     * @var json
     */
    private $content;

    /**
     * Constructor
     *
     * @param string $owner
     * @param string $repo
     * @param string $refs
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($owner, $repo, $refs)
    {
        $module = ModUtil::getModule($this->name);
        require_once $module->getPath() . '/vendor/autoload.php';

        $client = new \Github\Client();
        try {
            $this->manifest = $client->api('repo')->contents()->show($owner, $repo, 'zikula.manifest.json', $refs);
        } catch (\Exception $e) {
            Util::log("Unable to fetch manifest file");
            throw new \InvalidArgumentException();
        }

        $this->decodeContent();
    }

    /**
     * Decode the content of the manifest
     * @throws \InvalidArgumentException
     */
    private function decodeContent()
    {
        try {
            $this->content = json_decode(base64_decode($this->manifest["content"]));
        } catch (\Exception $e) {
            Util::log(sprintf("Unable to decode manifest content (%s)", json_last_error_msg()));
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @return json object
     */
    public function getContent()
    {
        return $this->content;
    }
} 