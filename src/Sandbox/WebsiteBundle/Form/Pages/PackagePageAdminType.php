<?php

namespace Sandbox\WebsiteBundle\Form\Pages;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * PackagePageAdminType
 */
class PackagePageAdminType extends \Kunstmaan\NodeBundle\Form\PageAdminType
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
        $builder->add('packageId', 'integer', array(
            'required' => false,
        ));
        $builder->add('numberAdults', 'integer', array(
            'required' => false,
        ));
        $builder->add('numberChildren', 'integer', array(
            'required' => false,
        ));
        $builder->add('duration', 'integer', array(
            'required' => false,
        ));
        $builder->add('description', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('checkin', 'text', array(
            'required' => false,
        ));
        $builder->add('checkout', 'text', array(
            'required' => false,
        ));
        $builder->add('minprice', 'number', array(
            'required' => false,
        ));
        $builder->add('image', 'text', array(
            'required' => false,
        ));
        $builder->add('categories', 'entity', array(
            'class' => 'Sandbox\WebsiteBundle\Entity\PackageCategory',
            'expanded' => true,
            'multiple' => true,
            'required' => false,
        ));
        $builder->add('bankPayment', 'checkbox', array(
            'required' => false,
        ));
        $builder->add('creditcardPayment', 'checkbox', array(
            'required' => false,
        ));
        $builder->add('onthespotPayment', 'checkbox', array(
            'required' => false,
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sandbox_websitebundle_packagepagetype';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Sandbox\WebsiteBundle\Entity\Pages\PackagePage'
        ));
    }
}
