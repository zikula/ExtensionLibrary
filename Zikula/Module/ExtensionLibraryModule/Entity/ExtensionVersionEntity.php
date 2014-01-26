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
     * id field
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * extension version
     *
     * @ORM\Column(type="string", length=10)
     */
    private $version = '';

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $created;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $compatibilty;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $license;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status; // active/not

    /**
     * @ORM\ManyToOne(targetEntity="ExtensionEntity", inversedBy="versions")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $extension;

    /**
     * Constructor
     */
    public function __construct(ExtensionEntity $extension, $version)
    {
        $this->extension = $extension;
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

}
