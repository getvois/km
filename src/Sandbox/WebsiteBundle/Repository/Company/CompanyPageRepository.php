<?php

namespace Sandbox\WebsiteBundle\Repository\Company;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticlePageRepository;

/**
 * Repository class for the CompanyPage
 */
class CompanyPageRepository extends AbstractArticlePageRepository
{

    public function getRoot($internalName, $lang)
    {
        $dql = "SELECT n.id, p.title, nt.slug
FROM Sandbox\WebsiteBundle\Entity\Pages\ContentPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node ";

        $dql .= ' WHERE n.deleted = 0
        AND n.internalName = :name
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\ContentPage\'
AND nt.online = 1';

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        $query->setParameter(':name', $internalName);

        $objects = $query->getOneOrNullResult();

        return $objects;
    }


    public function getByRoot($rootNodeId, $lang)
    {
        $dql = "SELECT n.id, p.title, nt.slug, m.url
FROM Sandbox\WebsiteBundle\Entity\Pages\ContentPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node
LEFT JOIN p.picture m";

        $dql .= ' WHERE n.deleted = 0
        AND n.parent = :parent
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\ContentPage\'
AND nt.online = 1';

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $dql .= " ORDER BY p.title ";

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        $query->setParameter(':parent', $rootNodeId);

        $objects = $query->getResult();

        return $objects;
    }

    public function getByParentIds($parentIds, $lang)
    {
        $dql = "SELECT IDENTITY(n.parent) as parent, n.id,  p.title, nt.slug
FROM Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";

        $dql .= ' WHERE n.deleted = 0
        AND n.parent IN (:parentids)
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage\'
AND nt.online = 1';

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $dql .= " ORDER BY p.title ";

        $query = $this->_em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);
        $query->setParameter(':parentids', $parentIds);

        $objects = $query->getResult();

        return $objects;
    }


    /**
     * Returns an array of all CompanyPages
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
        $rsm->addRootEntityFromClassMetadata('Sandbox\WebsiteBundle\Entity\Company\CompanyPage', 'qp');

        $query = "SELECT";
        $query .= " article.*";
        $query .= " FROM";
        $query .= " sb_company_pages as article";
        $query .= " INNER JOIN";
        $query .= " kuma_node_versions nv ON nv.ref_id = article.id";
        $query .= " INNER JOIN";
        $query .= " kuma_node_translations nt ON nt.public_node_version_id = nv.id and nt.id = nv.node_translation_id";
        $query .= " INNER JOIN";
        $query .= " kuma_nodes n ON n.id = nt.node_id";
        $query .= " WHERE";
        $query .= " n.deleted = 0";
        $query .= " AND";
        $query .= " n.ref_entity_name = 'Sandbox\\\\WebsiteBundle\\\\Entity\\\\Company\\\\CompanyPage'";
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
