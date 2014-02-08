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
use Zikula\Module\ExtensionLibraryModule\Entity\VendorEntity;
use Zikula\Module\ExtensionLibraryModule\Util;
use Zikula\Module\UsersModule\Constant as UsersConstant;
use Symfony\Component\HttpFoundation\RedirectResponse;
use System;
use StringUtil;
use Zikula\Module\ExtensionLibraryModule\Manager\ImageManager;

/**
 * UI operations executable by general users.
 */
class UserController extends \Zikula_AbstractController
{
    private function checkIfCoreChosen()
    {
        if (!Util::getChosenCore()) {
            return new RedirectResponse(System::normalizeUrl(ModUtil::url('ZikulaExtensionLibraryModule', 'user', 'chooseCore')));
        }

        return true;
    }

    /**
     * @Route("")
     *
     * @Route("/v/{vendor_slug}", name="zikulaextensionlibrarymodule_user_filterbycore")
     * @ParamConverter("vendorEntity",
     *      class="ZikulaExtensionLibraryModule:VendorEntity",
     *      options={"mapping": {"vendor_slug": "titleSlug"}}
     * )
     *
     * The default entry point. Shows either all extensions or only the one's matching the specified vendor.
     *
     * @param VendorEntity $vendorEntity
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function indexAction(VendorEntity $vendorEntity = null)
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        $this->checkIfCoreChosen();

        if ($vendorEntity === null) {
            $extensions = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:ExtensionEntity')->findAllMatchingCoreFilter();
        } else {
            $extensions = $vendorEntity->getExtensionsbyCoreFilter();
        }

        $this->view->assign('extensions', $extensions);
        $this->view->assign('gravatarDefaultPath', $this->request->getUriForPath('/'.UsersConstant::DEFAULT_AVATAR_IMAGE_PATH.'/'.UsersConstant::DEFAULT_GRAVATAR_IMAGE));
        if ($vendorEntity === null) {
            $this->view->assign('breadcrumbs', array());
        } else {
            $this->view->assign('breadcrumbs', array(array('title' => $vendorEntity->getTitle())));
        }

        return $this->response($this->view->fetch('User/view.tpl'));
    }

    /**
     * @Route("/e/{extension_slug}")
     * @ParamConverter("extensionEntity",
     *      class="ZikulaExtensionLibraryModule:ExtensionEntity",
     *      options={"mapping": {"extension_slug": "titleSlug"}}
     * )
     *
     * Displays the detail page for an extension.
     *
     * @param ExtensionEntity $extensionEntity
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function display(ExtensionEntity $extensionEntity)
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        $this->checkIfCoreChosen();

        $this->view->assign('extension', $extensionEntity);
        $this->view->assign('gravatarDefaultPath', $this->request->getUriForPath('/'.UsersConstant::DEFAULT_AVATAR_IMAGE_PATH.'/'.UsersConstant::DEFAULT_GRAVATAR_IMAGE));
        $this->view->assign('breadcrumbs', array(
            array(
                'title' => $extensionEntity->getVendor()->getTitle(),
                'route' => $this->get('router')->generate(
                        'zikulaextensionlibrarymodule_user_filterbycore',
                        array('vendor_slug' => $extensionEntity->getVendor()->getTitleSlug()
                        )
                    )
            ),
            array(
                'title' => $extensionEntity->getName()
            ),
        ));

        return $this->response($this->view->fetch('User/display.tpl'));
    }

    /**
     * @Route("/choose-your-core/{version}")
     *
     * @param string $version
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @return Response
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
            if (!($version === 'all' || array_key_exists($version, $coreVersions['outdated']) || array_key_exists($version, $coreVersions['supported']) || array_key_exists($version, $coreVersions['dev']))) {
                throw new NotFoundHttpException();
            }
            Util::setChosenCore($version);
            return new RedirectResponse(System::normalizeUrl(ModUtil::url('ZikulaExtensionLibraryModule', 'user', 'index')));
        }

        $this->view->assign('coreVersions', array_reverse($coreVersions, true));
        $this->view->assign('breadcrumbs', array(array('title' => $this->__('Choose a Core Version'))));

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
        if (file_exists("app/logs/el.log")) {
            $logfile = file_get_contents("app/logs/el.log");
        } else {
            $logfile = $this->__('Nothing logged yet!');
        }
        $this->view->assign('log', nl2br($logfile));
        $this->view->assign('breadcrumbs', array(array('title' => $this->__('Log'))));

        return $this->response($this->view->fetch('User/log.tpl'));
    }

    /**
     * @Route("/doc/{file}", requirements={"file" = "manifest|sample-manifest|composer|sample-composer|instructions"})
     *
     * Display a requested doc file
     *
     * @param string $file
     *
     * @return Response
     */
    public function displayDocFile($file = 'instructions')
    {
        $module = ModUtil::getModule($this->name);
        $docs = array(
            'manifest' => '/docs/manifest.md',
            'composer' => '/docs/composer.md',
            'instructions' => '/docs/instructions.md',
            'sample-manifest' => '/docs/zikula.manifest.json',
            'sample-composer' => '/docs/composer.json',
        );
        $docfile = file_get_contents($module->getPath() . $docs[$file]);
        $json = false;
        if (substr($file, 0, 6) != "sample") {
            $docfile = StringUtil::getMarkdownExtraParser()->transform($docfile);
        } else {
            $json = true;
        }
        $this->view->assign('docfile', $docfile)
                ->assign('json', $json)
                ->assign('breadcrumbs', array(
                    array(
                        'title' => $this->__('Docs'),
                        'route' => 'el/docs',
                    ),
                    array(
                        'title' => $file,
                        'route' => 'el/' . $file,
                    ),
            ));

        return $this->response($this->view->fetch('User/doc.tpl'));
    }

