<?php

namespace Sandbox\WebsiteBundle\Form\Pages;

use Kunstmaan\ArticleBundle\Form\AbstractArticlePageAdminType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * PackagePageAdminType
 */
class PackagePageAdminType extends AbstractArticlePageAdminType
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
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            'attr' => array('class' => 'chzn-select'),
            'property' => 'name',

        ));

        $builder->add('places', 'place');

        $builder->add('bankPayment', 'checkbox', array(
            'required' => false,
        ));
        $builder->add('creditcardPayment', 'checkbox', array(
            'required' => false,
        ));
        $builder->add('onthespotPayment', 'checkbox', array(
            'required' => false,
        ));

        $builder->add('orderNumber');
        $builder->add('company');

        $builder->add('country', 'place', [
            'multiple' => false,
            'empty_data'  => null,
            'attr' => array('class' => 'chzn-select', 'data-allowempty' => 1)
        ]);

        $builder->add('originalLanguage');


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
