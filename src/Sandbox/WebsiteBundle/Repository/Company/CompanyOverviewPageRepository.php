<?php

namespace Sandbox\WebsiteBundle\Repository\Company;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticleOverviewPageRepository;

/**
 * Repository class for the CompanyOverviewPage
 */
class CompanyOverviewPageRepository extends AbstractArticleOverviewPageRepository
{

    public function getActive()
    {
        $res = $this->findActiveOverviewPages();

        $ids = [];
        foreach ($res as $page) {
            $ids[] = $page->getId();
        }

        return $this->getEntityManager()->createQueryBuilder()
            ->select('n')
            ->from('SandboxWebsiteBundle:Company\CompanyOverviewPage', 'n')
            ->where('n.id IN(:ids)')
            ->setParameter(":ids", $ids);

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
          ->from('SandboxWebsiteBundle:Company\CompanyOverviewPage', 'n')
          ->where('n.id IN(:ids)')
          ->setParameter(":ids", $ids);
    }

}
