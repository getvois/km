<?php

namespace Sandbox\WebsiteBundle\AdminList\Article;

use Doctrine\ORM\QueryBuilder;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticlePageAdminListConfigurator;
use Sandbox\WebsiteBundle\Entity\Article\ArticleOverviewPage;

/**
 * The AdminList configurator for the ArticlePage
 */
class ArticlePageAdminListConfigurator extends AbstractArticlePageAdminListConfigurator
{

    /**
     * Return current bundle name.
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'SandboxWebsiteBundle';
    }

    /**
     * Return current entity name.
     *
     * @return string
     */
    public function getEntityName()
    {
        return 'Article\ArticlePage';
    }

    /**
     * @param QueryBuilder $queryBuilder The query builder
     */
    public function adaptQueryBuilder(QueryBuilder $queryBuilder)
    {
        parent::adaptQueryBuilder($queryBuilder);

        $queryBuilder->setParameter('class', 'Sandbox\WebsiteBundle\Entity\Article\ArticlePage');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getOverviewPageRepository()
    {
        return $this->em->getRepository('SandboxWebsiteBundle:Article\ArticleOverviewPage');
    }

    /**
     * @return string
     */
    public function getListTemplate()
    {
        return 'SandboxWebsiteBundle:AdminList/Article/ArticlePageAdminList:list.html.twig';
    }

    public function getLimit()
    {
        return 30;
    }


}
