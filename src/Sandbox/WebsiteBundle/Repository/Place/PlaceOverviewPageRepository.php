<?php

namespace Sandbox\WebsiteBundle\Repository\Place;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticleOverviewPageRepository;
use Sandbox\WebsiteBundle\Entity\Host;

/**
 * Repository class for the PlaceOverviewPage
 */
class PlaceOverviewPageRepository extends AbstractArticleOverviewPageRepository
{

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
}
