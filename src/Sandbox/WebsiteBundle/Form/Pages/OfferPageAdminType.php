<?php

namespace Sandbox\WebsiteBundle\Form\Pages;

use Kunstmaan\NodeBundle\Form\PageAdminType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * OfferPageAdminType
 */
class OfferPageAdminType extends PageAdminType
{

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('titleTranslated');
        $builder->add('summary');

        $builder->add('offerId', 'integer', array(
            'required' => false,
        ));
        $builder->add('longTitle', 'text', array(
            'required' => false,
        ));
        $builder->add('image', 'text', array(
            'required' => false,
        ));
        $builder->add('wideImage', 'text', array(
            'required' => false,
        ));
        $builder->add('price', 'number', array(
            'required' => false,
        ));
        $builder->add('priceNormal', 'number', array(
            'required' => false,
        ));
        $builder->add('priceEur', 'number', array(
            'required' => false,
        ));
        $builder->add('priceNormalEur', 'number', array(
            'required' => false,
        ));
        $builder->add('currency', 'text', array(
            'required' => false,
        ));
        $builder->add('days', 'text', array(
            'required' => false,
        ));
        $builder->add('description', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('longDescription', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('shortDescription', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('shortDescriptionTranslated', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('logo', 'text', array(
            'required' => false,
        ));
        $builder->add('absoluteUrl', 'text', array(
            'required' => false,
        ));
        $builder->add('categories', 'entity', array(
            'class' => 'Sandbox\WebsiteBundle\Entity\PackageCategory',
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            'attr' => array('class' => 'chzn-select'),
            'property' => 'name',

        ));
        $builder->add('country', 'text', array(
            'required' => false,
        ));
        $builder->add('city', 'text', array(
            'required' => false,
        ));
        $builder->add('region', 'text', array(
            'required' => false,
        ));
        $builder->add('transportation', 'text', array(
            'required' => false,
        ));
        $builder->add('targetGroup', 'text', array(
            'required' => false,
        ));
        $builder->add('accomodation', 'text', array(
            'required' => false,
        ));
        $builder->add('accomodationType', 'text', array(
            'required' => false,
        ));
        $builder->add('expirationDate', 'datetime', array(
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
            'date_format' => 'dd/MM/yyyy',
            'required' => false,
        ));
        $builder->add('offerSold', 'integer', array(
            'required' => false,
        ));
        $builder->add('adress', 'text', array(
            'required' => false,
        ));
        $builder->add('included', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('latitude', 'text', array(
            'required' => false,
        ));
        $builder->add('longitude', 'text', array(
            'required' => false,
        ));
        $builder->add('nights', 'text', array(
            'required' => false,
        ));
        $builder->add('priceType', 'text', array(
            'required' => false,
        ));
        $builder->add('pricePer', 'text', array(
            'required' => false,
        ));
        $builder->add('discount', 'text', array(
            'required' => false,
        ));
        $builder->add('maxPersons', 'integer', array(
            'required' => false,
        ));
        $builder->add('minPersons', 'integer', array(
            'required' => false,
        ));
        $builder->add('soldOut', 'checkbox', array(
            'required' => false,
        ));
        $builder->add('bookingFee', 'text', array(
            'required' => false,
        ));
        $builder->add('extra', 'text', array(
            'required' => false,
        ));

        $builder->add('company');

        $builder->add('places', 'place');

        $builder->add('countryPlace', 'place', [
            'multiple' => false,
            'empty_data'  => null,
            'attr' => array('class' => 'chzn-select', 'data-allowempty' => 1)
        ]);

        $builder->add('originalLanguage');

        $builder->add('mapCategory', null, [
            'multiple' => false,
            'empty_data'  => null,
            'attr' => array('class' => 'chzn-select', 'data-allowempty' => 1),
            'property' => 'name'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sandbox_websitebundle_offerpagetype';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Sandbox\WebsiteBundle\Entity\Pages\OfferPage'
        ));
    }
}
