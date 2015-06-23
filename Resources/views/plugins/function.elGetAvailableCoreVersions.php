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

/**
 * Zikula_View|Smarty template plugin
 * Retrieve all available core versions.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding
 *               variable instead of printed out
 *
 * Example
 *   {elGetAvailableCoreVersions assign='coreVersions'}
 *
 * @param $params
 * @param Zikula_View $view
 * @return array the available core versions
 */
function smarty_function_elGetAvailableCoreVersions($params, Zikula_View $view)
{
    /**
     * @var \Zikula\Module\ExtensionLibraryModule\Manager\CoreReleaseManager $coreReleaseManager
     */
    $coreReleaseManager = ServiceUtil::get('zikulaextensionlibrarymodule.corereleasemanager');
    $coreVersions = $coreReleaseManager->getAvailableCoreVersions();

    if (isset($params['assign']) && !empty($params['assign'])) {
        $view->assign($params['assign'], $coreVersions);
    } else {
        return $coreVersions;
    }
}
