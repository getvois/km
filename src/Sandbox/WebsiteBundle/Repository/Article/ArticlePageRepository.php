<?php

namespace Sandbox\WebsiteBundle\Repository\Article;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticlePageRepository;
use Sandbox\WebsiteBundle\Entity\Host;

/**
 * Repository class for the ArticlePage
 */
class ArticlePageRepository extends AbstractArticlePageRepository
{
    public function getArticlePagesWithImage($lang, $host, $limit = 10)
    {
        $dql = "SELECT p.title, nt.slug, m.url, p.date
FROM Sandbox\WebsiteBundle\Entity\Article\ArticlePage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node
LEFT JOIN p.image m";

        if($host) {
            $dql .= ' JOIN p.hosts h ';
        }

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Article\ArticlePage\'
AND nt.online = 1';

        /** @var Host $host */
        if($host){
            $dql .= " AND h.name = '". $host->getName() ."'";
        }

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $dql .= ' ORDER BY p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setMaxResults($limit);
        $objects = $query->getArrayResult();

        return $objects;
    }




    public function getRoot($lang)
    {
        $dql = "SELECT n.id, p.title, nt.slug
FROM Sandbox\WebsiteBundle\Entity\Article\ArticleOverviewPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node ";

        $dql .= ' WHERE n.deleted = 0
        AND n.parent = 1
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Article\ArticleOverviewPage\'
AND nt.online = 1';

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setMaxResults(1);
        $objects = $query->getOneOrNullResult(Query::HYDRATE_ARRAY);

        return $objects;
    }

    public function getArticlePages($lang, $host, $limit = 10)
    {
        $dql = "SELECT n.id, p.title, nt.slug
FROM Sandbox\WebsiteBundle\Entity\Article\ArticlePage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node ";

        if($host) {
            $dql .= ' JOIN p.hosts h ';
        }

        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Article\ArticlePage\'
AND nt.online = 1';

        /** @var Host $host */
        if($host){
            $dql .= " AND h.name = '". $host->getName() ."'";
        }

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $dql .= ' ORDER BY p.date DESC ';

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        $query->setMaxResults($limit);
        $objects = $query->getArrayResult();

        return $objects;
    }


    /**
     * Returns an array of all ArticlePages
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

        $query .= " LEFT JOIN kuma_media ON article.image_id = kuma_media.id";

        //$query .= " LEFT JOIN sb_article_place_overview ON article.id = sb_article_place_overview.articlepage_id";
        //$query .= " LEFT JOIN sb_place_overviewpages ON  sb_place_overviewpages.id = sb_article_place_overview.placeoverviewpage_id";

        if($host) {
            $query .= " INNER JOIN sb_host_article ON article.id = sb_host_article.articlepage_id";
            $query .= " INNER JOIN sb_host ON sb_host_article.host_id = sb_host.id";
        }

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
