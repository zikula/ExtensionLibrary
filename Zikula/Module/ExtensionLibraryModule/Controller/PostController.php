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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\ModUrl;
use Zikula\Core\Response\PlainResponse;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionVersionEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\VendorEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;
use Zikula\Module\ExtensionLibraryModule\Util;
use Zikula\Module\ExtensionLibraryModule\Manager\ManifestManager;
use Zikula\Module\ExtensionLibraryModule\Manager\ComposerManager;
use Zikula\Module\ExtensionLibraryModule\Manager\PayloadManager;
use Zikula\Module\ExtensionLibraryModule\Manager\ImageManager;
use ModUtil;
use Zikula\Core\Hook\ProcessHook;
use ZLanguage;

/**
 * GitHub Webhook access points.
 */
class PostController extends \Zikula_AbstractController
{
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
            Util::log('processInboundAction aborted. no tags in payload');
            return new PlainResponse();
        }

        // fetch the manifest, validate and get contents
        $manifestManager = new ManifestManager(
            $jsonPayload->repository->owner->name,
            $jsonPayload->repository->name,
            $jsonPayload->ref
        );
        if ($manifestManager->hasDecodingErrors()) {
            Util::log("{$jsonPayload->repository->name}: Cannot decode manifest. Violations:", Util::LOG_PROD);
            foreach ($manifestManager->getDecodingErrors() as $error) {
                Util::log($error, Util::LOG_PROD);
            }
            return new PlainResponse();
        }

        $manifestContent = $manifestManager->getContent();
        if (empty($manifestContent)) {
            Util::log("{$jsonPayload->repository->name}: Manifest file does not validate. Violations:", Util::LOG_PROD);
            foreach ($manifestManager->getValidationErrors() as $error) {
                Util::log(sprintf("[%s] %s", $error['property'], $error['message']), Util::LOG_PROD);
            }
            return new PlainResponse();
        }

        // fetch the composer.json file, validate and get contents
        $composerManager = new ComposerManager(
            $jsonPayload->repository->owner->name,
            $jsonPayload->repository->name,
            $jsonPayload->ref,
            $manifestContent->version->composerpath
        );
        $composerContent = $composerManager->getContent();
        if (empty($composerContent)) {
            Util::log("{$jsonPayload->repository->name}: Composer file does not validate. Violations:", Util::LOG_PROD);
            foreach ($composerManager->getValidationErrors() as $error) {
                Util::log(sprintf("[%s] %s", $error['property'], $error['message']), Util::LOG_PROD);
            }
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
        if (!empty($manifestContent->vendor) && !empty($manifestContent->vendor->logo)) {
            $imageManager = new ImageManager($manifestContent->vendor->logo);
            if ($imageManager->import()) {
                $manifestContent->vendor->logo = $imageManager->getName();
            } else {
                $manifestContent->vendor->logo = '';
                Util::log("{$jsonPayload->repository->name}: Invalid vendor logo. Violations:", Util::LOG_PROD);
                foreach ($imageManager->getValidationErrors() as $error) {
                    Util::log($error, Util::LOG_PROD);
                }
            }
        }
        $vendor->mergeManifest($manifestContent);
        $vendor->mergeComposer($composerContent);

        // check extension exists, if not create new extension
        if ($vendor->hasExtensionById($jsonPayload->repository->id)) {
            Util::log(sprintf('Extension (%s) found', $jsonPayload->repository->id));
            $extension = $vendor->getExtensionById($jsonPayload->repository->id);
        } else {
            // not found, create new extension and assign to vendor
            $extension = new ExtensionEntity(
                $vendor,
                (int)$jsonPayload->repository->id,
                $jsonPayload->repository->name,
                $manifestContent->extension->title,
                $composerContent->description,
                $composerContent->type
            );
            $vendor->addExtension($extension);
            $this->entityManager->persist($extension);
            Util::log(sprintf('Extension (%s) created', $jsonPayload->repository->id));
        }
        if (!empty($manifestContent->extension->icon)) {
            $imageManager = new ImageManager($manifestContent->extension->icon);
            if ($imageManager->import()) {
                $manifestContent->extension->icon = $imageManager->getName();
            } else {
                $manifestContent->extension->icon = '';
                Util::log("{$jsonPayload->repository->name}: Invalid extension icon. Violations:", Util::LOG_PROD);
                foreach ($imageManager->getValidationErrors() as $error) {
                    Util::log($error, Util::LOG_PROD);
                }
            }
        }
        $extension->mergeManifest($manifestContent);
        $extension->mergeComposer($composerContent);

        // compare version to newest available. If newer, add new version
        list(, , $semver) = explode('/', $jsonPayload->ref);
        $newestVersion = $extension->getNewestVersion();
        if (empty($newestVersion) || (version_compare($semver, $newestVersion->getSemver(), '>'))) {
            // add new version of extension
            $version = new ExtensionVersionEntity(
                $extension,
                $semver,
                $manifestContent->version->compatibility,
                $composerContent->license
            );
            $this->entityManager->persist($version);
            $extension->addVersion($version);
            $version->mergeManifest($manifestContent);
            $version->mergeComposer($composerContent);
            Util::log(sprintf('Version %s added to extension %s',
                $semver,
                $extension->getTitle()), Util::LOG_PROD);
        } else {
            Util::log(sprintf("(%s) The version %s was not added because it was the same or older than the current version (%s).",
                $extension->getTitle(),
                $semver,
                $newestVersion->getSemver()), Util::LOG_PROD);
            // return without flushing since there should be no changes if version isn't new
            return new PlainResponse();
        }

        $this->entityManager->flush();

        // add keywords via the Tag module when hooked
        /** @var $hookDispatcher \Zikula\Component\HookDispatcher\StorageInterface */
        $hookDispatcher = \ServiceUtil::get('hook_dispatcher');
        $url = new ModUrl($this->name, 'user', 'display', ZLanguage::getLanguageCode(), array('extension_slug' => $extension->getTitleSlug()));
        if (ModUtil::available('Tag')) {
            $bindings = $hookDispatcher->getBindingsBetweenOwners($this->name, 'Tag');
            if (count($bindings) > 0) {
                $areaId = $hookDispatcher->getAreaId('subscriber.el.ui_hooks.extension');
                $args = array(
                    'module' => $this->name,
                    'objectId' => $extension->getId(),
                    'areaId' => $areaId,
                    'objUrl' => $url,
                    'hookdata' => array('tags' => $manifestContent->version->keywords),
                );
                ModUtil::apiFunc('Tag', 'user', 'tagObject', $args);
            }
        }

        if (ModUtil::available('ZikulaDizkusModule')) {
            $bindings = $hookDispatcher->getBindingsBetweenOwners($this->name, 'ZikulaDizkusModule');
            if (count($bindings) > 0) {
                $this->request->request->set('dizkus', array('createTopic' => 1));
            }
        }
        $this->dispatchHooks('el.ui_hooks.community.process_edit', new ProcessHook($extension->getId(), $url));

        return new PlainResponse();
    }

    /**
     * @Route("/core-endpoint")
     * @Method("POST")
     */
    public function coreHookEndpoint(Request $request)
    {
        $payloadManager = new PayloadManager($this->request);
        $jsonPayload = $payloadManager->getJsonPayload();

        $securityToken = $this->getVar('github_webhook_token');
        if (!empty($securityToken)) {
            $signature = $request->headers->get('X-Hub-Signature');
            if (empty($signature)) {
                return new Response('Missing security token!', 400);
            }
            $computedSignature = $this->computeSignature($jsonPayload, $securityToken);

            if (!$this->secure_equals($computedSignature, $signature)) {
                return new Response('Signature did not match!', 400);
            }
        }

        $event = $this->request->headers->get('X-Github-Event');
        if (empty($event)) {
            return new Response('"X-Github-Event" header is missing!', 400);
        }
        $useragent = $request->headers->get('User-Agent');
        if (strpos($useragent, 'GitHub Hookshot') !== 0) {
            // User agent does not match "GitHub Hookshot*"
            return new Response('User-Agent not allowed!', 400);
        }

        switch ($event) {
            case 'ping':
                return new Response('Ping successful!');
            case 'release':
            case 'create':
                // @todo Handle created tag / release!
                return new Response('Everything ok!');
            default:
                // We do not listen to that event.
                return new Response('Event ignored!');
        }
    }

    private function computeSignature($payload, $securityToken)
    {
        $hash = 'sha1=' . hash_hmac('sha1', $payload, $securityToken);

        return $hash;
    }

    // Compares two strings $a and $b in length-constant time.
    // https://crackstation.net/hashing-security.htm#slowequals
    private function secure_equals($a, $b)
    {
        $diff = strlen($a) ^ strlen($b);
        for($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }

        return $diff === 0;
    }

}
