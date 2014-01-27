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

namespace Zikula\Module\ExtensionLibraryModule\Controller;

use SecurityUtil;
use ModUtil;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\Core\Response\PlainResponse;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionVersionEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\VendorEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;
use Zikula\Module\ExtensionLibraryModule\Util;
use Zikula\Module\ExtensionLibraryModule\Manager\ManifestManager;
use Zikula\Module\ExtensionLibraryModule\Manager\PayloadManager;

/**
 * UI operations executable by general users.
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * @Route("")
     * The default entry point.
     *
     * @return string
     */
    public function indexAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        return $this->response($this->view->fetch('User/view.tpl'));
    }

    /**
     * @Route("/postreceive-hook")
     */
    public function processInboundAction()
    {
        // log that the method was called
        Util::log('ExtensionLibraryModule::processInboundAction called.');

        $payloadManager = new PayloadManager($this->request);
        $jsonPayload = $payloadManager->getJsonPayload();

        // check 'refs' for tags, if none, then return
        if (!strpos($jsonPayload->ref, 'tags')) {
            return new PlainResponse();
        }

        // check vendor exists, if not create new vendor
        $vendor = $this->entityManager
            ->getRepository('ZikulaExtensionLibraryModule:VendorEntity')
            ->findOneBy(array('owner' => $jsonPayload->repository->owner->name));
        if (!isset($vendor)) {
            // not found, create
            $vendor = new VendorEntity($jsonPayload->repository->owner->name);
            $this->entityManager->persist($vendor);
            Util::log(sprintf('Vendor (%s) created', $jsonPayload->repository->owner->name));
        } else {
            // found
            Util::log(sprintf('Vendor (%s) found', $jsonPayload->repository->owner->name));
        }

        // check extension exists, if not create new extension
        if ($vendor->hasExtensionById($jsonPayload->repository->id)) {
            Util::log(sprintf('Extension (%s) found', $jsonPayload->repository->id));
            $extension = $vendor->getExtensionById($jsonPayload->repository->id);
        } else {
            // not found, create new extension and assign to vendor
            $title = 'title'; // temp
            $type = 'm'; // temp
            $extension = new ExtensionEntity($vendor, (int)$jsonPayload->repository->id, $jsonPayload->repository->name, $title, $type);
            $vendor->addExtension($extension);
            $this->entityManager->persist($extension);
            Util::log(sprintf('Extension (%s) created', $jsonPayload->repository->id));
        }

        // compare version to newest available. If newer, add new version
        list(, , $semver) = explode('/', $jsonPayload->ref);
        $newestVersion = $extension->getNewestVersion();
        if (empty($newestVersion) || (version_compare($semver, $newestVersion->getSemver(), '>'))) {
            // add new version of extension
            $compatibility = '>=1.3.7'; // temp
            $licenses = '{}'; // temp
            $versionEntity = new ExtensionVersionEntity($extension, $semver, $compatibility, $licenses);
            $this->entityManager->persist($versionEntity);
            $extension->addVersion($versionEntity);
            Util::log(sprintf('Version %s added to extension %s', $semver, $jsonPayload->repository->id));
        } else {
            Util::log("The version was not added because it was the same or older than the current version.");
        }

        $this->entityManager->flush();

        return new PlainResponse();
    }

}
