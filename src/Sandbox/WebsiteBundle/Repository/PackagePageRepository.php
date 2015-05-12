<?php

namespace Sandbox\WebsiteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticlePageRepository;
use Kunstmaan\NodeBundle\Entity\Node;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;

/**
 * Repository class for the NewsPage
 */
class PackagePageRepository extends EntityRepository
{
    public function filter($lang, $duration, $city)
    {
        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\PackagePage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\PackagePage\'
AND nt.online = 1
AND p.duration = :duration
';


        if ($lang) $dql .= " AND nt.lang = :lang ";
        $dql .= ' ORDER BY p.orderNumber DESC, p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setParameter(':duration', $duration);

        $objects = $query->getResult();

        return $objects;
    }

    /**
     * @param $lang
     * @return PackagePage[]
     */
    public function getPackagePages($lang)
    {
        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\PackagePage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\PackagePage\'
AND nt.online = 1';


        if ($lang) $dql .= " AND nt.lang = :lang ";
        $dql .= ' ORDER BY p.orderNumber DESC, p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $objects = $query->getResult();

        return $objects;
    }

    /**
     * @param $lang
     * @param $packageId
     * @return \Sandbox\WebsiteBundle\Entity\Pages\PackagePage[]
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPackagePage($lang, $packageId)
    {
        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\PackagePage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\PackagePage\'
AND nt.online = 1';

        $dql .= " AND p.packageId = :package ";

        if ($lang) $dql .= " AND nt.lang = :lang ";
        $dql .= ' ORDER BY p.orderNumber DESC, p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        $query->setParameter(':package', $packageId);

        $query->setMaxResults(1);
        $objects = $query->getOneOrNullResult();

        return $objects;
    }

    public function getPackagesByParent($lang, Node $node)
    {
        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\PackagePage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\PackagePage\'
AND nt.online = 1';

        $dql .= ' AND n.parent = :parent';

        if ($lang) $dql .= " AND nt.lang = :lang ";
        $dql .= ' ORDER BY p.orderNumber DESC, p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setParameter(':parent', $node->getId());

        $objects = $query->getResult();

        return $objects;
    }
}
