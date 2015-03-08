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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;

/**
 * Extension repository class.
 */
class ExtensionRepository extends EntityRepository
{
    /**
     * Get all extensions matching the provided $coreVersion and $extensionType or the user's core and extension
     * filter otherwise.
     *
     * @param string $orderBy default: 'title'
     * @param string $orderDir default: 'ASC'
     * @param null|integer $limit pager limit
     * @param null|integer $offset pager offset
     * @param null|string $coreVersion The core version to filter, defaults to the core selected by the user.
     * @param null|string $extensionType The extension type to filter, defaults to the extension type selected by the
     * user.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity[]
     */
    public function findAllMatchingFilter($orderBy = 'title', $orderDir = 'ASC', $limit = null, $offset = null, $coreVersion = null, $extensionType = null)
    {
        if (!isset($extensionType)) {
            $extensionType = Util::getExtensionTypeFilter();
        }
        if (!isset($coreVersion)) {
            $coreVersion = Util::getCoreVersionFilter();
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select('e', 'v')
            ->from('ZikulaExtensionLibraryModule:ExtensionEntity', 'e')
            ->join('e.versions', 'v');
        if (($extensionType != 'all') && (in_array($extensionType , array(ExtensionEntity::TYPE_MODULE, ExtensionEntity::TYPE_PLUGIN, ExtensionEntity::TYPE_THEME)))) {
            $qb->where($qb->expr()->eq('e.type', ':type'))
                ->setParameter('type', $extensionType);
        }
        if (isset($orderBy)) {
            $qb->orderBy("e.$orderBy", $orderDir);
        }
        if (!empty($offset)) {
            $qb->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $qb->setMaxResults($limit);
        }
        $extensions = new Paginator($qb);

        if ($coreVersion != 'all') {
            $extensions = Util::filterExtensions($extensions, $coreVersion);
        }

        return $extensions;
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
