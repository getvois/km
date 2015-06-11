<?php

namespace Sandbox\WebsiteBundle\AdminList;

use Doctrine\ORM\EntityManager;

use Doctrine\ORM\QueryBuilder;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticlePageAdminListConfigurator;
use Sandbox\WebsiteBundle\Form\OfferPageAdminType;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractDoctrineORMAdminListConfigurator;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;

/**
 * The admin list configurator for OfferPage
 */
class OfferPageAdminListConfigurator extends AbstractArticlePageAdminListConfigurator//AbstractDoctrineORMAdminListConfigurator
{
//    /**
//     * @param EntityManager $em        The entity manager
//     * @param AclHelper     $aclHelper The acl helper
//     */
//    public function __construct(EntityManager $em, AclHelper $aclHelper = null)
//    {
//        parent::__construct($em, $aclHelper);
//        $this->setAdminType(new OfferPageAdminType());
//    }
//
//    /**
//     * Configure the visible columns
//     */
//    public function buildFields()
//    {
//        $this->addField('viewCount', 'View count', true);
//        $this->addField('originalLanguage', 'Original language', true);
//        $this->addField('titleTranslated', 'Title translated', true);
//        $this->addField('summary', 'Summary', true);
//        $this->addField('offerId', 'Offer id', true);
//        $this->addField('longTitle', 'Long title', true);
//        $this->addField('image', 'Image', true);
//        $this->addField('wideImage', 'Wide image', true);
//        $this->addField('price', 'Price', true);
//        $this->addField('priceNormal', 'Price normal', true);
//        $this->addField('priceEur', 'Price eur', true);
//        $this->addField('priceNormalEur', 'Price normal eur', true);
//        $this->addField('currency', 'Currency', true);
//        $this->addField('days', 'Days', true);
//        $this->addField('description', 'Description', true);
//        $this->addField('longDescription', 'Long description', true);
//        $this->addField('shortDescription', 'Short description', true);
//        $this->addField('shortDescriptionTranslated', 'Short description translated', true);
//        $this->addField('logo', 'Logo', true);
//        $this->addField('absoluteUrl', 'Absolute url', true);
//        $this->addField('country', 'Country', true);
//        $this->addField('city', 'City', true);
//        $this->addField('region', 'Region', true);
//        $this->addField('transportation', 'Transportation', true);
//        $this->addField('targetGroup', 'Target group', true);
//        $this->addField('accomodation', 'Accomodation', true);
//        $this->addField('accomodationType', 'Accomodation type', true);
//        $this->addField('expirationDate', 'Expiration date', true);
//        $this->addField('offerSold', 'Offer sold', true);
//        $this->addField('adress', 'Adress', true);
//        $this->addField('included', 'Included', true);
//        $this->addField('latitude', 'Latitude', true);
//        $this->addField('longitude', 'Longitude', true);
//        $this->addField('nights', 'Nights', true);
//        $this->addField('priceType', 'Price type', true);
//        $this->addField('pricePer', 'Price per', true);
//        $this->addField('discount', 'Discount', true);
//        $this->addField('maxPersons', 'Max persons', true);
//        $this->addField('minPersons', 'Min persons', true);
//        $this->addField('soldOut', 'Sold out', true);
//        $this->addField('bookingFee', 'Booking fee', true);
//        $this->addField('extra', 'Extra', true);
//        $this->addField('title', 'Title', true);
//        $this->addField('pageTitle', 'Page title', true);
//    }
//
//    /**
//     * Build filters for admin list
//     */
//    public function buildFilters()
//    {
//        $this->addFilter('viewCount', new ORM\NumberFilterType('viewCount'), 'View count');
//        $this->addFilter('originalLanguage', new ORM\StringFilterType('originalLanguage'), 'Original language');
//        $this->addFilter('titleTranslated', new ORM\StringFilterType('titleTranslated'), 'Title translated');
//        $this->addFilter('summary', new ORM\StringFilterType('summary'), 'Summary');
//        $this->addFilter('offerId', new ORM\NumberFilterType('offerId'), 'Offer id');
//        $this->addFilter('longTitle', new ORM\StringFilterType('longTitle'), 'Long title');
//        $this->addFilter('image', new ORM\StringFilterType('image'), 'Image');
//        $this->addFilter('wideImage', new ORM\StringFilterType('wideImage'), 'Wide image');
//        $this->addFilter('price', new ORM\NumberFilterType('price'), 'Price');
//        $this->addFilter('priceNormal', new ORM\NumberFilterType('priceNormal'), 'Price normal');
//        $this->addFilter('priceEur', new ORM\NumberFilterType('priceEur'), 'Price eur');
//        $this->addFilter('priceNormalEur', new ORM\NumberFilterType('priceNormalEur'), 'Price normal eur');
//        $this->addFilter('currency', new ORM\StringFilterType('currency'), 'Currency');
//        $this->addFilter('days', new ORM\StringFilterType('days'), 'Days');
//        $this->addFilter('description', new ORM\StringFilterType('description'), 'Description');
//        $this->addFilter('longDescription', new ORM\StringFilterType('longDescription'), 'Long description');
//        $this->addFilter('shortDescription', new ORM\StringFilterType('shortDescription'), 'Short description');
//        $this->addFilter('shortDescriptionTranslated', new ORM\StringFilterType('shortDescriptionTranslated'), 'Short description translated');
//        $this->addFilter('logo', new ORM\StringFilterType('logo'), 'Logo');
//        $this->addFilter('absoluteUrl', new ORM\StringFilterType('absoluteUrl'), 'Absolute url');
//        $this->addFilter('country', new ORM\StringFilterType('country'), 'Country');
//        $this->addFilter('city', new ORM\StringFilterType('city'), 'City');
//        $this->addFilter('region', new ORM\StringFilterType('region'), 'Region');
//        $this->addFilter('transportation', new ORM\StringFilterType('transportation'), 'Transportation');
//        $this->addFilter('targetGroup', new ORM\StringFilterType('targetGroup'), 'Target group');
//        $this->addFilter('accomodation', new ORM\StringFilterType('accomodation'), 'Accomodation');
//        $this->addFilter('accomodationType', new ORM\StringFilterType('accomodationType'), 'Accomodation type');
//        $this->addFilter('expirationDate', new ORM\DateFilterType('expirationDate'), 'Expiration date');
//        $this->addFilter('offerSold', new ORM\NumberFilterType('offerSold'), 'Offer sold');
//        $this->addFilter('adress', new ORM\StringFilterType('adress'), 'Adress');
//        $this->addFilter('included', new ORM\StringFilterType('included'), 'Included');
//        $this->addFilter('latitude', new ORM\StringFilterType('latitude'), 'Latitude');
//        $this->addFilter('longitude', new ORM\StringFilterType('longitude'), 'Longitude');
//        $this->addFilter('nights', new ORM\StringFilterType('nights'), 'Nights');
//        $this->addFilter('priceType', new ORM\StringFilterType('priceType'), 'Price type');
//        $this->addFilter('pricePer', new ORM\StringFilterType('pricePer'), 'Price per');
//        $this->addFilter('discount', new ORM\StringFilterType('discount'), 'Discount');
//        $this->addFilter('maxPersons', new ORM\NumberFilterType('maxPersons'), 'Max persons');
//        $this->addFilter('minPersons', new ORM\NumberFilterType('minPersons'), 'Min persons');
//        $this->addFilter('soldOut', new ORM\BooleanFilterType('soldOut'), 'Sold out');
//        $this->addFilter('bookingFee', new ORM\StringFilterType('bookingFee'), 'Booking fee');
//        $this->addFilter('extra', new ORM\StringFilterType('extra'), 'Extra');
//        $this->addFilter('title', new ORM\StringFilterType('title'), 'Title');
//        $this->addFilter('pageTitle', new ORM\StringFilterType('pageTitle'), 'Page title');
//    }
    public function adaptQueryBuilder(QueryBuilder $queryBuilder)
    {
        parent::adaptQueryBuilder($queryBuilder);

        $queryBuilder->setParameter('class', 'Sandbox\WebsiteBundle\Entity\Pages\OfferPage');
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
        return 'Pages\OfferPage';
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

    public function getListTemplate()
    {
        return 'SandboxWebsiteBundle:AdminList/Offer:list.html.twig';
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return 20;
    }
}
