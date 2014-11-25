<?php

namespace Sandbox\WebsiteBundle\Repository\Article;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticlePageRepository;

/**
 * Repository class for the ArticlePage
 */
class ArticlePageRepository extends AbstractArticlePageRepository
{

    /**
     * Returns an array of all ArticlePages
     *
     * @param string $lang
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function getArticles($lang = null, $offset = null, $limit = null)
    {
        $q = $this->getArticlesQuery($lang, $offset, $limit);

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
    public function getArticlesQuery($lang = null, $offset, $limit)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('Sandbox\WebsiteBundle\Entity\Article\ArticlePage', 'qp');

        $query = "SELECT";
        $query .= " article.*";
        $query .= " FROM";
        $query .= " sb_article_pages as article";
        $query .= " INNER JOIN";
        $query .= " kuma_node_versions nv ON nv.ref_id = article.id";
        $query .= " INNER JOIN";
        $query .= " kuma_node_translations nt ON nt.public_node_version_id = nv.id and nt.id = nv.node_translation_id";
        $query .= " INNER JOIN";
        $query .= " kuma_nodes n ON n.id = nt.node_id";
        $query .= " WHERE";
        $query .= " n.deleted = 0";
        $query .= " AND";
        $query .= " n.ref_entity_name = 'Sandbox\\\\WebsiteBundle\\\\Entity\\\\Article\\\\ArticlePage'";
        $query .= " AND";
        $query .= " nt.online = 1 ";
        if ($lang) {
            $query .= " AND";
            $query .= " nt.lang = ? ";
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
