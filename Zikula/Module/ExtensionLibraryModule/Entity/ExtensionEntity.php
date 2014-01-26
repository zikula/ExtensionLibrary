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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Extension entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="el_extension")
 */
class ExtensionEntity extends EntityAccess
{
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
     * name slug
     * automatically computed from $name
     *
     * @ORM\Column(type="string", length=128)
     * @Gedmo\Slug(fields={"name"})
     */
    private $nameSlug;

    /**
     * extension version
     *
     * @ORM\Column(type="string", length=10)
     */
    private $version = '';

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="change", field={"version"})
     */
    private $updated;

    private $url;
    private $description;
    private $compatibilty;
    private $license;
    private $status; // active/not

    /**
     * @ORM\ManyToOne(targetEntity="VendorEntity", inversedBy="extensions")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $vendor;

    /**
     * Constructor
     */
    public function __construct(VendorEntity $vendor, $id, $name, $version)
    {
        $this->vendor = $vendor;
        $this->repositoryId = $id;
        $this->name = $name;
        $this->version = $version;
        $this->updated = new \DateTime();
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
     * @param mixed $nameSlug
     */
    public function setNameSlug($nameSlug)
    {
        $this->nameSlug = $nameSlug;
    }

    /**
     * @return mixed
     */
    public function getNameSlug()
    {
        return $this->nameSlug;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return VendorEntity
     */
    public function getVendor()
    {
        return $this->vendor;
    }
}
