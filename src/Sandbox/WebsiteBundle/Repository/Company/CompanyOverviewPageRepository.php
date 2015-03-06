<?php

namespace Sandbox\WebsiteBundle\Repository\Company;

use Doctrine\ORM\Query;
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


    /**
     * Returns an array of all NewsPages
     *
     * @param string $lang
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function getCompanies($lang = null, $offset = null, $limit = null)
    {
        $q = $this->getCompaniesQuery($lang, $offset, $limit);

        return $q->getResult();
    }

    /**
     * Returns the article query
     *
     * @param string $lang
     * @param int    $offset
     * @param int    $limit
     *
     * @return Query
     */
    public function getCompaniesQuery($lang = null, $offset, $limit)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage', 'qp');

        $query = "SELECT";
        $query .= " article.*";
        $query .= " FROM";
        $query .= " sb_company_overviewpages as article";
        $query .= " INNER JOIN";
        $query .= " kuma_node_versions nv ON nv.ref_id = article.id";
        $query .= " INNER JOIN";
        $query .= " kuma_node_translations nt ON nt.public_node_version_id = nv.id and nt.id = nv.node_translation_id";
        $query .= " INNER JOIN";
        $query .= " kuma_nodes n ON n.id = nt.node_id";

        //$query .= " LEFT JOIN companies_news ON article.id = companies_news.newspage_id";
        //$query .= " LEFT JOIN sb_company_overviewpages ON sb_company_overviewpages.id = companies_news.companyoverviewpage_id";

        $query .= " LEFT JOIN kuma_media ON article.logo_id = kuma_media.id";

        //$query .= " LEFT JOIN sb_news_place_overview ON article.id = sb_news_place_overview.newspage_id";
        //$query .= " LEFT JOIN sb_place_overviewpages ON  sb_place_overviewpages.id = sb_news_place_overview.placeoverviewpage_id";

        $query .= " WHERE";
        $query .= " n.deleted = 0";
        $query .= " AND";
        $query .= " n.ref_entity_name = 'Sandbox\\\\WebsiteBundle\\\\Entity\\\\Company\\\\CompanyOverviewPage'";
        $query .= " AND";
        $query .= " nt.online = 1 ";
        $query .= " AND";
        $query .= " n.parent_id <> 1 ";

        if ($lang) {
            $query .= " AND";
            $query .= " nt.lang = ? ";
        }

        $query .= " ORDER BY article.title ASC";
        if($limit){
            $query .= " LIMIT ?";
            if($offset){
                $query .= " OFFSET ?";
            }
        }

        $q = $this->_em->createNativeQuery($query, $rsm);

        if ($lang) {
            $q->setParameter(1, $lang);
            if($limit){
                $q->setParameter(2, $limit);
                if($offset){
                    $q->setParameter(3, $offset);
                }
            }

        } else {
            if($limit){
                $q->setParameter(1, $limit);
                if($offset){
                    $q->setParameter(2, $offset);
                }
            }
        }

        return $q;
    }


}
