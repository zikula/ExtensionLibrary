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
        $this->view->assign('gravatarDefaultPath', $request->getUriForPath('/'.UsersConstant::DEFAULT_AVATAR_IMAGE_PATH.'/'.UsersConstant::DEFAULT_GRAVATAR_IMAGE));
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

        $releaseManager = $this->get('zikulaextensionlibrarymodule.releasemanager');
        $releases = $releaseManager->getSignificantReleases(false);
        $this->view->assign('releases', $releases);

        return $this->response($this->view->fetch('User/viewreleases.tpl'));
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
            throw new \RuntimeException('Something unexpected happend!');
        }

        /** @var RepositoryManager $userRepositoryManager */
        $userRepositoryManager = $this->get('zikulaextensionlibrarymodule.repositorymanager');
        $userRepositoryManager->setGitHubClient($userGitHubClient);

        /** @var RepositoryManager $elRepositoryManager */
        $elRepositoryManager = $this->get('zikulaextensionlibrarymodule.repositorymanager');
        $elRepositoryManager->setGitHubClient($elGitHubClient);

        $userRepositoriesWithPushAccess = array_column($userRepositoryManager->getRepositoriesWithPushAccess(), 'full_name');
        sort($userRepositoriesWithPushAccess);

        $currentUser = $userGitHubClient->api('current_user')->show();
        $vendor = $request->request->get('vendor', json_decode($request->request->get('_vendor'), true));
        $extension = $request->request->get('extension');
        $this->view->assign('breadcrumbs', array (array ('title' => $this->__('Add extension'))));
        if (empty($extension)) {
            $this->view->assign('repos', $userRepositoriesWithPushAccess);
            $this->view->assign('vendor', $currentUser);

            return $this->response($this->view->fetch('User/addextension.tpl'));
        } else if (empty($extension['name'])) {
            // validate step one parameters
            if (empty($vendor['displayName'])) {
                $this->request->getSession()->getFlashBag()->set('error', $this->__f('%s is required', '<code>Vendor display name</code>'));
                $request->request->remove('extension');
                return $this->addExtensionAction($request); // @TODO not sure this is proper... maybe a redirect instead
            }

            list($owner, $repo) = explode('/', $extension['repository']);
            $repo = $userGitHubClient->api('repo')->show($owner, $repo);
            $this->view->assign('repo',  $repo);
            $this->view->assign('vendor', $request->get('vendor'));

            return $this->response($this->view->fetch('User/addextension2.tpl'));
        }
        // validate step two parameters
        $requiredParams = array('name', 'version', 'description', 'license', 'coreCompatibility');
        foreach ($requiredParams as $requiredParam) {
            if (empty($extension[$requiredParam])) {
                $this->request->getSession()->getFlashBag()->set('error', $this->__f('%s is required', '<code>'.$requiredParam.'</code>'));
            }
        }
        // @TODO validate actual semver? validate license acronym?
        if ($this->request->getSession()->getFlashBag()->has('error')) {
            $request->request->remove('extension');
            // @TODO is there a way to make this redirect to the second step instead of the first?
            return $this->addExtensionAction($request); // @TODO not sure this is proper... maybe a redirect instead
        }

        if (!in_array($extension['repository'], $userRepositoriesWithPushAccess)) {
            // The user tried to select a repository he has no push access to.
            throw new NotFoundHttpException();
        }

        $userRepository = $userRepositoryManager->getRepository($extension['repository']);

        try {
            $webHook = $userRepositoryManager->createWebHook(
                $userRepository,
                array('push'),
                $this->get('router')->generate('zikulaextensionlibrarymodule_webhook_extension', array(), RouterInterface::ABSOLUTE_URL)
            );
        } catch (ValidationFailedException $e) {
            // Hook already exists.
            $webHook = false;
        }

        // fork repository to zikulabot
        $forkedRepository = $elRepositoryManager->forkRepository($userRepository);

        // create branch
        $defaultBranch = $forkedRepository['default_branch'];
        $prBranch = 'extension-library';
        $elRepositoryManager->addBranch($forkedRepository, $defaultBranch, $prBranch);

        // search for current composer.json file in original repo
        // @TODO currently only searches at repo root for composer file. This MUST change to allow for PSR-0
        $currentComposerFile = $elRepositoryManager->getFileInRepository($userRepository, $defaultBranch, 'composer.json');
        if ($currentComposerFile !== false) {
            $this->request->getSession()->getFlashBag()->set('error', $this->__('It seems like there already is a composer.json file in your repository. Sorry, we do not support updating composer files yet. Please follow the instructions below.'));

            return new RedirectResponse(System::normalizeUrl($this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile')));
        }
        // ensure composer file also not in forked repo
        $forkedComposerFile = $elRepositoryManager->getFileInRepository($forkedRepository, $prBranch, 'composer.json');
        if ($forkedComposerFile === false) {
            // create and write composer file
            $author = array(
                "name" => $vendor['name'],
                "role" => "owner"
            );
            if (!empty($vendor['url'])) {
                $author["homepage"] = $vendor['url'];
            }
            if (!empty($vendor['email'])) {
                $author["email"] = $vendor['email'];
            }
            list($vendorPrefix) = explode('/', $extension['repository']);
            // @TODO must add the `extra` field here for namespaced modules.
            $content = json_encode(array(
                "name" => "$vendorPrefix/{$extension['name']}-" . strtolower(substr($extension['type'], strlen('zikula-'))),
                "description" => $extension['description'],
                "type" => $extension['type'],
                "license" => $extension['license'],
                "authors" => array ($author),
                "require" => array ("php" => ">5.3.3")
            ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            // place composer.json in root of repo
            // @TODO need to place in proper location for PSR-0
            $elRepositoryManager->createFileInRepository($forkedRepository, $prBranch, 'composer.json', $content);
        }

        // search for existing zikula.manifest.json file in original repo
        $currentManifestFile = $elRepositoryManager->getFileInRepository($userRepository, $defaultBranch, 'zikula.manifest.json');
        if ($currentManifestFile !== false) {
            $this->request->getSession()->getFlashBag()->set('error', $this->__('It seems like there already is a zikula.manifest.json file in your repository. Sorry, we do not support updating manifest files yet. Please follow the instructions below.'));

            return new RedirectResponse(System::normalizeUrl($this->get('router')->generate('zikulaextensionlibrarymodule_user_displaydocfile')));
        }
        // ensure zikula.manifest file also not in forked repo
        $forkedManifestFile = $elRepositoryManager->getFileInRepository($forkedRepository, $prBranch, 'zikula.manifest.json');
        if ($forkedManifestFile === false) {
            // create and write zikula.manifest file
            $vendorArr = array("title" => $vendor['displayName']);
            if (!empty($vendor['url'])) {
                $vendorArr["url"] = $vendor['url'];
            }
            if (!empty($vendor['logo'])) {
                $vendorArr["logo"] = $vendor['logo'];
            }
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
            $versionArr['composerpath'] = 'composer.json';
            $versionArr['description'] = $extension['description'];
            $versionArr['urls']['issues'] = $userRepository['html_url'] . "/issues";

            $content = json_encode(array(
                /*"api" => "v1",*/
                "vendor" => $vendorArr,
                "extension" => $extensionArr,
                "version" => $versionArr
            ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            // place zikula.manifest file in root of repo
            $elRepositoryManager->createFileInRepository($forkedRepository, $prBranch, 'zikula.manifest.json', $content);
        }

        // @TODO check for success of other operations before creating PR?
        // create Pull Request
        $title = "Add this extension to the Zikula Extension Library";
        $elLink = $this->get('router')->generate('zikulaextensionlibrarymodule_user_index', array(), RouterInterface::ABSOLUTE_URL);
        $elRepoLink = 'https://github.com/craigh/ExtensionLibrary/issues/new';
        $ghReleasesUrl = $userRepository['html_url'] . "/releases/new";
        $body = <<< EOF
#### Hi @{$userRepository['owner']['login']}!

You requested to add this extension to the [Zikula Extension Library]($elLink) :star:. You're just two clicks away from there:
1. Merge this PR!
2. Add a new Tag as version {$versionArr['semver']} (either using the git command line, your favourite git client or the [GitHub online interface]($ghReleasesUrl))!

In case something doesn't work as expected, feel free to open an issue in the [Extension Library repository]($elRepoLink)!
EOF;
        $pullRequest = $elRepositoryManager->createPullRequest($userRepository, $forkedRepository, $prBranch, $defaultBranch, $title, $body);

        // Delete the fork. The pull request will still work.
        $elRepositoryManager->deleteRepository($forkedRepository);

        return new RedirectResponse($userRepository['html_url'] . "/pull/{$pullRequest['number']}");
    }
}
