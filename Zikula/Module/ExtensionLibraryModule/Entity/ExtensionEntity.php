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

use Doctrine\Common\Collections\ArrayCollection;
use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Extension entity class
 *
 * @ORM\Entity(repositoryClass="Zikula\Module\ExtensionLibraryModule\Entity\Repository\ExtensionRepository")
 * @ORM\Table(name="el_extension")
 */
class ExtensionEntity extends EntityAccess
{
    /**
     * constants defining the type of extension
     */
    const TYPE_MODULE = 'm';
    const TYPE_THEME = 't';
    const TYPE_PLUGIN = 'p';

    /**
     * id field
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * github repository id
     * provided from github repository id in constructor
     *
     * @ORM\Column(type="integer", unique=true)
     */
    private $repositoryId;

    /**
     * extension name
     * taken from github repository name
     * must be unique
     *
     * @ORM\Column(type="string", length=128, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated;

    /**
     * name slug
     * automatically computed from $name
     *
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Slug(fields={"name"})
     */
    private $nameSlug;

    /**
     * extension title
     * supplied by vendor
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * title slug
     * automatically computed from $title
     *
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Slug(fields={"title"})
     */
    private $titleSlug;

    /**
     * extension type
     * supplied by vendor
     * must be one of the TYPE_* constants above
     *
     * @ORM\Column(type="string", length=1)
     */
    private $type = self::TYPE_MODULE;

    /**
     * extension url
     * supplied by vendor
     * can be null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url = null;

    /**
     * local icon image path
     * can be null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon = null;

    /**
     * extension version
     *
     * @ORM\OneToMany(targetEntity="ExtensionVersionEntity", mappedBy="extension", indexBy="semver", cascade={"remove"})
     * @ORM\OrderBy({"semver" = "DESC"})
     */
    private $versions;

    /**
     * @ORM\ManyToOne(targetEntity="VendorEntity", inversedBy="extensions")
     */
    private $vendor;

    /**
     * Constructor
     */
    public function __construct(VendorEntity $vendor, $id, $name, $title, $type = self::TYPE_MODULE)
    {
        $this->vendor = $vendor;
        $this->repositoryId = $id;
        $this->name = $name;
        $this->title= $title;
        $this->type = $type;
        $this->updated = new \DateTime();
        $this->versions = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getRepositoryId()
    {
        return $this->repositoryId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getNameSlug()
    {
        return $this->nameSlug;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return VendorEntity
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @return ArrayCollection|ExtensionVersionEntity[]
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @return ExtensionVersionEntity|boolean
     */
    public function getNewestVersion()
    {
        return $this->versions->first();
    }

    /**
     * @param ExtensionVersionEntity $version
     */
    public function addVersion(ExtensionVersionEntity $version)
    {
        $this->versions->add($version);
    }

    /**
     * @param ExtensionVersionEntity $version
     */
    public function removeExtension(ExtensionVersionEntity $version)
    {
        $this->versions->removeElement($version);
    }

    /**
     * @param string $semver
     * @return ExtensionEntity|null
     */
    public function getVersionBySemver($semver)
    {
        return $this->versions->get($semver);
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
            return $this->name;
        }
    }

    /**
     * @return string
     */
    public function getTitleSlug()
    {
        if (!empty($this->titleSlug)) {
            return $this->titleSlug;
        } else {
            return $this->nameSlug;
        }
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if (($type == self::TYPE_MODULE) || ($type == self::TYPE_PLUGIN) || ($type == self::TYPE_THEME)) {
            $this->type = $type;
        } else {
            $this->type = self::TYPE_MODULE;
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        $types = array(
            self::TYPE_MODULE => 'Module',
            self::TYPE_THEME => 'Theme',
            self::TYPE_PLUGIN => 'Plugin',
        );
        return $types[$this->type];
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
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        if (empty($this->icon)) {
            return 'modules/Zikula/Module/ExtensionLibraryModule/Resources/public/images/zikula.png';
        }
        return $this->icon;
    }

    /**
     * merge some properties of the manifest
     * @param $manifest
     */
    public function mergeManifest($manifest)
    {
        $this->url = !empty($manifest->extension->url) ? $manifest->extension->url : null;
        $this->icon = !empty($manifest->extension->icon) ? $manifest->extension->icon : null;
    }

}
