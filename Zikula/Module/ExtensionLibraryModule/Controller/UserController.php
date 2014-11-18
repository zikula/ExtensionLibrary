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

use GitHub\Exception\RuntimeException;
use Github\Exception\ValidationFailedException;
use Github\Client as GitHubClient;
use SecurityUtil;
use ModUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter; // used in annotations - do not remove
use Zikula\Core\Response\PlainResponse;
use Zikula\Core\RouteUrl;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\VendorEntity;
use Zikula\Module\ExtensionLibraryModule\Manager\RepositoryManager;
use Zikula\Module\ExtensionLibraryModule\Util;
use Zikula\Module\UsersModule\Constant as UsersConstant;
use Symfony\Component\HttpFoundation\RedirectResponse;
use System;
use StringUtil;
use Zikula\Module\ExtensionLibraryModule\Manager\ImageManager;
use Zikula\Module\ExtensionLibraryModule\OAuth\Manager as OAuthManager;

/**
 * UI operations executable by general users.
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * @Route("")
     *
     * The default entry point. Shows all extensions.
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function indexAction()
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $orderBy = 'title';
        $orderDir = 'ASC';
        $perpage = $this->getVar('perpage', 45);
        $offset = $this->request->query->get('offset', null);

        $extensions = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:ExtensionEntity')->findAllMatchingFilter($orderBy, $orderDir, $perpage, $offset);

        $this->view->assign('pagerCount', $extensions->count());
        $this->view->assign('extensions', $extensions);
        $this->view->assign('gravatarDefaultPath', $this->request->getUriForPath('/'.UsersConstant::DEFAULT_AVATAR_IMAGE_PATH.'/'.UsersConstant::DEFAULT_GRAVATAR_IMAGE));
        $this->view->assign('breadcrumbs', array());

        return $this->response($this->view->fetch('User/view.tpl'));
    }

    /**
     * @Route("/v/{vendor_slug}", name="zikulaextensionlibrarymodule_user_filterbyvendor")
     * @ParamConverter("vendorEntity",
     *      class="ZikulaExtensionLibraryModule:VendorEntity",
     *      options={"mapping": {"vendor_slug": "titleSlug"}}
     * )
     *
     * Shows extensions matching the specified vendor.
     *
     * @param VendorEntity $vendorEntity
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function extensionsByVendorAction(VendorEntity $vendorEntity)
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $extensions = $vendorEntity->getExtensionsbyFilter();

        $this->view->assign('pagerCount', $extensions->count());
        $this->view->assign('extensions', $extensions);
        $this->view->assign('gravatarDefaultPath', $this->request->getUriForPath('/'.UsersConstant::DEFAULT_AVATAR_IMAGE_PATH.'/'.UsersConstant::DEFAULT_GRAVATAR_IMAGE));
        $this->view->assign('breadcrumbs', array(array('title' => \DataUtil::formatForDisplay($vendorEntity->getTitle()))));

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
    public function displayAction(ExtensionEntity $extensionEntity, Request $request)
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $oAuthManager = $this->get('zikulaextensionlibrarymodule.oauthmanager');
        $hasPushAccess = $oAuthManager->hasPushAccess($extensionEntity);
        if (!$hasPushAccess && $request->query->filter('authenticate', false, false, FILTER_VALIDATE_BOOLEAN)) {
            $result = $oAuthManager->authenticate($this->get('router')->generate('zikulaextensionlibrarymodule_user_display', array ('extension_slug' => $extensionEntity->getTitleSlug(), 'authenticate' => true), RouterInterface::ABSOLUTE_URL));
            if ($result instanceof RedirectResponse) {
                return $result;
            } else if ($result instanceof GitHubClient) {
                $hasPushAccess = $oAuthManager->hasPushAccess($extensionEntity);
            } else {
                throw new \RuntimeException('Something went wrong!');
            }
        }

        $this->view->assign('isExtensionAdmin', $hasPushAccess);
        $this->view->assign('extension', $extensionEntity);
        $this->view->assign('hookUrl', new RouteUrl('zikulaextensionlibrarymodule_user_display', array('extension_slug' => $extensionEntity->getTitleSlug())));
        $this->view->assign('gravatarDefaultPath', $request->getUriForPath('/'.UsersConstant::DEFAULT_AVATAR_IMAGE_PATH.'/'.UsersConstant::DEFAULT_GRAVATAR_IMAGE));
        $this->view->assign('breadcrumbs', array(
            array(
                'title' => \DataUtil::formatForDisplay($extensionEntity->getVendor()->getTitle()),
                'route' => $this->get('router')->generate(
                        'zikulaextensionlibrarymodule_user_filterbyvendor',
                        array('vendor_slug' => $extensionEntity->getVendor()->getTitleSlug()
                        )
                    )
            ),
            array(
                'title' => \DataUtil::formatForDisplay($extensionEntity->getTitle())
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
                    'webhook' => $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile', array('file' => 'webhook')),
                )),
            'webhook' => array(
                'file' => '/docs/webhookGuide.md',
                'urls' => array(
                    // @todo - getRelativePath() is deprecated
                    'img1' => $module->getRelativePath() . '/Resources/docs/images/shots1.png',
                    'img2' => $module->getRelativePath() . '/Resources/docs/images/shots2.png',
                    'img3' => $module->getRelativePath() . '/Resources/docs/images/shots3.png',
                    'img4' => $module->getRelativePath() . '/Resources/docs/images/shots4.png',
                    'img5' => $module->getRelativePath() . '/Resources/docs/images/shots5.png',
                    'img6' => $module->getRelativePath() . '/Resources/docs/images/shots6.png',
                    'img7' => $module->getRelativePath() . '/Resources/docs/images/shots7.png',
                )),
            'sample-manifest' => array('file' => '/docs/zikula.manifest.json'),
            'sample-composer' => array('file' => '/docs/composer.json'),
        );
        $docfile = file_get_contents($module->getPath() . "/Resources" . $docs[$file]['file']);
        $json = false;
        if (substr($file, 0, 6) != "sample") {
            $parser = StringUtil::getMarkdownExtraParser();
            $parser->predef_urls = $docs[$file]['urls'];
            $docfile = $parser->transform($docfile);
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
        if (isset($name) && !strpos($name, '/')) {
            $path = ImageManager::STORAGE_PATH . $name;
        } else {
            // get a default image
            $module = ModUtil::getModule('ZikulaExtensionLibraryModule');
            // @todo - getRelativePath() is deprecated
            $path = $module->getRelativePath() . '/Resources/public/images/default_extension.png';
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
            $response = new PlainResponse(file_get_contents($path), Response::HTTP_OK, array(
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
            return $this->getImageAction();
        }
    }

    /**
     * @Route("/releases")
     */
    public function viewCoreReleasesAction()
    {
        $this->view->assign('breadcrumbs', array (
            array (
                'title' => $this->__('Core Releases')
            )
        ));

        $releaseManager = $this->get('zikulaextensionlibrarymodule.releasemanager');
        $releases = $releaseManager->getSignificantReleases(false);
        $this->view->assign('releases', $releases);

        return $this->response($this->view->fetch('User/viewreleases.tpl'));
    }

    /**
     * @Route("/edit-vendor-information")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editVendorAction(Request $request)
    {
        $oAuthManager = $this->get('zikulaextensionlibrarymodule.oauthmanager');
        $result = $oAuthManager->authenticate($this->get('router')->generate('zikulaextensionlibrarymodule_user_editvendor', array(), RouterInterface::ABSOLUTE_URL));
        if ($result instanceof RedirectResponse) {
            return $result;
        } else if ($result instanceof GitHubClient) {
            $userGitHubClient = $result;
            unset($result);
        } else {
            throw new \RuntimeException('Something unexpected happened!');
        }

        /** @var RepositoryManager $userRepositoryManager */
        $userRepositoryManager = $this->get('zikulaextensionlibrarymodule.repositorymanager');
        $userRepositoryManager->setGitHubClient($userGitHubClient);
        $orgsAndUser = $userRepositoryManager->getOrgsAndUserWithAdminAccess();

        // Now check which vendors actually exist in the ExtensionLibrary.
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('v.id')
            ->from('ZikulaExtensionLibraryModule:VendorEntity', 'v')
            ->where($qb->expr()->in('v.id', array_column($orgsAndUser, 'id')));
        $vendorIds = array_column($qb->getQuery()->getArrayResult(), 'id');
        foreach ($orgsAndUser as $id => $orgOrUser) {
            if (!in_array($id, $vendorIds)) {
                // Unset this vendor as it is not known by the ExtensionLibrary (yet).
                unset($orgsAndUser[$id]);
            }
        }
        if (count($orgsAndUser) == 0) {
            $request->getSession()->getFlashBag()->add('error', $this->__('It seems like you don\'t have permission to any published vendor. If you want to change your own vendor information, make sure to publish an extension first!'));

            return new RedirectResponse($this->get('router')->generate('zikulaextensionlibrarymodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        }

        $this->view->assign('breadcrumbs', array (array ('title' => $this->__('Edit vendor information'))));

        if ($request->isMethod('GET')) {
            $this->view->assign('vendors', $orgsAndUser);

            return new Response($this->view->fetch('User/editvendor.tpl'));
        }

        $vendorData = $request->request->get('vendor');
        $vendor = $this->entityManager->find('ZikulaExtensionLibraryModule:VendorEntity', $vendorData['id']);
        if (!in_array($vendor->getId(), array_keys($orgsAndUser))) {
            throw new NotFoundHttpException();
        }

        if (!$request->request->has('save')) {
            $this->view->assign('vendor', $vendor);

            return new Response($this->view->fetch('User/editvendor.tpl'));
        } else {
            if (!empty($vendorData['title'])) {
                $vendor->setTitle($vendorData['title']);
            } else {
                $vendor->setTitle($vendor->getGitHubName());
            }
            if (!empty($vendorData['email'])) {
                $vendor->setEmail($vendorData['email']);
            } else {
                $vendor->setEmail(null);
            }

            $url = $vendorData['url'];
            if (empty($url)) {
                $vendor->setUrl(null);
            } else {
                // Verify that url is valid.
                if (
                    (strpos($url, "http://") === 0 || strpos($url, "https://") === 0) &&
                    filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED) !== false
                ) {
                    $vendor->setUrl($url);
                } else {
                    $request->getSession()->getFlashBag()->add('error', $this->__('Could not validate the given url. Please make sure to include "http://" or "https://"'));
                }
            }

            if (!empty($vendorData['logo'])) {
                $imageManager = new ImageManager($vendorData['logo']);
                $worked = $imageManager->import();
                if ($worked) {
                    $vendor->setLogoFileName($imageManager->getName());
                    $vendor->setLogo($vendorData['logo']);
                } else {
                    // Leave old vendor logo.
                    $request->getSession()->getFlashBag()->add('error',
                        $this->__f('Could not upload or validate image file. The following error(s) occured: %s', array(
                            implode("<br />", $imageManager->getValidationErrors())
                    )));
                }
            } else {
                $vendor->setLogo(null);
                $vendor->setLogoFileName(null);
            }

            $this->entityManager->persist($vendor);
            $this->entityManager->flush();

            $request->getSession()->getFlashBag()->add('status', $this->__('Your vendor information has been updated.'));

            return new RedirectResponse($this->get('router')->generate('zikulaextensionlibrarymodule_user_filterbyvendor', array('vendor_slug' => $vendor->getTitleSlug()), RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/add-extension")
     */
    public function addExtensionAction(Request $request)
    {
        $oAuthManager = $this->get('zikulaextensionlibrarymodule.oauthmanager');
        $elGitHubClient = Util::getGitHubClient(false);
        $result = $oAuthManager->authenticate($this->get('router')->generate('zikulaextensionlibrarymodule_user_addextension', array(), RouterInterface::ABSOLUTE_URL));
        if ($result === false || $elGitHubClient === false) {
            return new RedirectResponse($this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile', array ('file' => 'webhook'), RouterInterface::ABSOLUTE_URL));
        } else if ($result instanceof RedirectResponse) {
            return $result;
        } else if ($result instanceof GitHubClient) {
            $userGitHubClient = $result;
            unset($result);
        } else {
            throw new \RuntimeException('Something unexpected happened!');
        }

        /** @var RepositoryManager $userRepositoryManager */
        $userRepositoryManager = $this->get('zikulaextensionlibrarymodule.repositorymanager');
        $userRepositoryManager->setGitHubClient($userGitHubClient);

        /** @var RepositoryManager $elRepositoryManager */
        $elRepositoryManager = $this->get('zikulaextensionlibrarymodule.repositorymanager');
        $elRepositoryManager->setGitHubClient($elGitHubClient);

        $userRepositoriesWithPushAccess = array_column($userRepositoryManager->getRepositoriesWithPushAccess(), 'full_name');
        sort($userRepositoriesWithPushAccess);

        $currentUser = $userGitHubClient->currentUser()->show();
        $extension = $request->request->get('extension');
        $this->view->assign('breadcrumbs', array (array ('title' => $this->__('Add extension'))));
        if (empty($extension)) {
            $this->view->assign('repos', $userRepositoriesWithPushAccess);

            return $this->response($this->view->fetch('User/addextension.tpl'));
        } else if (empty($extension['name'])) {
            list($owner, $repo) = explode('/', $extension['repository']);
            $repo = $userGitHubClient->repo()->show($owner, $repo);
            $this->view->assign('repo',  $repo);
            $this->view->assign('extension', $extension);

            return $this->response($this->view->fetch('User/addextension2.tpl'));
        }
        // validate step two parameters
        $requiredParams = array('name', 'version', 'description', 'license', 'coreCompatibility');
        foreach ($requiredParams as $requiredParam) {
            if (empty($extension[$requiredParam])) {
                $this->request->getSession()->getFlashBag()->add('error', $this->__f('%s is required', '<code>'.$requiredParam.'</code>'));
            }
        }
        if (($extension['apitype'] != "1.3") && (!strpos($extension['namespace'], "\\"))) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__f('%1$s is required if %2$s is selected.', array('<code>namespace</code>', '<code>Core 1.4 '. $this->__("compatible") .' namespaced/PSR-n</code>')));
        }
        if (!in_array($extension['repository'], $userRepositoriesWithPushAccess)) {
            // The user tried to select a repository he has no push access to.
            // Throw exception, this should not happen!
            throw new NotFoundHttpException();
        }

        // @TODO validate actual semver? validate license acronym?
        if ($this->request->getSession()->getFlashBag()->has('error')) {
            $request->request->remove('extension');
            unset($extension['name']);
            $request->request->set('extension', $extension);

            return $this->addExtensionAction($request);
        }

        $userRepository = $userRepositoryManager->getRepository($extension['repository']);

        ///// (1) Create WebHook.
        $hasWebHookPermission = true;
        try {
            $userRepositoryManager->createWebHook(
                $userRepository,
                array('push'),
                $this->get('router')->generate('zikulaextensionlibrarymodule_webhook_extension', array(), RouterInterface::ABSOLUTE_URL)
            );
        } catch (ValidationFailedException $e) {
            // Hook already exists.
        } catch (RuntimeException $e) {
            // User doesn't have permission to add a webhook.
            $hasWebHookPermission = false;
        }

        ///// (2) Fork repository to zikulabot.
        $forkedRepository = $elRepositoryManager->forkRepository($userRepository);

        ///// (3) Create PR branch.
        $defaultBranch = $forkedRepository['default_branch'];
        $prBranch = 'extension-library-' . uniqid();
        $elRepositoryManager->addBranch($forkedRepository, $defaultBranch, $prBranch);

        ///// (4) Generate content of composer file.
        $author = array(
            "name" => empty($currentUser['name']) ? $currentUser['login'] : $currentUser['name'],
            "role" => "owner"
        );
        if (!empty($currentUser['blog'])) {
            $author["homepage"] = $currentUser['blog'];
        }
        if (!empty($currentUser['email'])) {
            $author["email"] = $currentUser['email'];
        }
        list($vendorPrefix) = explode('/', $extension['repository']);
        $composerContent = array(
            "name" => strtolower("$vendorPrefix/{$extension['name']}-" . substr($extension['type'], strlen('zikula-'))),
            "description" => $extension['description'],
            "type" => $extension['type'],
            "license" => $extension['license'],
            "authors" => array ($author),
            "require" => array ("php" => ">5.3.3")
        );
        // add the `extra` and `autoload` fields for namespaced modules.
        if ($extension['apitype'] != '1.3') {
            $psrType = "psr-" . substr($extension['apitype'], -1);
            $classNameParts = explode("\\", $extension['namespace']);
            $className = array_shift($classNameParts) . array_pop($classNameParts);
            $composerContent['autoload'] = array($psrType => array($extension['namespace'] => ""));
            $composerContent['extra'] = array('zikula' => array('class' => $extension['namespace'] . "\\" . $className));
        }

        ///// (5) Create or update composer.json file.
        // Calculate path to composer file to search for current composer.json file in the fork.
        $extension['namespace'] = preg_replace("#\\\\+#", "\\", $extension['namespace']);
        $path = strpos($extension['namespace'], "\\") ? str_replace("\\", "/", $extension['namespace']) : '';
        $composerPath = ($extension['apitype'] != '1.4-0') ? 'composer.json' : $path.'/composer.json';

        // Check if composer.json exists in fork.
        $forkedComposerFile = $elRepositoryManager->getFileInRepository($forkedRepository, $prBranch, $composerPath);
        if ($forkedComposerFile === false) {
            // create and write composer file.
            $content = json_encode($composerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $elRepositoryManager->createFileInRepository($forkedRepository, $prBranch, $composerPath, $content);
        } else {
            // update existing composer file.
            // Get current composer content.
            $originalContent = json_decode(base64_decode($forkedComposerFile['content']), true);
            if (!empty($originalContent['authors'])) {
                unset($composerContent['authors']);
            }
            if (!empty($originalContent['require'])) {
                unset($composerContent['require']);
            }
            if (!empty($originalContent['autoload'])) {
                unset($composerContent['autoload']);
            }
            if (!empty($originalContent['extra']) && isset($composerContent['extra'])) {
                $originalContent['extra'] = array_merge($originalContent['extra'], $composerContent['extra']);
                unset($composerContent['extra']);
            }
            // Merge new content.
            $composerContent = array_merge($originalContent, $composerContent);
            // Update file.
            $content = json_encode($composerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $elRepositoryManager->updateFileInRepository($forkedRepository, $prBranch, $forkedComposerFile['sha'], $composerPath, $content);
        }

        ///// (7) Generate content of manifest file.
        $extensionArr = array("title" => $extension['displayName']);
        if (!empty($extension['url'])) {
            $extensionArr["url"] = $extension['url'];
        }
        if (!empty($extension['icon'])) {
            $extensionArr["icon"] = $extension['icon'];
        }
        $versionArr = array();
        if (!empty($extension['keywords'])) {
            $versionArr['keywords'] = array_map("trim", explode(',', $extension['keywords']));
        }
        $versionArr['semver'] = $extension['version'];
        $versionArr['dependencies']['zikula/core'] = $extension['coreCompatibility'];
        $versionArr['composerpath'] = $composerPath;
        $versionArr['description'] = $extension['description'];
        $versionArr['urls']['issues'] = $userRepository['html_url'] . "/issues";

        $manifestContent = array(
            /*"api" => "v1",*/
            "extension" => $extensionArr,
            "version" => $versionArr
        );

        ///// (6) Create or update manifest file.
        // search for existing zikula.manifest.json file in fork.
        $forkedManifestFile = $elRepositoryManager->getFileInRepository($forkedRepository, $prBranch, 'zikula.manifest.json');
        if ($forkedManifestFile === false) {
            $content = json_encode($manifestContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            // place zikula.manifest file in root of repo
            $elRepositoryManager->createFileInRepository($forkedRepository, $prBranch, 'zikula.manifest.json', $content);
        } else {
            // update existing manifest file.
            // Get current manifest content.
            $originalContent = json_decode(base64_decode($forkedManifestFile['content']), true);
            // Merge new content.
            $manifestContent = array_merge($originalContent, $manifestContent);
            // Update file.
            $content = json_encode($manifestContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $elRepositoryManager->updateFileInRepository($forkedRepository, $prBranch, $forkedManifestFile['sha'], 'zikula.manifest.json', $content);
        }

        // create Pull Request
        $title = "Add this extension to the Zikula Extension Library";
        $elLink = $this->get('router')->generate('zikulaextensionlibrarymodule_user_index', array(), RouterInterface::ABSOLUTE_URL);
        $elRepoLink = 'https://github.com/craigh/ExtensionLibrary/issues/new';
        $ghReleasesUrl = $userRepository['html_url'] . "/releases/new";
        $vendorEditUrl = $this->get('router')->generate('zikulaextensionlibrarymodule_user_editvendor', array(), RouterInterface::ABSOLUTE_URL);
        $body = <<< EOF
#### Hi @{$userRepository['owner']['login']}!

You requested to add this extension to the [Zikula Extension Library]($elLink) :star:. You're just two clicks away from there:
EOF;
        if (!$hasWebHookPermission) {
            $elWebHookSetupUrl = $this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile', array('file' => 'webhook'), RouterInterface::ABSOLUTE_URL);
            $body .= "\n" . <<< EOF
**0. Add a WebHook to this repository. This couldn't be done by the ExtensionLibrary, as you don't have sufficient permission. Please ask an admin of @{$userRepository['owner']['login']} to do so by following [these instructions]($elWebHookSetupUrl).**
EOF;
        }
        $body .= "\n" . <<< EOF
1. Merge this PR!
2. Add a new Tag as version {$extension['version']} (either using the git command line, your favourite git client or the
[GitHub online interface]($ghReleasesUrl))!
3. You're done! After your extension is published, you can edit your vendor information (such as `logo`, `title`, `homepage` and `email` [at zikula.org]($vendorEditUrl).

In case something doesn't work as expected, feel free to open an issue in the [Extension Library repository]($elRepoLink)!
EOF;
        $pullRequest = $elRepositoryManager->createPullRequest($userRepository, $forkedRepository, $prBranch, $defaultBranch, $title, $body);

        // Delete the fork. The pull request will still work.
        $elRepositoryManager->deleteRepository($forkedRepository);

        return new RedirectResponse($userRepository['html_url'] . "/pull/{$pullRequest['number']}");
    }
}
