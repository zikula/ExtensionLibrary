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

namespace Zikula\Module\ExtensionLibraryModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\Module\ExtensionLibraryModule\Util;

/**
 * Extension repository class.
 */
class ExtensionRepository extends EntityRepository
{
    public function findAllMatchingCoreFilter($filter = null)
    {
        if (!isset($filter)) {
            $filter = Util::getChosenCore();
        }
        if ($filter === 'all') {
            return $this->findAll();
        }

        return Util::filterExtensionsByCore($this->findAll(), $filter);
    }
}