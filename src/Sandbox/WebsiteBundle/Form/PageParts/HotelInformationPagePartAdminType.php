<?php

namespace Sandbox\WebsiteBundle\Form\PageParts;

use Sandbox\WebsiteBundle\Form\HotelImageAdminType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * HotelInformationPagePartAdminType
 */
class HotelInformationPagePartAdminType extends \Symfony\Component\Form\AbstractType
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
        $builder->add('name', 'text', array(
            'required' => false,
        ));
        $builder->add('description', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('images', 'collection', array(
            'type' => new HotelImageAdminType(),
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'cascade_validation' => true,
            'attr' => array(
                'nested_form' => true,
                'nested_form_min' => 1,
                'nested_form_max' => 20,
            )
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sandbox_websitebundle_hotelinformationpageparttype';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Sandbox\WebsiteBundle\Entity\PageParts\HotelInformationPagePart',
            'cascade_validation' => true,
        ));
    }
}