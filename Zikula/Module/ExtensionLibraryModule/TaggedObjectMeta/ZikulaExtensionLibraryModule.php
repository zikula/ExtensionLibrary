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

namespace Zikula\Module\ExtensionLibraryModule\TaggedObjectMeta;

use DateUtil;
use ModUtil;
use ZLanguage;
use Zikula\Core\ModUrl;

class ZikulaExtensionLibraryModule extends \Tag_AbstractTaggedObjectMeta
{

    public function __construct($objectId, $areaId, $module, $urlString = null, ModUrl $urlObject = null)
    {
        parent::__construct($objectId, $areaId, $module, $urlString, $urlObject);
        $entityManager = \ServiceUtil::get('doctrine.entitymanager');
        $extension = $entityManager->getRepository("ZikulaExtensionLibraryModule:ExtensionEntity")->findOne($objectId);
        $this->setObjectTitle($extension->getTitle());
        $this->setObjectDate($extension->getUpdated());
    }

    public function setObjectTitle($title)
    {
        $this->title = $title;
    }

    public function setObjectDate($date = null)
    {
        $this->date = DateUtil::formatDatetime($date, 'datetimebrief');
    }

    public function setObjectAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Override the method to present specialized link
     * @return string
     */
    public function getPresentationLink()
    {
        $title = $this->getTitle();
        $date = $this->getDate();
        $link = null;
        if (!empty($title)) {
            $urlObject = $this->getUrlObject();
            $modinfo = ModUtil::getInfoFromName('ZikulaExtensionLibraryModule');
            $link = "{$modinfo['displayname']}: <a href='{$urlObject->getUrl()}'>{$title}</a> ({$date})";
        }

        return $link;
    }

}
