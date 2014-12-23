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
 * Retrieve the currently selected core version
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding
 *               variable instead of printed out
 *
 * Example
 *   {elGetChosenCore|safetext}
 *
 * @param $params
 * @param Zikula_View $view
 * @return void|string the selected core version string
 */
function smarty_function_elGetChosenCore($params, Zikula_View $view)
{
    $version = \Zikula\Module\ExtensionLibraryModule\Util::getCoreVersionFilter();

    if (isset($params['assign']) && !empty($params['assign'])) {
        $view->assign($params['assign'], $version);
    } else {
        return $version;
    }
}
