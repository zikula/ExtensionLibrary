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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove


/**
 * UI operations executable by general users.
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * @Route("")
     * The default entry point.
     *
     * @return string
     */
    public function indexAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        $extensions = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:ExtensionEntity')->findAll();
        $this->view->assign('extensions', $extensions);

        return $this->response($this->view->fetch('User/view.tpl'));
    }

}
