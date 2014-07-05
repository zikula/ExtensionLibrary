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
 * Convert the status integer to a string representing it.
 *
 *
 * Example
 *   {$var|elReleaseStatusToText}
 *
 * @param $status
 * @return string The release status.
 */
function smarty_modifier_elReleaseStatusToText($status)
{
    return \Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity::statusToText($status);
}
