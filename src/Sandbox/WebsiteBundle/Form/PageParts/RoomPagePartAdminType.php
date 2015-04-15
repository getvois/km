<?php

namespace Sandbox\WebsiteBundle\Form\PageParts;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * RoomPagePartAdminType
 */
class RoomPagePartAdminType extends \Symfony\Component\Form\AbstractType
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
        $builder->add('roomId', 'integer', array(
            'required' => false,
        ));
        $builder->add('name', 'text', array(
            'required' => false,
        ));
        $builder->add('image', 'text', array(
            'required' => false,
        ));
        $builder->add('content', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
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
        return 'sandbox_websitebundle_roompageparttype';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Sandbox\WebsiteBundle\Entity\PageParts\RoomPagePart'
        ));
    }
}
