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

namespace Zikula\Module\ExtensionLibraryModule\HookedTopicMeta;

use ServiceUtil;
use ZLanguage;
use Zikula\Module\DizkusModule\AbstractHookedTopicMeta;

class ZikulaExtensionLibraryModule extends AbstractHookedTopicMeta
{

    /**
     * @var \Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity
     */
    private $extension = null;
    /**
     * module translation domain
     */
    private $dom;

    public function setup()
    {
        $this->dom = ZLanguage::getModuleDomain('ZikulaExtensionLibraryModule');
        $entityManager = ServiceUtil::get('doctrine.entitymanager');
        $this->extension = $entityManager->getRepository('ZikulaExtensionLibraryModule:ExtensionEntity')->find($this->getObjectId());
    }

    public function setTitle()
    {
        $this->title = $this->extension->getTitle() . ' ' . __('from', $this->dom) . ' ' . $this->extension->getVendor()->getTitle();
    }

    public function setContent()
    {
        $replacements = array(
            $this->extension->getNewestVersion()->getSemver(),
            $this->extension->getTitle(),
            $this->extension->getVendor()->getTitle(),
            $this->extension->getUpdated()->format('j M Y'),
            $this->extension->getDescription(),
            $this->getLink(),
        );
        $this->content = __f('The %1$s version of extension %2$s was added by %3$s on %4$s.<br /><br />Short description: %5$s<br /><br />[%6$s]', $replacements, $this->dom);
    }

}
