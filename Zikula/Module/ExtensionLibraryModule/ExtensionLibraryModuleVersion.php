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

namespace Zikula\Module\ExtensionLibraryModule;

/**
 * ExtensionLibrary module version information and other metadata.
 */
class ExtensionLibraryModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Provides an array of standard Zikula Extension metadata.
     *
     * @return array Zikula Extension metadata.
     */
    public function getMetaData()
    {
        return array(
            'displayname' => $this->__('Extension Library'),
            'description' => $this->__('Browseable Zikula extensions listing'),
            'url' => $this->__('library'),
            'version' => '1.0.0',
            'core_min' => '1.3.7',
            'core_max' => '1.3.99',
            'securityschema' => array(
                $this->name.'::' => '::',
            )
        );
    }

}