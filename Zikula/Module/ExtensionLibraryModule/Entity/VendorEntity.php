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
     * The vendor's GitHub name - IMPORTANT: These names aren't unique: If a GitHub user deletes or renames his account,
     * the name can be taken by another user.
     *
     * @ORM\Column(type="string", length=128)
     */
    private $gitHubName;

    /**
     * vendor url
     * supplied by vendor
     * can be null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url = null;

    /**
     * vendor email
     * supplied by vendor
     * can be null
     *
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $email = null;

    /**
     * vendor title
     * supplied by vendor
     * set to the GitHub name if empty.
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * User supplied url to the vendor logo.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo = null;

    /**
     * local logo image file name.
     * can be null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logoFileName = null;

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
    public function __construct($id, $gitHubName)
    {
        $this->id = $id;
        $this->gitHubName = $gitHubName;
        $this->title = $gitHubName;
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
        if (empty($this->logoFileName)) {
            return "https://avatars.githubusercontent.com/u/{$this->id}?v=2&s=120";
        }
        return \ServiceUtil::get('router')->generate('zikulaextensionlibrarymodule_user_getimage', array('name' => $this->logoFileName));
    }

    /**
     * @return string
     */
    public function getTitleSlug()
    {
        return $this->titleSlug;
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
     * merge some properties of the composer file. Only do so if the vendor is new!!
     * @param \stdClass $composer
     */
    public function mergeComposer($composer)
    {
        if (!empty($composer->authors)) {
            foreach ($composer->authors as $author) {
                if (!empty($author->role) && ($author->role == "owner")) {
                    if (!empty($author->name)) {
                        $this->title = $author->name;
                    }
                    if (!empty($author->email)) {
                        $this->email = $author->email;
                    }
                    if (!empty($author->homepage)) {
                        $this->url = $author->homepage;
                    }

                    return;
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getGitHubName()
    {
        return $this->gitHubName;
    }

    /**
     * @param mixed $gitHubName
     */
    public function setGitHubName($gitHubName)
    {
        $this->gitHubName = $gitHubName;
    }

    /**
     * @return mixed
     */
    public function getLogoFileName()
    {
        return $this->logoFileName;
    }

    /**
     * @param mixed $logoFileName
     */
    public function setLogoFileName($logoFileName)
    {
        $this->logoFileName = $logoFileName;
    }
}
