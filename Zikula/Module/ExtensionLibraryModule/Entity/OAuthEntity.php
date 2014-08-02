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
 * @ORM\Entity()
 * @ORM\Table(name="el_oauth")
 */
class OAuthEntity extends EntityAccess
{
    const TYPE_TOKEN = 1;

    const TYPE_STATE = 2;

    /**
     * id field
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $sessId;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $type;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $service;

    /**
     * @ORM\Column(type="object")
     * @var object
     */
    private $value;

    public function __construct($sessId, $type, $service, $value)
    {
        $this->sessId = $sessId;
        $this->type = $type;
        $this->value = $value;
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $sessId
     */
    public function setSessId($sessId)
    {
        $this->sessId = $sessId;
    }

    /**
     * @return string
     */
    public function getSessId()
    {
        return $this->sessId;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param object $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return object
     */
    public function getValue()
    {
        return $this->value;
    }
}
