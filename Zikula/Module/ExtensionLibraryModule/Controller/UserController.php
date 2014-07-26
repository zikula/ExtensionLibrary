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
    /**
     * @Route("")
     *
     * @Route("/v/{vendor_slug}", name="zikulaextensionlibrarymodule_user_filterbyvendor")
     * @ParamConverter("vendorEntity",
     *      class="ZikulaExtensionLibraryModule:VendorEntity",
     *      options={"mapping": {"vendor_slug": "titleSlug"}}
     * )
     *
     * The default entry point. Shows either all extensions or only the one's matching the specified vendor.
     *
     * @param VendorEntity $vendorEntity
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function indexAction(VendorEntity $vendorEntity = null)
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        if ($vendorEntity === null) {
            $extensions = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:ExtensionEntity')->findAllMatchingFilter();
        } else {
            $extensions = $vendorEntity->getExtensionsbyFilter();
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
    public function displayAction(ExtensionEntity $extensionEntity)
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $this->view->assign('extension', $extensionEntity);
        $this->view->assign('gravatarDefaultPath', $this->request->getUriForPath('/'.UsersConstant::DEFAULT_AVATAR_IMAGE_PATH.'/'.UsersConstant::DEFAULT_GRAVATAR_IMAGE));
        $this->view->assign('breadcrumbs', array(
            array(
                'title' => $extensionEntity->getVendor()->getTitle(),
                'route' => $this->get('router')->generate(
                        'zikulaextensionlibrarymodule_user_filterbyvendor',
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
     * @Route("/filter/{filterType}/{filter}/{returnUrl}", requirements={"filterType" = "coreVersion|extensionType"}))
     *
     * @param $filterType string Can be either "coreVersion" or "extensionType".
     * @param $filter     string The value to filter.
     * @param $returnUrl  string The return url to redirect to.
     *
     * @throws NotFoundHttpException
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return RedirectResponse()
     */
    public function filterAction($filterType, $filter, $returnUrl)
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        switch ($filterType) {
            case 'coreVersion':
                try {
                    Util::setCoreFilter($filter);
                } catch (\InvalidArgumentException $e) {
                    throw new NotFoundHttpException('Invalid arguments received.');
                }
                break;
            case 'extensionType':
                try {
                    Util::setExtensionTypeFilter($filter);
                } catch (\InvalidArgumentException $e) {
                    throw new NotFoundHttpException('Invalid arguments received.');
                }
                break;
            default:
                // Should never happen due to the requirements set in the route.
                throw new NotFoundHttpException('Invalid arguments received.');
        }

        return new RedirectResponse(System::normalizeUrl(urldecode($returnUrl)));
    }

    /**
     * @Route("/log")
     *
     * Display the log file
     *
     * @return Response
     */
    public function displayLogAction()
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
     * @Route("/doc/{file}", requirements={"file" = "manifest|sample-manifest|composer|sample-composer|instructions|webhook"})
     *
     * Display a requested doc file
     *
     * @param string $file
     *
     * @return Response
     */
    public function displayDocFileAction($file = 'instructions')
    {
        $module = ModUtil::getModule($this->name);
        $docs = array(
            'manifest' => array(
                'file' => '/docs/manifest.md',
                'urls' => array(
                    'sample' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile', array('file' => 'sample-manifest')),
                )),
            'composer' => array(
                'file' => '/docs/composer.md',
                'urls' => array(
                    'sample' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile', array('file' => 'sample-composer')),
                )),
            'instructions' => array(
                'file' => '/docs/instructions.md',
                'urls' => array(
                    'manifest' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile', array('file' => 'manifest')),
                    'composer' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile', array('file' => 'composer')),
                    'validate' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_validatemanifest'),
                    'log' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaylog'),
                    'postreceive-hook' => $this->get('router')->generate('zikulaextensionlibrarymodule_post_processinbound'),
                    'webhook' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile', array('file' => 'webhook')),
                )),
            'webhook' => array(
                'file' => '/docs/webhookGuide.md',
                'urls' => array(
                    // @todo - getRelativePath() is deprecated
                    'img1' => $module->getRelativePath() . '/docs/images/shots1.png',
                    'img2' => $module->getRelativePath() . '/docs/images/shots2.png',
                    'img3' => $module->getRelativePath() . '/docs/images/shots3.png',
                    'img4' => $module->getRelativePath() . '/docs/images/shots4.png',
                    'img5' => $module->getRelativePath() . '/docs/images/shots5.png',
                )),
            'sample-manifest' => array('file' => '/docs/zikula.manifest.json'),
            'sample-composer' => array('file' => '/docs/composer.json'),
        );
        $docfile = file_get_contents($module->getPath() . $docs[$file]['file']);
        $json = false;
        if (substr($file, 0, 6) != "sample") {
            $parser = StringUtil::getMarkdownExtraParser();
            $parser->predef_urls = $docs[$file]['urls'];
            $docfile = StringUtil::getMarkdownExtraParser()->transform($docfile);
        } else {
            $json = true;
        }
        $this->view->assign('docfile', $docfile)
                ->assign('json', $json)
                ->assign('breadcrumbs', array(
                    array(
                        'title' => $this->__('Docs'),
                        'route' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocindex'),
                    ),
                    array(
                        'title' => $file,
                        'route' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile', array('file' => $file)),
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
    public function displayDocindexAction()
    {
        $this->view->assign('breadcrumbs', array(
                array(
                    'title' => $this->__('Docs'),
                ),
            ));
        return $this->response($this->view->fetch('User/docs.tpl'));
    }

    /**
     * @Route("/validate")
     *
     * Display a form to validate a zikula.manifest.json file
     * Handle form submission via ajax
     *
     * @return Response
     */
    public function validateManifestAction()
    {
        $this->view->assign('breadcrumbs', array(
            array(
                'title' => $this->__('Validate'),
            ),
        ));
        return $this->response($this->view->fetch('User/validate.tpl'));
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
    public function getImageAction($name = null)
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

    /**
     * @Route("/releases")
     */
    public function viewCoreReleasesAction()
    {
        $this->view->assign('breadcrumbs', array (
            array (
                'title' => 'Core Releases'
            )
        ));
        $this->view->assign('releases', $this->entityManager->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findBy(array(), array('status' => 'ASC', 'id' => 'ASC')));

        return $this->response($this->view->fetch('User/viewreleases.tpl'));
    }
}
