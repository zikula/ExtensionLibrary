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

use SecurityUtil;
use ModUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter; // used in annotations - do not remove
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;
use Zikula\Module\ExtensionLibraryModule\Util;
use Zikula\Module\UsersModule\Constant as UsersConstant;
use Symfony\Component\HttpFoundation\RedirectResponse;
use System;
use StringUtil;

/**
 * UI operations executable by general users.
 */
class UserController extends \Zikula_AbstractController
{
    private function checkChosenCore()
    {
        if (!Util::getChosenCore()) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url('ZikulaExtensionLibraryModule', 'user', 'chooseCore')));
        }
    }

    /**
     * @Route("")
     * The default entry point.
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function indexAction()
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        $this->checkChosenCore();

        $extensions = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:ExtensionEntity')->findAllMatchingCoreFilter();
        $this->view->assign('extensions', $extensions);
        $this->view->assign('gravatarDefaultPath', $this->request->getUriForPath('/'.UsersConstant::DEFAULT_AVATAR_IMAGE_PATH.'/'.UsersConstant::DEFAULT_GRAVATAR_IMAGE));

        return $this->response($this->view->fetch('User/view.tpl'));
    }

    /**
     * @Route("/display/{id}")
     * @ParamConverter("extension", class="ZikulaExtensionLibraryModule:ExtensionEntity")
     *
     * Displays the detail page for an extension.
     *
     * @param int $id The extension id.
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function display(ExtensionEntity $extension)
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        $this->checkChosenCore();

        $this->view->assign('extension', $extension);
        $this->view->assign('gravatarDefaultPath', $this->request->getUriForPath('/'.UsersConstant::DEFAULT_AVATAR_IMAGE_PATH.'/'.UsersConstant::DEFAULT_GRAVATAR_IMAGE));

        return $this->response($this->view->fetch('User/display.tpl'));
    }

    /**
     * @Route("/choose-your-core/{version}")
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function chooseCore($version = null)
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // @todo Fetch from GitHub.
        $coreVersions = array(
            'outdated'  => array('1.3.5' => 'foo', '1.3.4' => 'foo', '1.3.3' => 'foo', '1.3.2' => 'foo', '1.3.1' => 'foo', '1.3.0' => 'foo'),
            'supported' => array('1.3.6' => 'foo'),
            'dev'       => array('1.4.0' => 'foo'),
        );

        if (isset($version)) {
            if (!($version === 'no-filter' || array_key_exists($version, $coreVersions['outdated']) || array_key_exists($version, $coreVersions['supported']) || array_key_exists($version, $coreVersions['dev']))) {
                throw new NotFoundHttpException();
            }
            Util::setChosenCore($version);
            return new RedirectResponse(System::normalizeUrl(ModUtil::url('ZikulaExtensionLibraryModule', 'user', 'index')));
        }

        $this->view->assign('coreVersions', array_reverse($coreVersions, true));

        return $this->response($this->view->fetch('User/chooseCore.tpl'));
    }

    /**
     * @Route("/log")
     *
     * Display the log file
     *
     * @return Response
     */
    public function displayLog()
    {
        $logfile = file_get_contents("app/logs/el.log");
        $this->view->assign('log', nl2br($logfile));

        return $this->response($this->view->fetch('User/log.tpl'));
    }

    /**
     * @Route("/doc/{file}", requirements={"file" = "manifest|sample|instructions"})
     */
    public function displayDocFile($file = 'instructions')
    {
        $module = ModUtil::getModule($this->name);
        $docs = array(
            'manifest' => '/docs/manifest.md',
            'sample' => '/docs/zikula.manifest.json',
            'instructions' => '/docs/instructions.md',
        );
        $docfile = file_get_contents($module->getPath() . $docs[$file]);
        if ($file != 'sample') {
            $docfile = StringUtil::getMarkdownExtraParser()->transform($docfile);
        } else {
            $docfile = nl2br(htmlentities($docfile));
        }
        $this->view->assign('docfile', $docfile);

        return $this->response($this->view->fetch('User/doc.tpl'));
    }
}
