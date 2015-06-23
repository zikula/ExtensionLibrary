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

use DoctrineHelper;
use HookUtil;

/**
 * ExtensionLibrary module installer.
 */
class ExtensionLibraryModuleInstaller extends \Zikula_AbstractInstaller
{
    private $entities = array(
        'Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity',
        'Zikula\Module\ExtensionLibraryModule\Entity\VendorEntity',
        'Zikula\Module\ExtensionLibraryModule\Entity\ExtensionVersionEntity',
        'Zikula\Module\ExtensionLibraryModule\Entity\OAuthEntity'
    );

    /**
     * Initialise the module.
     *
     * @return boolean True on success or false on failure.
     */
    public function install()
    {
        try {
            DoctrineHelper::createSchema($this->entityManager, $this->entities);
        } catch (\Exception $e) {
            $this->request->getSession()->getFlashBag()->add('error', $e->getMessage());
            return false;
        }
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());

        return true;
    }

    /**
     * Upgrade the module from an old version.
     *
     * @param string $oldversion The version from which the upgrade is beginning (the currently installed version); this should be compatible
     *                              with {@link version_compare()}.
     *
     * @return boolean True on success or false on failure.
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion) {
            case '1.0.0':
                HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
            case '1.0.1':
                HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());
                HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
            case '1.0.2':
                DoctrineHelper::updateSchema($this->entityManager, array('Zikula\Module\ExtensionLibraryModule\Entity\VendorEntity'));
            case '1.0.3':
                DoctrineHelper::createSchema($this->entityManager, array('Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity'));
            case '1.0.4':
            case '1.0.5':
                DoctrineHelper::updateSchema($this->entityManager, array('Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity'));
            case '1.0.6':
                DoctrineHelper::updateSchema($this->entityManager, array('Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity'));
                DoctrineHelper::createSchema($this->entityManager, array('Zikula\Module\ExtensionLibraryModule\Entity\OAuthEntity'));
            case '1.0.7':
            case '1.0.8':
            case '1.0.9':
                DoctrineHelper::dropSchema($this->entityManager, $this->entities);
                DoctrineHelper::createSchema($this->entityManager, $this->entities);

        }

        // Update successful
        return true;
    }

    /**
     * Delete the module.
     *
     * @return boolean True on success or false on failure.
     */
    public function uninstall()
    {
        try {
            DoctrineHelper::dropSchema($this->entityManager, $this->entities);
        } catch (\PDOException $e) {
            $this->request->getSession()->getFlashBag()->add('error', $e->getMessage());
            return false;
        }
        HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());

        return true;
    }

}
