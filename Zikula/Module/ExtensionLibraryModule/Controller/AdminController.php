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

use Github\HttpClient\Message\ResponseMediator;
use SecurityUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Module\ExtensionLibraryModule\Manager\ImageManager;
use Zikula\Module\ExtensionLibraryModule\Util;

/**
 * @Route("/admin")
 *
 * UI operations executable by admins only.
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * @Route("")
     * @Method("GET")
     * The default entry point.
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function indexAction()
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Rate limit check
        $client = Util::getGitHubClient();
        $response = $client->getHttpClient()->get('rate_limit');
        $rate = ResponseMediator::getContent($response);
        $rate = $rate['rate'];

        $now = new \DateTime('now');
        $reset = \DateTime::createFromFormat('U', $rate['reset']);
        $rate['minutesUntilReset'] = $now->diff($reset)->format('%i');

        $this->view->assign('rate', $rate);

        // Storage directory check
        if (!ImageManager::checkStorageDir(false)) {
            $this->view->assign('storageDir', ImageManager::STORAGE_PATH);
        }

        $this->view->assign('settings', $this->getVars());

        return $this->response($this->view->fetch('Admin/modifyconfig.tpl'));
    }

    /**
     * @Route("")
     * @Method("POST")
     *
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function modifyConfigAction()
    {
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $modvars = $this->request->request->get('settings');
        $this->setVars($modvars);

        // Check if GitHub authentication works after changing token.
        $client = Util::getGitHubClient(false, false);

        if ($client === false) {
            $this->setVar('github_token', null);
            \LogUtil::registerError('GitHub token is invalid, authorization failed!');
        }

        return new RedirectResponse($this->get('router')->generate('zikulaextensionlibrarymodule_admin_index'));
    }
}
