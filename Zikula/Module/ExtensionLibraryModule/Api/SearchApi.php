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

namespace Zikula\Module\ExtensionLibraryModule\Api;

use SecurityUtil;
use Zikula_View;
use ModUtil;
use DataUtil;
use DBUtil;
use LogUtil;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;

/**
 * ATTN: This is a TEMPORARY hack to get some sort of search working with an ABYSMAL core implementation of a search API
 * when the Core search service is refactored, this must be redone
 *
 * Class SearchApi
 * @package Zikula\Module\ExtensionLibraryModule\Api
 */
class SearchApi extends \Zikula_AbstractApi
{

    /**
     * Search plugin info
     */
    public function info()
    {
        return array('title' => $this->name,
            'functions' => array($this->name => 'search'));
    }

    /**
     * Search form component
     */
    public function options($args)
    {
        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            $view = Zikula_View::getInstance($this->name);
            $view->assign('active', !isset($args['active']) || isset($args['active'][$this->name]));
            return $view->fetch('Search/options.tpl');
        }

        return '';
    }

    /**
     * Search plugin main function
     */
    public function search($args)
    {
        ModUtil::dbInfoLoad('Search');

        $sessionId = session_id();
        
        $searchFragments = \Search_Api_User::split_query(DataUtil::formatForStore($args['q']), false);

        // this is an 'eager' search - it doesn't compensate for search type indicated in search UI
        $results = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:ExtensionEntity')->getByFragment($searchFragments);

        foreach ($results as $result) {
            /** @var $result ExtensionEntity */
            $record = array(
                'title' => $result->getTitle(),
                'text' => $result->getDescription(),
                'extra' => serialize(array('slug' => $result->getTitleSlug())),
                'module' => $this->name,
                'session' => $sessionId
            );

            if (!DBUtil::insertObject($record, 'search_result')) {
                return LogUtil::registerError($this->__('Error! Could not save the search results.'));
            }
        }

        return true;
    }

    /**
     * Do last minute access checking and assign URL to items
     *
     * Access checking is ignored since access check has
     * already been done. But we do add a URL to the found item
     */
    public function search_check($args)
    {
        $datarow = &$args['datarow'];
        $extra = unserialize($datarow['extra']);
        $datarow['url'] = ModUtil::url($this->name, 'user', 'display', array('extension_slug' => $extra['slug']));
        return true;
    }

}