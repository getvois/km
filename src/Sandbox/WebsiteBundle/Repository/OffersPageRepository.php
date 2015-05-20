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
class OffersPageRepository extends EntityRepository
{
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
}
