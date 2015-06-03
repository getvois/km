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

    public function getHotelPagesByCityBounds($lang, $city, $trLat, $trLong, $blLat, $blLong)
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


        $dql .= " AND (p.latitude >= :blLat AND p.latitude <= :trLat) ";
        $dql .= " AND (p.longitude >= :blLong AND p.longitude <= :trLong) ";

        $dql .= " AND (p.city = :city OR p.cityParish = :city) ";

        if ($lang) $dql .= " AND nt.lang = :lang ";
        $dql .= ' ORDER BY p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setParameter(':blLat', $blLat);
        $query->setParameter(':trLat', $trLat);
        $query->setParameter(':blLong', $blLong);
        $query->setParameter(':trLong', $trLong);
        $query->setParameter(':city', $city);

        $objects = $query->getResult();

        if(!$objects) $objects = [];

        return $objects;
    }

    /**
     * @param $lang
     * @param $trLat
     * @param $trLong
     * @param $blLat
     * @param $blLong
     * @return \Sandbox\WebsiteBundle\Entity\Pages\HotelPage[]
     */
    public function getHotelPagesByBounds($lang, $trLat, $trLong, $blLat, $blLong, $mapCategoryId = null)
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


        $dql .= " AND (p.latitude >= :blLat AND p.latitude <= :trLat) ";
        $dql .= " AND (p.longitude >= :blLong AND p.longitude <= :trLong) ";

        if($mapCategoryId){
            $dql .= " AND p.mapCategory = :map ";
        }

        if ($lang) $dql .= " AND nt.lang = :lang ";
        $dql .= ' ORDER BY p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        if($mapCategoryId) $query->setParameter(':map', $mapCategoryId);

        $query->setParameter(':blLat', $blLat);
        $query->setParameter(':trLat', $trLat);
        $query->setParameter(':blLong', $blLong);
        $query->setParameter(':trLong', $trLong);

        $objects = $query->getResult();

        if(!$objects) $objects = [];

        return $objects;
    }

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

        if(!$objects) $objects = [];

        return $objects;
    }

    /**
     * @param $lang
     * @param $hotelId
     * @return \Sandbox\WebsiteBundle\Entity\Pages\HotelPage[]
     */
    public function getHotelPage($lang, $hotelId)
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

        $dql .= " AND p.hotelId = :hotel ";

        if ($lang) $dql .= " AND nt.lang = :lang ";
        $dql .= ' ORDER BY p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setParameter(':hotel', $hotelId);

        $query->setMaxResults(1);
        $objects = $query->getOneOrNullResult();

        return $objects;
    }

    public function getHotelPagesByParent($lang,Node $node, $orderBy = 'p.date DESC')
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
        $dql .= " ORDER BY $orderBy ";

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setParameter(':parent', $node->getId());

        $objects = $query->getResult();

        return $objects;
    }

    public function getHotelPagesByMapCategory($lang, $mapCategoryId)
    {
        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\OfferPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\OfferPage\'
AND nt.online = 1';


        if ($lang) $dql .= " AND nt.lang = :lang ";

        $dql .= ' AND p.mapCategory = :map';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        $query->setParameter(':map', $mapCategoryId);

        $objects = $query->getResult();

        if(!$objects) $objects = [];

        return $objects;
    }

}
