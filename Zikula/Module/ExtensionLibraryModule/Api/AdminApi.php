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

use ModUtil;
use SecurityUtil;

class AdminApi extends \Zikula_AbstractApi
{

    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();
        if (SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => $this->get('router')->generate('zikulaextensionlibrarymodule_admin_index'),
                'text' => $this->__('Settings'),
                'title' => $this->__('Edit settings'),
                'icon' => 'wrench');
        }

        return $links;
    }

}
