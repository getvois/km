<?php

namespace Sandbox\WebsiteBundle\AdminList\News;

use Doctrine\ORM\QueryBuilder;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticlePageAdminListConfigurator;
use Sandbox\WebsiteBundle\Entity\News\NewsOverviewPage;

/**
 * The AdminList configurator for the NewsPage
 */
class NewsPageAdminListConfigurator extends AbstractArticlePageAdminListConfigurator
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
        return 'News\NewsPage';
    }

    /**
     * @param QueryBuilder $queryBuilder The query builder
     */
    public function adaptQueryBuilder(QueryBuilder $queryBuilder)
    {
        parent::adaptQueryBuilder($queryBuilder);

        $queryBuilder->setParameter('class', 'Sandbox\WebsiteBundle\Entity\News\NewsPage');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getOverviewPageRepository()
    {
        return $this->em->getRepository('SandboxWebsiteBundle:News\NewsOverviewPage');
    }

    /**
     * @return string
     */
    public function getListTemplate()
    {
        return 'SandboxWebsiteBundle:AdminList/News/NewsPageAdminList:list.html.twig';
    }

}
