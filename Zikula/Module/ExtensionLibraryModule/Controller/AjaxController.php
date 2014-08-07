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
use Zikula\Core\Response\Ajax\ForbiddenResponse;

/**
 * @Route("/ajax")
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * @Route("/validateJson", options={"expose"=true})
     * @Method("POST")
     *
     * Validate a json
     *
     * @return AjaxResponse
     */
    public function validateJsonAction()
    {
        $this->checkAjaxToken();
        $schema = $this->request->request->get('schema', 'schema.manifest.json');
        $content = $this->request->request->get('content', '');
        if (empty($content)) {
            return new BadDataResponse();
        }

        // Get the schema and data as objects
        $module = ModUtil::getModule($this->name);
        $retriever = new \JsonSchema\Uri\UriRetriever;
        $schemaFile = $retriever->retrieve('file://' . realpath($module->getPath() . '/Schema/' . $schema));

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
        $schemaNames = array(
            'schema.manifest.json' => 'zikula.manifest.json',
            'schema.composer.json' => 'composer.json',
        );

        return new AjaxResponse(array(
            'content' => $content,
            'schemaName' => $schemaNames[$schema],
            'errors' => $errors,
            'valid' => $valid));
    }

    /**
     * @Route("/setVersionStatus", options={"expose"=true})
     * @Method("POST")
     *
     * set the status of a version
     *
     * @return AjaxResponse
     */
    public function setVersionStatus()
    {
        $this->checkAjaxToken();
        $checked = $this->request->request->get('checked', 1);
        $extid = $this->request->request->get('extid');
        $version = $this->request->request->get('version');
        if (empty($extid) || empty($version)) {
            return new BadDataResponse();
        }

        $version = $this->entityManager
            ->getRepository('ZikulaExtensionLibraryModule:ExtensionVersionEntity')
            ->findOneBy(array('extension' => $extid, 'semver' => $version));

        $oAuthManager = $this->get('zikulaextensionlibrarymodule.oauthmanager');
        $hasPushAccess = $oAuthManager->hasPushAccess($version->getExtension());
        if (!\SecurityUtil::checkPermission('ZikulaExtensionLibraryModule::', '.*', ACCESS_ADMIN) && !$hasPushAccess) {
            return new ForbiddenResponse(\LogUtil::getErrorMsgPermission());
        }


        $version->setStatus($checked);
        $this->entityManager->flush();

        return new AjaxResponse(array('status' => $version->getStatus()));
    }
}
