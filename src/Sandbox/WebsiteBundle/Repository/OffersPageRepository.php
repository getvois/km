<?php

namespace Sandbox\WebsiteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticlePageRepository;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;

/**
 * Repository class for the NewsPage
 */
class OffersPageRepository extends EntityRepository
{
    public function getTotalPages($lang, $originalLang = null)
    {
        $dql = "SELECT COUNT(p.id)
FROM Sandbox\WebsiteBundle\Entity\Pages\OfferPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\OfferPage\'
AND nt.online = 1';


        if ($lang) $dql .= " AND nt.lang = :lang ";
        if($originalLang) $dql .= ' AND p.originalLanguage = :originalLang ';

        $dql .= ' AND p.expirationDate >= :date ';

        $dql .= ' ORDER BY p.price ASC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        if($originalLang) $query->setParameter(':originalLang', $originalLang);

        $query->setParameter(':date', new \DateTime());
        $objects = $query->getSingleScalarResult();

        return $objects;
    }

    /**
     * @param $lang
     * @param null $originalLang
     * @return OfferPage[]
     */
    public function getOfferPages($lang, $originalLang = null)
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
        if($originalLang) $dql .= ' AND p.originalLanguage = :originalLang ';

        $dql .= ' AND p.expirationDate >= :date ';

        $dql .= ' ORDER BY p.price ASC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        if($originalLang) $query->setParameter(':originalLang', $originalLang);

        $query->setParameter(':date', new \DateTime());
        $objects = $query->getResult();

        return $objects;
    }


    /**
     * @param $lang
     * @param $city
     * @param $trLat
     * @param $trLong
     * @param $blLat
     * @param $blLong
     * @return OfferPage[]
     */
    public function getOfferPagesByCityBounds($lang, $city, $trLat, $trLong, $blLat, $blLong)
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


        $dql .= " AND (p.latitude >= :blLat AND p.latitude <= :trLat) ";
        $dql .= " AND (p.longitude >= :blLong AND p.longitude <= :trLong) ";

        $dql .= " AND p.city = :city ";

        if ($lang) $dql .= " AND nt.lang = :lang ";

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
     * @param $city
     * @param $trLat
     * @param $trLong
     * @param $blLat
     * @param $blLong
     * @return OfferPage[]
     */
    public function getOfferPagesHotelByCityBounds($lang, $city, $trLat, $trLong, $blLat, $blLong)
    {
        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\OfferPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";
        //todo add connection to package category

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\OfferPage\'
AND nt.online = 1';


        $dql .= " AND (p.latitude >= :blLat AND p.latitude <= :trLat) ";
        $dql .= " AND (p.longitude >= :blLong AND p.longitude <= :trLong) ";

        $dql .= " AND p.city = :city ";

        if ($lang) $dql .= " AND nt.lang = :lang ";

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

}
