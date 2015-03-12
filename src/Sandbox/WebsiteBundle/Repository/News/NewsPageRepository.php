<?php

namespace Sandbox\WebsiteBundle\Repository\News;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticlePageRepository;
use Sandbox\WebsiteBundle\Entity\Host;

/**
 * Repository class for the NewsPage
 */
class NewsPageRepository extends AbstractArticlePageRepository
{

    /**
     * Returns an array of all NewsPages
     *
     * @param string $lang
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function getArticles($lang = null, $offset = null, $limit = null, $host = null)
    {
        $q = $this->getArticlesQuery($lang, $offset, $limit, $host);

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
    public function getArticlesQuery($lang = null, $offset, $limit, $host = null)
    {
//        $dql = "SELECT p, nv, n, nt
//FROM Sandbox\WebsiteBundle\Entity\News\NewsPage p
//INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
//INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
//INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node
//WHERE n.deleted = 0
//AND n.refEntityName = 'Sandbox\\WebsiteBundle\\Entity\\News\\NewsPage'
//AND nt.online = 1
//ORDER BY p.date DESC";

        //var_dump($this->_em->createQuery($dql)->getResult());


        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('Sandbox\WebsiteBundle\Entity\News\NewsPage', 'qp');

        $query = "SELECT";
        $query .= " article.*";
        $query .= " FROM";
        $query .= " sb_news_pages as article";
        $query .= " INNER JOIN";
        $query .= " kuma_node_versions nv ON nv.ref_id = article.id";
        $query .= " INNER JOIN";
        $query .= " kuma_node_translations nt ON nt.public_node_version_id = nv.id and nt.id = nv.node_translation_id";
        $query .= " INNER JOIN";
        $query .= " kuma_nodes n ON n.id = nt.node_id";

        //$query .= " LEFT JOIN companies_news ON article.id = companies_news.newspage_id";
        //$query .= " LEFT JOIN sb_company_overviewpages ON sb_company_overviewpages.id = companies_news.companyoverviewpage_id";

        $query .= " LEFT JOIN kuma_media ON article.image_id = kuma_media.id";

        //$query .= " LEFT JOIN sb_news_place_overview ON article.id = sb_news_place_overview.newspage_id";
        //$query .= " LEFT JOIN sb_place_overviewpages ON  sb_place_overviewpages.id = sb_news_place_overview.placeoverviewpage_id";

        if($host) {
            $query .= " INNER JOIN sb_host_news ON article.id = sb_host_news.newspage_id";
            $query .= " INNER JOIN sb_host ON sb_host_news.host_id = sb_host.id";
        }

        $query .= " WHERE";
        $query .= " n.deleted = 0";
        $query .= " AND";
        $query .= " n.ref_entity_name = 'Sandbox\\\\WebsiteBundle\\\\Entity\\\\News\\\\NewsPage'";
        $query .= " AND";
        $query .= " nt.online = 1 ";
        if ($lang) {
            $query .= " AND";
            $query .= " nt.lang = ? ";
        }

        /** @var Host $host */
        if($host){
            $query .= " AND sb_host.name = '". $host->getName() ."'";
        }

        $query .= " ORDER BY article.date DESC";
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
