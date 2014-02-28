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
 * Retrieve all available extension types.
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding
 *               variable instead of printed out
 *
 * Example
 *   {elGetAvailableExtensionTypes assign='extensionTypes'}
 *
 * @param $params
 * @param Zikula_View $view
 * @return array the available extension types.
 */
function smarty_function_elGetAvailableExtensionTypes($params, Zikula_View $view)
{
    $extensionTypes = \Zikula\Module\ExtensionLibraryModule\Util::getAvailableExtensionTypes();

    if (isset($params['assign']) && !empty($params['assign'])) {
        $view->assign($params['assign'], $extensionTypes);
    } else {
        return $extensionTypes;
    }
}
