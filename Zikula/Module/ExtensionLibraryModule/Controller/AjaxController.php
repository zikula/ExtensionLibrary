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

namespace Zikula\Module\ExtensionLibraryModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\BadDataResponse;
use ModUtil;

/**
 * @Route("/ajax")
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * @Route("/validateManifest")
     * @Method("POST")
     *
     * Validate a manifest
     *
     * @return AjaxResponse
     */
    public function validateManifest()
    {
        $this->checkAjaxToken();
        $content = $this->request->request->get('content', '');
        if (empty($content)) {
            return new BadDataResponse();
        }

        // Get the schema and data as objects
        $module = ModUtil::getModule($this->name);
        $retriever = new \JsonSchema\Uri\UriRetriever;
        $schemaFile = $retriever->retrieve('file://' . realpath($module->getPath() . '/Schema/schema.manifest.json'));

        // Validate
        $validator = new \JsonSchema\Validator();
        $validator->check(json_decode($content), $schemaFile);

        if ($validator->isValid()) {
            $valid = true;
            $errors = array();
        } else {
            $valid = false;
            $errors = $validator->getErrors();
        }

        return new AjaxResponse(array(
            'content' => $content,
            'errors' => $errors,
            'valid' => $valid));
    }
}
