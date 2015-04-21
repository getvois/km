<?php

namespace Sandbox\WebsiteBundle\Form\Pages;

use Kunstmaan\ArticleBundle\Form\AbstractArticlePageAdminType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * HotelPageAdminType
 */
class HotelPageAdminType extends AbstractArticlePageAdminType
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
        $builder->add('hotelId', 'integer', array(
            'required' => false,
        ));
        $builder->add('street', 'text', array(
            'required' => false,
        ));
        $builder->add('city', 'text', array(
            'required' => false,
        ));
        $builder->add('cityParish', 'text', array(
            'required' => false,
        ));
        $builder->add('country', 'text', array(
            'required' => false,
        ));
        $builder->add('latitude', 'text', array(
            'required' => false,
        ));
        $builder->add('longitude', 'text', array(
            'required' => false,
        ));
        $builder->add('shortDescription', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('longDescription', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('criterias', 'entity', array(
            'class' => 'Sandbox\WebsiteBundle\Entity\HotelCriteria',
            'property' => 'name',
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            'attr' => array('class' => 'chzn-select')
        ));

        $builder->add('www');
        $builder->add('places', 'place');
        $builder->add('countryPlace', 'place', ['multiple' => false, 'empty_data'  => null]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sandbox_websitebundle_hotelpagetype';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Sandbox\WebsiteBundle\Entity\Pages\HotelPage'
        ));
    }
}
