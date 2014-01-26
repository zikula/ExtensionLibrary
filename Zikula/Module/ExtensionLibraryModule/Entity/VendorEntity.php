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

/**
 * Vendor entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="el_vendor")
 */
class VendorEntity extends EntityAccess
{
    /**
     * constants defining verification status for vendor
     */
    const VERIFIED = 1;
    const UNVERIFIED = 0;
    const DENIED = -1;

    /**
     * id field
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * Associated Zikula Core user_id
     * can be empty array if the vendor has been unclaimed
     *
     * @ORM\Column(type="array")
     */
    private $userIds = array();

    /**
     * vendor url
     * supplied by vendor
     * can be null if the vendor has been unclaimed
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url = null;

    /**
     * vendor title
     * supplied by vendor
     * can be null if the vendor has been unclaimed
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title = null;

    /**
     * owner slug
     * automatically computed from $owner
     *
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Slug(fields={"owner"})
     */
    private $ownerSlug;

    /**
     * vendor verification status
     *
     * @ORM\Column(type="integer")
     */
    private $verified = self::UNVERIFIED;

    /**
     * Collection of extensions provided by this vendor
     *
     * @ORM\OneToMany(targetEntity="ExtensionEntity", mappedBy="vendor", indexBy="repositoryId", cascade={"remove"})
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $extensions;

    /**
     * Constructor
     */
    public function __construct($owner)
    {
        $this->owner = $owner;
        $this->extensions = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getOwnerSlug()
    {
        return $this->ownerSlug;
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
        return $this->title;
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
     * @param integer $userId
     */
    public function addUserId($userId)
    {
        if (!in_array($userId, $this->userIds)) {
            $this->userIds[] = $userId;
        }
    }

    /**
     * @param integer $userId
     */
    public function removeUserId($userId)
    {
        $key = array_search($userId, $this->userIds);
        if (false !== $key) {
            unset($this->userIds[$key]);
        }
    }

    /**
     * @return array
     */
    public function getUserIds()
    {
        return $this->userIds;
    }

    public function setVerified()
    {
        $this->verified = self::VERIFIED;
    }

    public function setDenied()
    {
        $this->verified = self::DENIED;
    }

    public function isVerified()
    {
        return ($this->verified == self::VERIFIED);
    }

    public function isDenied()
    {
        return ($this->verified == self::DENIED);
    }

    /**
     * @return ArrayCollection
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param ExtensionEntity $extension
     */
    public function addExtension(ExtensionEntity $extension)
    {
        $this->extensions->add($extension);
//        $extension->setVendor($this);
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
}
