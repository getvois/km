<?php

namespace Sandbox\WebsiteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticlePageRepository;
use Kunstmaan\NodeBundle\Entity\Node;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;

/**
 * Repository class for the NewsPage
 */
class HotelPageRepository extends EntityRepository
{
    /**
     * @param $lang
     * @param int $online
     * @return \Sandbox\WebsiteBundle\Entity\Pages\HotelPage[]
     */
    public function getHotelPages($lang)
    {
        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\HotelPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\HotelPage\'
AND nt.online = 1';


        if ($lang) $dql .= " AND nt.lang = :lang ";
        $dql .= ' ORDER BY p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $objects = $query->getResult();

        return $objects;
    }

    public function getHotelPagesByParent($lang,Node $node)
    {
        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\HotelPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\HotelPage\'
AND nt.online = 1';

        $dql .= " AND n.parent = :parent ";

        if ($lang) $dql .= " AND nt.lang = :lang ";
        $dql .= ' ORDER BY p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setParameter(':parent', $node->getId());

        $objects = $query->getResult();

        return $objects;
    }
}
