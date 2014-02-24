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

namespace Zikula\Module\ExtensionLibraryModule\Helper;

use Zikula\Module\SearchModule\AbstractSearchable;
use SecurityUtil;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;
use Zikula\Core\ModUrl;

class SearchHelper extends AbstractSearchable
{
    /**
     * get the UI options for search form
     *
     * @param $args
     * @return string
     */
    public function getOptions($args)
    {
        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
            $this->view->assign('active', !isset($args['active']) || isset($args['active'][$this->name]));
            return $this->view->fetch('Search/options.tpl');
        }

        return '';
    }

    /**
     * Get the search results
     *
     * @param $args
     * @return array
     */
    public function getResults($args)
    {
        // this is an 'eager' search - it doesn't compensate for search type indicated in search UI
        $results = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:ExtensionEntity')->getByFragment($args['q']);

        $sessionId = session_id();
        $records = array();
        foreach ($results as $result) {
            // @todo do a perms check here
            /** @var $result ExtensionEntity */
            $records[] = array(
                'title' => $result->getTitle(),
                'text' => $result->getDescription(),
                'module' => $this->name,
                'sesid' => $sessionId,
                'created' => $result->getUpdated(),
                'url' => new ModUrl($this->name, 'user', 'display', \ZLanguage::getLanguageCode(), array('extension_slug' => $result->getTitleSlug())),
            );
        }

        return $records;
    }
} 