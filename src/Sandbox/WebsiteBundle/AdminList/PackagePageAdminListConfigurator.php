<?php

namespace Sandbox\WebsiteBundle\AdminList;

use Doctrine\ORM\EntityManager;

use Doctrine\ORM\QueryBuilder;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractDoctrineORMAdminListConfigurator;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticlePageAdminListConfigurator;

/**
 * The admin list configurator for PackagePage
 */
class PackagePageAdminListConfigurator extends AbstractArticlePageAdminListConfigurator//AbstractDoctrineORMAdminListConfigurator
{
//    /**
//     * @param EntityManager $em        The entity manager
//     * @param AclHelper     $aclHelper The acl helper
//     */
//    public function __construct(EntityManager $em, AclHelper $aclHelper = null)
//    {
//        parent::__construct($em, $aclHelper);
//    }
//
//    /**
//     * Configure the visible columns
//     */
//    public function buildFields()
//    {
//        $this->addField('titleTranslated', 'Title translated', true);
//        $this->addField('orderNumber', 'Order number', true);
//        $this->addField('packageId', 'Package id', true);
//        $this->addField('numberAdults', 'Number adults', true);
//        $this->addField('numberChildren', 'Number children', true);
//        $this->addField('duration', 'Duration', true);
//        $this->addField('description', 'Description', true);
//        $this->addField('checkin', 'Checkin', true);
//        $this->addField('checkout', 'Checkout', true);
//        $this->addField('minprice', 'Minprice', true);
//        $this->addField('image', 'Image', true);
//        $this->addField('bankPayment', 'Bank payment', true);
//        $this->addField('creditcardPayment', 'Creditcard payment', true);
//        $this->addField('onthespotPayment', 'Onthespot payment', true);
//        $this->addField('date', 'Date', true);
//        $this->addField('summary', 'Summary', true);
//        $this->addField('title', 'Title', true);
//        $this->addField('pageTitle', 'Page title', true);
//    }
//
//    /**
//     * Build filters for admin list
//     */
//    public function buildFilters()
//    {
//        $this->addFilter('titleTranslated', new ORM\StringFilterType('titleTranslated'), 'Title translated');
//        $this->addFilter('orderNumber', new ORM\NumberFilterType('orderNumber'), 'Order number');
//        $this->addFilter('packageId', new ORM\NumberFilterType('packageId'), 'Package id');
//        $this->addFilter('numberAdults', new ORM\NumberFilterType('numberAdults'), 'Number adults');
//        $this->addFilter('numberChildren', new ORM\NumberFilterType('numberChildren'), 'Number children');
//        $this->addFilter('duration', new ORM\NumberFilterType('duration'), 'Duration');
//        $this->addFilter('description', new ORM\StringFilterType('description'), 'Description');
//        $this->addFilter('checkin', new ORM\StringFilterType('checkin'), 'Checkin');
//        $this->addFilter('checkout', new ORM\StringFilterType('checkout'), 'Checkout');
//        $this->addFilter('minprice', new ORM\NumberFilterType('minprice'), 'Minprice');
//        $this->addFilter('image', new ORM\StringFilterType('image'), 'Image');
//        $this->addFilter('bankPayment', new ORM\BooleanFilterType('bankPayment'), 'Bank payment');
//        $this->addFilter('creditcardPayment', new ORM\BooleanFilterType('creditcardPayment'), 'Creditcard payment');
//        $this->addFilter('onthespotPayment', new ORM\BooleanFilterType('onthespotPayment'), 'Onthespot payment');
//        $this->addFilter('date', new ORM\DateFilterType('date'), 'Date');
//        $this->addFilter('summary', new ORM\StringFilterType('summary'), 'Summary');
//        $this->addFilter('title', new ORM\StringFilterType('title'), 'Title');
//        $this->addFilter('pageTitle', new ORM\StringFilterType('pageTitle'), 'Page title');
//    }
    /**
     * @param QueryBuilder $queryBuilder The query builder
     */
    public function adaptQueryBuilder(QueryBuilder $queryBuilder)
    {
        parent::adaptQueryBuilder($queryBuilder);

        $queryBuilder->setParameter('class', 'Sandbox\WebsiteBundle\Entity\Pages\PackagePage');
    }

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
        return 'Pages\PackagePage';
    }

    public function getListTemplate()
    {
        return 'SandboxWebsiteBundle:AdminList/Package:list.html.twig';
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

    /**
     * @return int
     */
    public function getLimit()
    {
        return 20;
    }
}
