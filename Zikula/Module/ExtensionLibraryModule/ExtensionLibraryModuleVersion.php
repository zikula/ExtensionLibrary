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

use Zikula\Component\HookDispatcher\SubscriberBundle;
use HookUtil;
use Zikula\Module\SearchModule\AbstractSearchable;

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
            'version' => '1.0.3',
            'core_min' => '1.4.0',
            'core_max' => '1.4.99',
            'securityschema' => array($this->name . '::' => '::'),
            'capabilities' => array(
                HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true),
                AbstractSearchable::SEARCHABLE => array('class' => 'Zikula\Module\ExtensionLibraryModule\Helper\SearchHelper'),
            )
        );
    }

    protected function setupHookBundles()
    {
        $bundle = new SubscriberBundle($this->name, 'subscriber.el.ui_hooks.extension', 'ui_hooks', $this->__('ExtensionLibrary Hooks'));
        $bundle->addEvent('display_view', 'el.ui_hooks.extension.display_view');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new SubscriberBundle($this->name, 'subscriber.el.ui_hooks.community', 'ui_hooks', $this->__('Community Tab Hooks'));
        $bundle->addEvent('display_view', 'el.ui_hooks.community.display_view');
        $bundle->addEvent('process_edit', 'el.ui_hooks.community.process_edit');
        $this->registerHookSubscriberBundle($bundle);
    }

}