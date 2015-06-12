<?php

namespace Sandbox\WebsiteBundle\Repository\Place;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticleOverviewPageRepository;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;

/**
 * Repository class for the PlaceOverviewPage
 */
class PlaceOverviewPageRepository extends AbstractArticleOverviewPageRepository
{
    public function getRoot($lang, $host)
    {
        $dql = "SELECT n.id, p.title, nt.slug
FROM Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node ";


        if($host) {
            $dql .= ' JOIN p.hosts h ';
        }

        $dql .= ' WHERE n.deleted = 0
        AND n.parent = 1
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage\'
AND nt.online = 1';

        /** @var Host $host */
        if($host){
            $dql .= " AND h.name = '". $host->getName() ."'";
        }

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setMaxResults(1);
        $objects = $query->getOneOrNullResult(Query::HYDRATE_ARRAY);

        return $objects;
    }


    public function getByParentIds($parentIds, $lang, $host)
    {
        $dql = "SELECT IDENTITY(n.parent) as parent, n.id, p.title, nt.slug, p.cityId
FROM Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node ";

        if($host) {
            $dql .= ' JOIN p.hosts h ';
        }

        $dql .= ' WHERE n.deleted = 0
        AND n.parent IN (:parentids)
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage\'
AND nt.online = 1';

        /** @var Host $host */
        if($host){
            $dql .= " AND h.name = '". $host->getName() ."'";
        }

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $dql .= ' ORDER BY p.title ASC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        $query->setParameter(':parentids', $parentIds);

        $objects = $query->getArrayResult();

        return $objects;
    }

    public function getByRoot($rootNodeId, $lang, $host)
    {
        $dql = "SELECT n.id, p.title, nt.slug, p.cityId
FROM Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node ";

        if($host) {
            $dql .= ' JOIN p.hosts h ';
        }

        $dql .= ' WHERE n.deleted = 0
        AND n.parent = :root
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage\'
AND nt.online = 1';

        /** @var Host $host */
        if($host){
            $dql .= " AND h.name = '". $host->getName() ."'";
        }

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $dql .= ' ORDER BY p.title ASC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        $query->setParameter(':root', $rootNodeId);

        $objects = $query->getArrayResult();

        return $objects;
    }


    /**
     * @param $lang
     * @param null $host
     * @param null $rootId
     * @return array
     */
    public function getActiveOverviewPages($lang, $host = null, $rootId = null)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata($this->getEntityName(), 'overviewpage');
        $query = "
            SELECT
                overviewpage.*
            FROM ";
        $query .= $this->_em->getClassMetadata($this->getEntityName())->getTableName();
        $query .= " AS overviewpage
            INNER JOIN
                kuma_node_versions nv ON nv.ref_id = overviewpage.id
            INNER JOIN
                kuma_node_translations nt ON nt.public_node_version_id = nv.id AND nt.id = nv.node_translation_id
            INNER JOIN
                kuma_nodes n ON n.id = nt.node_id";

        if($host) {
            $query .= " INNER JOIN sb_host_place ON overviewpage.id = sb_host_place.placeoverviewpage_id";
            $query .= " INNER JOIN sb_host ON sb_host_place.host_id = sb_host.id";
        }

        $query .= " WHERE
                n.deleted = 0
            AND
                n.ref_entity_name = :entity_name
        ";
        $query .= " AND";
        $query .= " nt.online = 1 ";

        if ($lang) {
            $query .= " AND";
            $query .= " nt.lang = '$lang' ";
        }

        /** @var Host $host */
        if($host){
            $query .= " AND sb_host.name = '". $host->getName() ."'";
        }

        if($rootId){
            $query .= " AND n.parent_id = " . $rootId;
        }

        $query .= ' ORDER BY overviewpage.title';

        $q = $this->_em->createNativeQuery($query, $rsm);
        $q->setParameter('entity_name',$this->getEntityName());

        return $q->getResult();
    }

    public function getByLang($lang)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata($this->getEntityName(), 'overviewpage');
        $query = "
            SELECT
                overviewpage.*
            FROM ";
        $query .= $this->_em->getClassMetadata($this->getEntityName())->getTableName();
        $query .= " AS overviewpage
            INNER JOIN
                kuma_node_versions nv ON nv.ref_id = overviewpage.id
            INNER JOIN
                kuma_node_translations nt ON nt.public_node_version_id = nv.id AND nt.id = nv.node_translation_id
            INNER JOIN
                kuma_nodes n ON n.id = nt.node_id
            WHERE
                n.deleted = 0
            AND
                nt.lang = :lang
            AND
                n.ref_entity_name = :entity_name
        ";
        $q = $this->_em->createNativeQuery($query, $rsm);
        $q->setParameter('entity_name',$this->getEntityName());
        $q->setParameter('lang',$lang);

        $res =  $q->getResult();

        $ids = [];
        foreach ($res as $page) {
            $ids[] = $page->getId();
        }

        return $this->getEntityManager()->createQueryBuilder()
            ->select('n')
            ->from('SandboxWebsiteBundle:Place\PlaceOverviewPage', 'n')
            ->where('n.id IN(:ids)')
            ->setParameter(":ids", $ids);
    }

    /**
     * @param $title
     * @return null|PlaceOverviewPage
     */
    public function getByTitle($title)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata($this->getEntityName(), 'overviewpage');
        $query = "
            SELECT
                overviewpage.*
            FROM ";
        $query .= $this->_em->getClassMetadata($this->getEntityName())->getTableName();
        $query .= " AS overviewpage
            INNER JOIN
                kuma_node_versions nv ON nv.ref_id = overviewpage.id
            INNER JOIN
                kuma_node_translations nt ON nt.public_node_version_id = nv.id AND nt.id = nv.node_translation_id
            INNER JOIN
                kuma_nodes n ON n.id = nt.node_id
            WHERE
                n.deleted = 0
            AND
                overviewpage.title = :title
            AND
                n.ref_entity_name = :entity_name
        ";
        $q = $this->_em->createNativeQuery($query, $rsm);
        $q->setParameter('entity_name',$this->getEntityName());
        $q->setParameter('title',$title);

        $res =  $q->getResult();

        if($res) return $res[0];

        return null;
    }
}
