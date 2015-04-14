<?php

namespace Sandbox\WebsiteBundle\AdminList;

use Doctrine\ORM\EntityManager;

use Doctrine\ORM\QueryBuilder;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticlePageAdminListConfigurator;
use Sandbox\WebsiteBundle\Form\HotelPageAdminType;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractDoctrineORMAdminListConfigurator;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;

/**
 * The admin list configurator for HotelPage
 */
class HotelPageAdminListConfigurator extends AbstractArticlePageAdminListConfigurator//AbstractDoctrineORMAdminListConfigurator
{
//    /**
//     * @param EntityManager $em        The entity manager
//     * @param AclHelper     $aclHelper The acl helper
//     */
//    public function __construct(EntityManager $em, AclHelper $aclHelper = null)
//    {
//        parent::__construct($em, $aclHelper);
//        $this->setAdminType(new HotelPageAdminType());
//    }

    /**
     * @param QueryBuilder $queryBuilder The query builder
     */
    public function adaptQueryBuilder(QueryBuilder $queryBuilder)
    {
        parent::adaptQueryBuilder($queryBuilder);

        $queryBuilder->setParameter('class', 'Sandbox\WebsiteBundle\Entity\Pages\HotelPage');
    }

//    /**
//     * Configure the visible columns
//     */
//    public function buildFields()
//    {
//        $this->addField('street', 'Street', true);
//        $this->addField('hotelId', 'Hotel id', true);
//        $this->addField('city', 'City', true);
//        $this->addField('cityParish', 'City parish', true);
//        $this->addField('country', 'Country', true);
//        $this->addField('latitude', 'Latitude', true);
//        $this->addField('longitude', 'Longitude', true);
//        $this->addField('shortDescription', 'Short description', true);
//        $this->addField('longDescription', 'Long description', true);
//        $this->addField('title', 'Title', true);
//        $this->addField('pageTitle', 'Page title', true);
//    }

//    /**
//     * Build filters for admin list
//     */
//    public function buildFilters()
//    {
//        $this->addFilter('street', new ORM\StringFilterType('street'), 'Street');
//        $this->addFilter('hotelId', new ORM\NumberFilterType('hotelId'), 'Hotel id');
//        $this->addFilter('city', new ORM\StringFilterType('city'), 'City');
//        $this->addFilter('cityParish', new ORM\StringFilterType('cityParish'), 'City parish');
//        $this->addFilter('country', new ORM\StringFilterType('country'), 'Country');
//        $this->addFilter('latitude', new ORM\StringFilterType('latitude'), 'Latitude');
//        $this->addFilter('longitude', new ORM\StringFilterType('longitude'), 'Longitude');
//        $this->addFilter('shortDescription', new ORM\StringFilterType('shortDescription'), 'Short description');
//        $this->addFilter('longDescription', new ORM\StringFilterType('longDescription'), 'Long description');
//        $this->addFilter('title', new ORM\StringFilterType('title'), 'Title');
//        $this->addFilter('pageTitle', new ORM\StringFilterType('pageTitle'), 'Page title');
//    }

    /**
     * Get bundle name
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'SandboxWebsiteBundle';
    }

    /**
     * Get entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        return 'Pages\HotelPage';
    }

    public function getListTemplate()
    {
        return 'SandboxWebsiteBundle:AdminList/Hotel:list.html.twig';
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getOverviewPageRepository()
    {
        return null;
    }

    public function getOverviewPage()
    {
        return null;
    }


}
