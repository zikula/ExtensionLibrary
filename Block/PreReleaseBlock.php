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
use Zikula\Module\ExtensionLibraryModule\AbstractButtonBlock;
use Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity;

class PreReleaseBlock extends AbstractButtonBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('ZikulaExtensionLibraryModule:preRelease:', 'Block title::');
    }

    /**
     * get information on block
     */
    public function info()
    {
        return array(
            'text_type' => 'preRelease',
            'module' => 'ZikulaExtensionLibraryModule',
            'text_type_long' => $this->__('Pre release button'),
            'allow_multiple' => true,
            'form_content' => false,
            'form_refresh' => false,
            'show_preview' => true,
            'admin_tableless' => true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('ZikulaExtensionLibraryModule:preRelease:', "$blockinfo[title]::", ACCESS_OVERVIEW) || !ModUtil::available('ZikulaExtensionLibraryModule')) {
            return "";
        }
        parent::display($blockinfo);

        $releaseManager = $this->get('zikulaextensionlibrarymodule.releasemanager');
        $releases = $releaseManager->getSignificantReleases();

        $preReleases = array_filter($releases, function (CoreReleaseEntity $release) {
            return $release->getState() === CoreReleaseEntity::STATE_PRERELEASE;
        });
        if (empty($preReleases)) {
            return "";
        }
        $this->view->assign('preRelease', current($preReleases));
        $this->view->assign('id', uniqid());
        $blockinfo['content'] = $this->view->fetch('Blocks/prerelease.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }
}
