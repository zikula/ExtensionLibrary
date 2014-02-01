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
     * JSON DECODING ERROR CODE DEFINITIONS
     * @var array
     */
    private $jsonErrorCodes = array(
        JSON_ERROR_NONE	=> "No error has occurred",
        JSON_ERROR_DEPTH => "The maximum stack depth has been exceeded",
        JSON_ERROR_STATE_MISMATCH => "Invalid or malformed JSON",
        JSON_ERROR_CTRL_CHAR => "Control character error, possibly incorrectly encoded",
        JSON_ERROR_SYNTAX => "Syntax error",
        JSON_ERROR_UTF8 => "Malformed UTF-8 characters, possibly incorrectly encoded",
    );
    /**
     * The module name
     * @var string
     */
    private $name = 'ZikulaExtensionLibraryModule';
    /**
     * The module path
     * @var string
     */
    private $modulePath;
    /**
     * The raw manifest request response
     * @var string
     */
    private $manifest;
    /**
     * The decoded content of the manifest
     * @var \stdClass
     */
    private $content;
    /**
     * Is the manifest valid?
     * @var bool
     */
    private $valid = false;
    /**
     * Validation error discovered in the validation method
     * @var array
     */
    private $validationErrors = array();

    /**
     * Constructor
     *
     * @param string $owner
     * @param string $repo
     * @param string $ref
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($owner, $repo, $ref)
    {
        $module = ModUtil::getModule($this->name);
        $this->modulePath = $module->getPath();

        $client = new \Github\Client();
        try {
            $this->manifest = $client->api('repo')->contents()->show($owner, $repo, 'zikula.manifest.json', $ref);
        } catch (\Exception $e) {
            Util::log("Unable to fetch manifest file");
            throw new \InvalidArgumentException();
        }
        $rateLimitRemaining = $client->getHttpClient()->getLastResponse()->getHeader('X-RateLimit-Remaining');
        Util::log('Rate limit remaining: ' . $rateLimitRemaining);

        $this->decodeContent();
        $this->validate();
        $this->validateVersion($ref);

        // append download links
        $tags = $client->api('repo')->tags($owner, $repo);
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

    /**
     * Decode the content of the manifest
     * @throws \InvalidArgumentException
     */
    private function decodeContent()
    {
        $jsonEncodedContent = base64_decode($this->manifest["content"]); // returns false on failure
        if (!$jsonEncodedContent) {
            Util::log("Unable to base64_decode manifest content. Be sure json is valid.");
            throw new \InvalidArgumentException();
        }
        $this->content = json_decode($jsonEncodedContent); // return null on failure
        if (empty($this->content)) {
            $error = $this->jsonErrorCodes[json_last_error()];
            Util::log(sprintf("Unable to json_decode manifest content (%s). Be sure json is valid.", $error));
            throw new \InvalidArgumentException();
        }
        Util::log("Content decoded!");
    }

    /**
     * validate the manifest with the schema
     */
    private function validate()
    {
        // Get the schema and data as objects
        $retriever = new \JsonSchema\Uri\UriRetriever;
        $schema = $retriever->retrieve('file://' . realpath($this->modulePath . '/Schema/manifest.json'));

        // Validate
        $validator = new \JsonSchema\Validator();
        $validator->check($this->content, $schema);

        if ($validator->isValid()) {
            $this->valid = true;
            Util::log('The manifest validated!');
        } else {
            $this->valid = false;
            $this->validationErrors = array_merge($this->validationErrors, $validator->getErrors());
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
     * @return \stdClass|boolean
     */
    public function getContent()
    {
        if ($this->isvalid()) {
            return $this->content;
        } else {
            Util::log("manifest does not validate. Violations:");
            foreach ($this->validationErrors as $error) {
                Util::log(sprintf("[%s] %s", $error['property'], $error['message']));
            }
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}