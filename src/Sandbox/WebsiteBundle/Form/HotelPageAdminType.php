<?php

namespace Sandbox\WebsiteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * The type for HotelPage
 */
class HotelPageAdminType extends AbstractType
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
        $builder->add('street');
        $builder->add('hotelId');
        $builder->add('city');
        $builder->add('cityParish');
        $builder->add('country');
        $builder->add('latitude');
        $builder->add('longitude');
        $builder->add('shortDescription');
        $builder->add('longDescription');
        $builder->add('title');
        $builder->add('pageTitle');
        $builder->add('criterias');
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'hotelpage_form';
    }
}
