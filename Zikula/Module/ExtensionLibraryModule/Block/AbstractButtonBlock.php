<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\ExtensionLibraryModule\Block;

use BlockUtil;
use Zikula_Controller_AbstractBlock;
use Zikula_View_Theme;

abstract class AbstractButtonBlock extends Zikula_Controller_AbstractBlock
{
    /**
     * display block
     */
    public function display($blockinfo)
    {
        $this->view->assign(BlockUtil::varsFromContent($blockinfo['content']));
    }

    /**
     * modify block settings
     *
     * @param array $blockinfo
     *
     * @return string the bock form
     */
    public function modify($blockinfo)
    {
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        return $this->view
            ->setCaching(\Zikula_View::CACHE_DISABLED)
            ->assign($vars)
            ->fetch('Blocks/modify.tpl');
    }

    /**
     * update block settings
     *
     * @param array $blockinfo
     *
     * @return array $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        $vars = array();
        $vars['btnBlock'] = $this->request->request->filter('btnBlock', false, false, FILTER_VALIDATE_BOOLEAN);
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        $this->view->clear_cache('Blocks/modify.tpl');
        Zikula_View_Theme::getInstance()->clear_cache();

        return $blockinfo;
    }
} 
