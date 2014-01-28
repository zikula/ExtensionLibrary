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
 * ExtensionVersion entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="el_extension_version")
 */
class ExtensionVersionEntity extends EntityAccess
{
    /**
     * constants defining the status of this version
     */
    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * id field
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * extension version (semver)
     * taken from "refs" in payload POST
     *
     * @ORM\Column(type="string", length=10)
     */
    private $semver = '';

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $created;

    /**
     * json array of related urls
     * supplied by vendor
     * 
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $urls;

    /**
     * extension version description
     * supplied by vendor
     * 
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * string defining Zikula Core version compatibility
     * supplied by vendor
     * 
     * @ORM\Column(type="string")
     */
    private $compatibility;

    /**
     * json array of licenses
     * supplied by vendor
     * 
     * @ORM\Column(type="json_array")
     */
    private $licenses;

    /**
     * json array of contributors
     * supplied by vendor
     * 
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $contributors;

    /**
     * json array of extension dependencies
     * supplied by vendor
     * 
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $dependencies;

    /**
     * active status of version
     * 
     * @ORM\Column(type="integer")
     */
    private $status = self::ACTIVE;

    /**
     * the number of times this version has been viewed
     *
     * @ORM\Column(type="integer")
     */
    private $impressions;

    /**
     * the related extension
     * 
     * @ORM\ManyToOne(targetEntity="ExtensionEntity", inversedBy="versions")
     */
    private $extension;

    /**
     * Constructor
     */
    public function __construct(ExtensionEntity $extension, $semver, $compatibility, $licenses)
    {
        $this->extension = $extension;
        $this->semver = $semver;
        $this->compatibility = $compatibility;
        $this->licenses = $licenses;
        $this->impressions = 0;
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
    public function getSemver()
    {
        return $this->semver;
    }

    /**
     * @param string $compatibility
     */
    public function setCompatibility($compatibility)
    {
        $this->compatibility = $compatibility;
    }

    /**
     * @return string
     */
    public function getCompatibility()
    {
        return $this->compatibility;
    }

    /**
     * @param json $contributors
     */
    public function setContributors($contributors)
    {
        $this->contributors = $contributors;
    }

    /**
     * @return json
     */
    public function getContributors()
    {
        return $this->contributors;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param json $dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @return json
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return ExtensionEntity
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param json $licenses
     */
    public function setLicenses($licenses)
    {
        $this->licenses = $licenses;
    }

    /**
     * @return json
     */
    public function getLicenses()
    {
        return $this->licenses;
    }

    /**
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param json $urls
     */
    public function setUrls($urls)
    {
        $this->urls = $urls;
    }

    /**
     * @return json
     */
    public function getUrls()
    {
        return $this->urls;
    }

    public function incrementImpressions()
    {
        $this->impressions++;
    }

    public function decrementImpressions()
    {
        $this->impressions--;
    }

    /**
     * @param integer $impressions
     */
    public function setImpressions($impressions)
    {
        $this->impressions = $impressions;
    }

    /**
     * @return integer
     */
    public function getImpressions()
    {
        return $this->impressions;
    }

    /**
     * merge some properties of the manifest
     * @param $manifest
     */
    public function mergeManifest($manifest)
    {
        $this->description = !empty($manifest->version->description) ? $manifest->version->description : null;
        $this->urls = !empty($manifest->version->urls) ? $manifest->version->urls : null;
        $this->contributors = !empty($manifest->version->contributors) ? $manifest->version->contributors : null;
        $this->dependencies = !empty($manifest->version->dependencies) ? $manifest->version->dependencies : null;
    }

}
