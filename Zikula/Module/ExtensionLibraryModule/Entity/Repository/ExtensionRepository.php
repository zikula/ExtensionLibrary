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

    /**
     * This is the docblock that Christian forgot to write. :-)
     *
     * @param null $filter
     * @return array|\Doctrine\Common\Collections\ArrayCollection|\Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity[]
     */
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

    /**
     * get Extension objects based on searching the title for fragment provided
     *
     * @param array $fragments
     * @return array|null
     */
    public function getByFragment(array $fragments)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e, v')
            ->from('Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity', 'e')
            ->join('e.vendor', 'v');
        $or = $qb->expr()->orX();
        $i = 1;
        foreach ($fragments as $fragment) {
            $or->add($qb->expr()->like('e.title', '?' . $i));
            $qb->setParameter($i, '%' . $fragment . '%');
            $or->add($qb->expr()->like('v.title', '?' . ($i + 1)));
            $qb->setParameter($i + 1, '%' . $fragment . '%');
            $i = $i + 2;
        }
        $qb->where($or);
        $query = $qb->getQuery();

        try {
            $result = $query->getResult();
        } catch (\Exception $e) {
            Util::log("could not get result from getByFragment query: " . $e->getMessage());
            $result = null;
        }
        return $result;
    }
}