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

namespace Zikula\Module\ExtensionLibraryModule\Block;

use BlockUtil;
use ModUtil;
use SecurityUtil;
use vierbergenlars\SemVer\version;
use Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity;
use Zikula_Controller_AbstractBlock;

class LatestReleaseBlock extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('ZikulaExtensionLibraryModule:latestRelease:', 'Block title::');
    }

    /**
     * get information on block
     */
    public function info()
    {
        return array(
            'text_type' => 'latestRelease',
            'module' => 'ZikulaExtensionLibraryModule',
            'text_type_long' => $this->__('Latest release button'),
            'allow_multiple' => true,
            'form_content' => false,
            'form_refresh' => false,
            'show_preview' => true,
            'admin_tableless' => true
        );
    }

    /**
     * display block
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('ZikulaExtensionLibraryModule:latestRelease:', "$blockinfo[title]::", ACCESS_OVERVIEW) || !ModUtil::available('ZikulaExtensionLibraryModule')) {
            return;
        }

        $supportedReleases = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findBy(array('status' => CoreReleaseEntity::STATE_SUPPORTED));
        usort($supportedReleases, function (CoreReleaseEntity $a, CoreReleaseEntity $b) {
            $a = new version($a->getSemver());
            $b = new version($b->getSemver());

            return version::compare($b, $a);
        });

        $this->view->assign('release', $supportedReleases[0]);
        $this->view->assign('id', uniqid());
        $blockinfo['content'] = $this->view->fetch('Blocks/latestrelease.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }

}
