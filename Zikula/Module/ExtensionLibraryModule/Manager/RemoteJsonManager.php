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
use Symfony\Component\HttpFoundation\Response;
use Zikula\Module\ExtensionLibraryModule\Exception\ClientException;
use Zikula\Module\ExtensionLibraryModule\Exception\ServerException;
use Zikula\Module\ExtensionLibraryModule\Util;

class RemoteJsonManager {
    /**
     * JSON DECODING ERROR CODE DEFINITIONS
     * @var array
     */
    private $jsonErrorCodes = array(
        JSON_ERROR_NONE => "No error has occurred",
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
     * The raw request response
     * @var string
     */
    private $file;
    /**
     * The decoded content
     * @var \stdClass
     */
    protected $content;
    /**
     * Is the file valid?
     * @var bool
     */
    protected $valid = false;
    /**
     * Validation error discovered in the validation method
     * @var array
     */
    protected $validationErrors = array();
    /**
     * Decoding error discovered in the decoding method
     * @var array
     */
    protected $decodingErrors = array();
    /**
     * Github api client
     * @var \Github\Client
     */
    protected $client;
    /**
     * The schema file
     * @var string
     */
    protected $schema;

    /**
     * Constructor
     *
     * @param string $owner repository owner
     * @param string $repo repository name
     * @param string $ref the ref string
     * @param string $remoteRelativePath path to remote file form base of repo e.g. 'zikula.manifest.json'
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($owner, $repo, $ref, $remoteRelativePath)
    {
        $module = ModUtil::getModule($this->name);
        $this->modulePath = $module->getPath();

        $this->client = Util::getGitHubClient();
        try {
            $this->file = $this->client->api('repo')->contents()->show($owner, $repo, $remoteRelativePath, $ref);
        } catch (\Exception $e) {
            throw new ClientException("Unable to fetch $remoteRelativePath", Response::HTTP_BAD_REQUEST);
        }

        if ($this->decodeContent()) {
            $this->validate();
        }
    }

    /**
     * Decode the content of the file
     *
     * @return boolean
     */
    private function decodeContent()
    {
        $jsonEncodedContent = base64_decode($this->file["content"]); // returns false on failure
        if (!$jsonEncodedContent) {
            $this->valid = false;
            $this->decodingErrors[] = "Unable to base64_decode file content. Be sure json is valid.";
            return false;
        }
        $this->content = json_decode($jsonEncodedContent); // returns null on failure
        if (empty($this->content)) {
            $this->valid = false;
            $error = $this->jsonErrorCodes[json_last_error()];
            $this->decodingErrors[] = sprintf("Unable to json_decode file content (%s). Be sure json is valid.", $error);
            return false;
        }

        return true;
    }

    /**
     * validate the file with the schema
     */
    private function validate()
    {
        if (empty($this->schema)) {
            throw new ServerException(sprintf("Schema is undefined (%s).", $this->schema), Response::HTTP_BAD_REQUEST);
        }
        // Get the schema and data as objects
        $retriever = new \JsonSchema\Uri\UriRetriever;
        $schemaFile = $retriever->retrieve('file://' . realpath($this->modulePath . '/Schema/' . $this->schema));

        // Validate
        $validator = new \JsonSchema\Validator();
        $validator->check($this->content, $schemaFile);

        if ($validator->isValid()) {
            $this->valid = true;
        } else {
            $this->valid = false;
            $this->validationErrors = array_merge($this->validationErrors, $validator->getErrors());
        }
    }

    /**
     * @return \stdClass|boolean
     */
    public function getContent()
    {
        if ($this->isvalid()) {
            return $this->content;
        } else {
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

    /**
     * @return array
     */
    public function getDecodingErrors()
    {
        return $this->decodingErrors;
    }

    /**
     * @return bool
     */
    public function hasDecodingErrors()
    {
        return !empty($this->decodingErrors);
    }
}
