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
 * Convert the state integer to a string representing it.
 *
 *
 * Example
 *   {$var|elReleaseStateToText}
 *
 * @param $state
 * @return string The release state.
 */
function smarty_modifier_elReleaseStateToText($state, $singularPlural = 'singular')
{
    return \Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity::stateToText($state, $singularPlural);
}
