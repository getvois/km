<?php

namespace Sandbox\WebsiteBundle\AdminList\Place;

use Doctrine\ORM\QueryBuilder;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticlePageAdminListConfigurator;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;

/**
 * The AdminList configurator for the PlacePage
 */
class PlacePageAdminListConfigurator extends AbstractArticlePageAdminListConfigurator
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
        return 'Place\PlacePage';
    }

    /**
     * @param QueryBuilder $queryBuilder The query builder
     */
    public function adaptQueryBuilder(QueryBuilder $queryBuilder)
    {
        parent::adaptQueryBuilder($queryBuilder);

        $queryBuilder->setParameter('class', 'Sandbox\WebsiteBundle\Entity\Place\PlacePage');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getOverviewPageRepository()
    {
        return $this->em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage');
    }

    /**
     * @return string
     */
    public function getListTemplate()
    {
        return 'SandboxWebsiteBundle:AdminList/Place/PlacePageAdminList:list.html.twig';
    }

}
