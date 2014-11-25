<?php

namespace Sandbox\WebsiteBundle\AdminList\Company;

use Doctrine\ORM\QueryBuilder;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticlePageAdminListConfigurator;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;

/**
 * The AdminList configurator for the CompanyPage
 */
class CompanyPageAdminListConfigurator extends AbstractArticlePageAdminListConfigurator
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
        return 'Company\CompanyPage';
    }

    /**
     * @param QueryBuilder $queryBuilder The query builder
     */
    public function adaptQueryBuilder(QueryBuilder $queryBuilder)
    {
        parent::adaptQueryBuilder($queryBuilder);

        $queryBuilder->setParameter('class', 'Sandbox\WebsiteBundle\Entity\Company\CompanyPage');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getOverviewPageRepository()
    {
        return $this->em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage');
    }

    /**
     * @return string
     */
    public function getListTemplate()
    {
        return 'SandboxWebsiteBundle:AdminList/Company/CompanyPageAdminList:list.html.twig';
    }

}
