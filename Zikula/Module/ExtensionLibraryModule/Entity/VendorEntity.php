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

namespace Zikula\Module\ExtensionLibraryModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Zikula\Module\ExtensionLibraryModule\Util;

/**
 * Vendor entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="el_vendor")
 */
class VendorEntity extends EntityAccess
{
    /**
     * id field - NOT auto-generated. The GitHub user id is used.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * vendor owner
     * taken from github repository owner name
     * must be unique
     *
     * @ORM\Column(type="string", length=128, unique=true)
     */
    private $owner;

    /**
     * owner name
     * supplied by vendor
     * can be null
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $ownerName;

    /**
     * owner email
     * supplied by vendor
     * can be null
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $ownerEmail;

    /**
     * owner email
     * supplied by vendor
     * can be null
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $ownerUrl;

    /**
     * vendor url
     * supplied by vendor
     * can be null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url = null;

    /**
     * vendor title
     * supplied by vendor
     * can be null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title = null;

    /**
     * local logo image path
     * can be null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo = null;

    /**
     * ownerName slug
     * automatically computed from $ownerName
     *
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Slug(fields={"ownerName"})
     */
    private $ownerNameSlug;

    /**
     * title slug
     * automatically computed from $title
     *
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Slug(fields={"title"})
     */
    private $titleSlug;

    /**
     * Collection of extensions provided by this vendor
     *
     * @ORM\OneToMany(targetEntity="ExtensionEntity", mappedBy="vendor", indexBy="id", cascade={"remove"})
     * @ORM\OrderBy({"repoName" = "ASC"})
     */
    private $extensions;

    /**
     * Constructor
     */
    public function __construct($id, $owner)
    {
        $this->id = $id;
        $this->owner = $owner;
        $this->extensions = new ArrayCollection();
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $ownerEmail
     */
    public function setOwnerEmail($ownerEmail)
    {
        $this->ownerEmail = $ownerEmail;
    }

    /**
     * @return string
     */
    public function getOwnerEmail()
    {
        return $this->ownerEmail;
    }

    /**
     * @param string $ownerName
     */
    public function setOwnerName($ownerName)
    {
        $this->ownerName = $ownerName;
    }

    /**
     * @return string
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * @param string $ownerUrl
     */
    public function setOwnerUrl($ownerUrl)
    {
        $this->ownerUrl = $ownerUrl;
    }

    /**
     * @return string
     */
    public function getOwnerUrl()
    {
        return $this->ownerUrl;
    }

    /**
     * @return string
     */
    public function getOwnerNameSlug()
    {
        return $this->ownerNameSlug;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if (!empty($this->title)) {
            return $this->title;
        } else {
            return $this->owner;
        }
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * @return null|string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        if (empty($this->logo)) {
            return "https://avatars.githubusercontent.com/u/{$this->id}?v=2&s=120";
        }
        return \ServiceUtil::get('router')->generate('zikulaextensionlibrarymodule_user_getimage', array('name' => $this->logo));
    }

    /**
     * @return string
     */
    public function getTitleSlug()
    {
        if (!empty($this->titleSlug)) {
            return $this->titleSlug;
        } else {
            return $this->owner;
        }
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return ArrayCollection
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Filter the extensions by core version and extension type.
     *
     * @param null|string $coreVersion      The core version to filter, defaults to the core selected by the user.
     * @param null|string $extensionType The extension type to filter, defaults to the extension type selected by the
     * user.
     *
     * @return ArrayCollection|\Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity[]
     */
    public function getExtensionsByFilter($coreVersion = null, $extensionType = null)
    {
        return Util::filterExtensions($this->extensions, $coreVersion, $extensionType);
    }

    /**
     * @param ExtensionEntity $extension
     */
    public function addExtension(ExtensionEntity $extension)
    {
        $this->extensions->add($extension);
    }

    /**
     * @param ExtensionEntity $extension
     */
    public function removeExtension(ExtensionEntity $extension)
    {
        $this->extensions->removeElement($extension);
    }

    /**
     * @param integer $extensionId
     *
     * @return boolean
     */
    public function hasExtensionById($extensionId)
    {
        return $this->extensions->containsKey($extensionId);
    }

    /**
     * @param integer $extensionId
     * @return ExtensionEntity|null
     */
    public function getExtensionById($extensionId)
    {
        return $this->extensions->get($extensionId);
    }

    /**
     * merge some properties of the manifest file
     * @param \stdClass $manifest
     */
    public function mergeManifest($manifest)
    {
        if (!empty($manifest->vendor)) {
            $this->title = !empty($manifest->vendor->title) ? $manifest->vendor->title : null;
            $this->url = !empty($manifest->vendor->url) ? $manifest->vendor->url : null;
            $this->logo = !empty($manifest->vendor->logo) ? $manifest->vendor->logo : null;
        }
    }

    /**
     * merge some properties of the composer file
     * @param \stdClass $composer
     */
    public function mergeComposer($composer)
    {
        if (!empty($composer->authors)) {
            foreach ($composer->authors as $author) {
                if (!empty($author->role) && ($author->role == "owner")) {
                    $this->ownerName = !empty($author->name) ? $author->name : null;
                    $this->ownerEmail = !empty($author->email) ? $author->email : null;
                    $this->ownerUrl = !empty($author->homepage) ? $author->homepage : null;
                    break;
                }
            }
        }
    }
}
