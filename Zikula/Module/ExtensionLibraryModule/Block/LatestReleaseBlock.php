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

namespace Zikula\Module\ExtensionLibraryModule\Block;

use BlockUtil;
use ModUtil;
use SecurityUtil;
use vierbergenlars\SemVer\version;
use Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity;
use Zikula_Controller_AbstractBlock;

class LatestReleaseBlock extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('ZikulaExtensionLibraryModule:latestRelease:', 'Block title::');
    }

    /**
     * get information on block
     */
    public function info()
    {
        return array(
            'text_type' => 'latestRelease',
            'module' => 'ZikulaExtensionLibraryModule',
            'text_type_long' => $this->__('Latest release button'),
            'allow_multiple' => true,
            'form_content' => false,
            'form_refresh' => false,
            'show_preview' => true,
            'admin_tableless' => true
        );
    }

    private function majorMinorPatchEqual(version $v1, version $v2)
    {
        $v1 = $v1->getMajor() . "." . $v1->getMinor() . "." . $v1->getPatch();
        $v2 = $v2->getMajor() . "." . $v2->getMinor() . "." . $v2->getPatch();

        return $v1 === $v2;
    }

    /**
     * display block
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('ZikulaExtensionLibraryModule:latestRelease:', "$blockinfo[title]::", ACCESS_OVERVIEW) || !ModUtil::available('ZikulaExtensionLibraryModule')) {
            return;
        }

        $outdatedReleases = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findBy(array('state' => CoreReleaseEntity::STATE_OUTDATED));

        $supportedReleases = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findBy(array('state' => CoreReleaseEntity::STATE_SUPPORTED));
        usort($supportedReleases, function (CoreReleaseEntity $a, CoreReleaseEntity $b) {
            $a = new version($a->getSemver());
            $b = new version($b->getSemver());

            return version::compare($b, $a);
        });

        $preReleases = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findBy(array('state' => CoreReleaseEntity::STATE_PRERELEASE));
        foreach ($preReleases as $key => $preRelease) {
            $preReleaseVersion = new version($preRelease->getSemver());
            foreach ($supportedReleases as $supportedRelease) {
                $supportedReleaseVersion = new version($supportedRelease->getSemver());
                if ($this->majorMinorPatchEqual($preReleaseVersion, $supportedReleaseVersion)) {
                    // There already is a supported release. Hide the prerelease.
                    unset($preReleases[$key]);
                }
            }
            foreach ($outdatedReleases as $outdatedRelease) {
                $outdatedReleaseVersion = new version($outdatedRelease->getSemver());
                if ($this->majorMinorPatchEqual($preReleaseVersion, $outdatedReleaseVersion)) {
                    // There already is an outdated release. Hide the prerelease.
                    unset($preReleases[$key]);
                }
            }
        }
        $developmentReleases = $this->entityManager->getRepository('ZikulaExtensionLibraryModule:CoreReleaseEntity')->findBy(array('state' => CoreReleaseEntity::STATE_DEVELOPMENT));
        foreach ($developmentReleases as $key => $developmentRelease) {
            $developmentReleasesVersion = new version($developmentRelease->getSemver());
            foreach ($supportedReleases as $supportedRelease) {
                $supportedReleaseVersion = new version($supportedRelease->getSemver());
                if ($this->majorMinorPatchEqual($developmentReleasesVersion, $supportedReleaseVersion)) {
                    // There already is a supported release. Hide the prerelease.
                    unset($developmentReleases[$key]);
                }
            }
            foreach ($outdatedReleases as $outdatedRelease) {
                $outdatedReleaseVersion = new version($outdatedRelease->getSemver());
                if ($this->majorMinorPatchEqual($developmentReleasesVersion, $outdatedReleaseVersion)) {
                    // There already is an outdated release. Hide the prerelease.
                    unset($developmentReleases[$key]);
                }
            }
            foreach ($preReleases as $preRelease) {
                $preReleaseVersion = new version($preRelease->getSemver());
                if ($this->majorMinorPatchEqual($developmentReleasesVersion, $preReleaseVersion)) {
                    // There already is an outdated release. Hide the prerelease.
                    unset($developmentReleases[$key]);
                }
            }
        }

        if (!empty($supportedReleases)) {
            $this->view->assign('supportedRelease', $supportedReleases[0]);
        }
        if (!empty($preReleases)) {
            $this->view->assign('preRelease', $preReleases[0]);
        }
        if (!empty($developmentReleases)) {
            $this->view->assign('developmentRelease', $developmentReleases[0]);
        }
        $this->view->assign('id', uniqid());
        $blockinfo['content'] = $this->view->fetch('Blocks/latestrelease.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }

}
