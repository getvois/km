<?php

namespace Sandbox\WebsiteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * The type for Ad
 */
class AdAdminType extends AbstractType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('campaign');
        $builder->add('lang');
        $builder->add('pagetypes', 'choice', ['choices' => [
            'Sandbox\WebsiteBundle\Entity\Article\ArticleOverviewPage' => 'ArticleOverviewPage',
            'Sandbox\WebsiteBundle\Entity\Article\ArticlePage' => 'ArticlePage',
            'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage' => 'CompanyOverviewPage',
            'Sandbox\WebsiteBundle\Entity\Company\CompanyPage' => 'CompanyPage',
            'Sandbox\WebsiteBundle\Entity\News\NewsOverviewPage' => 'NewsOverviewPage',
            'Sandbox\WebsiteBundle\Entity\News\NewsPage' => 'NewsPage',
            'Sandbox\WebsiteBundle\Entity\Pages\BookingcomPage' => 'BookingcomPage',
            'Sandbox\WebsiteBundle\Entity\Pages\CompanyPlacePage' => 'CompanyPlacePage',
            'Sandbox\WebsiteBundle\Entity\Pages\CompanyTypePage' => 'CompanyTypePage',
            'Sandbox\WebsiteBundle\Entity\Pages\ContactPage' => 'ContactPage',
            'Sandbox\WebsiteBundle\Entity\Pages\ContentPage' => 'ContentPage',
            'Sandbox\WebsiteBundle\Entity\Pages\FormPage' => 'FormPage',
            'Sandbox\WebsiteBundle\Entity\Pages\HomePage' => 'HomePage',
            'Sandbox\WebsiteBundle\Entity\Pages\HotelOverviewPage' => 'HotelOverviewPage',
            'Sandbox\WebsiteBundle\Entity\Pages\HotelPage' => 'HotelPage',
            'Sandbox\WebsiteBundle\Entity\Pages\OfferPage' => 'OfferPage',
            'Sandbox\WebsiteBundle\Entity\Pages\OffersOverviewPage' => 'OffersOverviewPage',
            'Sandbox\WebsiteBundle\Entity\Pages\PackageOverviewPage' => 'PackageOverviewPage',
            'Sandbox\WebsiteBundle\Entity\Pages\PackagePage' => 'PackagePage',
            'Sandbox\WebsiteBundle\Entity\Pages\SatelliteOverviewPage' => 'SatelliteOverviewPage',
            'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage' => 'PlaceOverviewPage',
        ],
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'js-advanced-select')
        ]);
        $builder->add('position', 'choice', ['choices' => [
            'top' => 'top',
            'middle' => 'middle',
            'bottom' => 'bottom',
            'sidebar' => 'sidebar',
            'gallery' => 'gallery',
        ],
            'required' => false,
            'attr' => array('class' => 'js-advanced-select')
        ]);
        $builder->add('html');
        $builder->add('weight');
        $builder->add('hosts');
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'ad_form';
    }
}
