<?php

namespace Sandbox\WebsiteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticlePageRepository;
use Sandbox\WebsiteBundle\Entity\Host;

/**
 * Repository class for the NewsPage
 */
class PackagePageRepository extends EntityRepository
{
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
}