    /**
     * @Route("/docs")
     *
     * Display an index of document files
     *
     * @return Response
     */
    public function displayDocindex()
    {
        $this->view->assign('breadcrumbs', array(
                array(
                    'title' => $this->__('Docs'),
                ),
            ));
        return $this->response(($this->view->fetch('User/docs.tpl')));
    }

    /**
     * @Route("/getimage/{name}")
     *
     * retrieve an image
     *
     * @param string $name
     *
     * @return Response
     */
    public function getImage($name = null)
    {
        // only allow local request for images
//        if (!($this->request->server->get("REMOTE_ADDR", 0) == $this->request->server->get('SERVER_ADDR', 1))) {
//            throw new AccessDeniedException();
//        }
        if (isset($name) && !strpos($name, '/')) {
            $path = ImageManager::STORAGE_PATH . $name;
        } else {
            // get a default image
            $module = ModUtil::getModule('ZikulaExtensionLibraryModule');
            // @todo - getRelativePath() is deprecated
            $path = $module->getRelativePath() . '/Resources/public/images/zikula.png';
        }

        if (function_exists('exif_imagetype')) {
            // errors suppressed: only need true/false (without triggering E_NOTICE)
            $type = @exif_imagetype($path);
        } else {
            // errors suppressed: only need true/false (without triggering E_NOTICE)
            $type = @getimagesize($path);
            $type = isset($type[2]) ? $type[2] : false;
        }
        if ($type) {
            $response = new Response(readfile($path), Response::HTTP_OK, array(
                'Content-Type' => image_type_to_mime_type($type),
                'Content-Length' => filesize($path)
            ));

            $imageCacheTime = $this->getVar('image_cache_time', 0);
            if ($imageCacheTime > 0) {
                $response->setMaxAge($imageCacheTime);
            }

            return $response;
        } else {
            // return default image instead
            Util::log("could not retrieve image ($name)");
            return $this->getImage();
        }
    }
}
