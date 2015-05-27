<?php

namespace Sandbox\WebsiteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * The type for OfferPage
 */
class OfferPageAdminType extends AbstractType
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
        $builder->add('viewCount');
        $builder->add('originalLanguage');
        $builder->add('titleTranslated');
        $builder->add('summary');
        $builder->add('offerId');
        $builder->add('longTitle');
        $builder->add('image');
        $builder->add('wideImage');
        $builder->add('price');
        $builder->add('priceNormal');
        $builder->add('priceEur');
        $builder->add('priceNormalEur');
        $builder->add('currency');
        $builder->add('days');
        $builder->add('description');
        $builder->add('longDescription');
        $builder->add('shortDescription');
        $builder->add('shortDescriptionTranslated');
        $builder->add('logo');
        $builder->add('absoluteUrl');
        $builder->add('country');
        $builder->add('city');
        $builder->add('region');
        $builder->add('transportation');
        $builder->add('targetGroup');
        $builder->add('accomodation');
        $builder->add('accomodationType');
        $builder->add('expirationDate');
        $builder->add('offerSold');
        $builder->add('adress');
        $builder->add('included');
        $builder->add('latitude');
        $builder->add('longitude');
        $builder->add('nights');
        $builder->add('priceType');
        $builder->add('pricePer');
        $builder->add('discount');
        $builder->add('maxPersons');
        $builder->add('minPersons');
        $builder->add('soldOut');
        $builder->add('bookingFee');
        $builder->add('extra');
        $builder->add('title');
        $builder->add('pageTitle');
        $builder->add('categories');
        $builder->add('places');
        $builder->add('company');
        $builder->add('countryPlace');
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'offerpage_form';
    }
}
