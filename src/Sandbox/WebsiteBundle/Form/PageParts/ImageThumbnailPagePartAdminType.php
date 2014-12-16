<?php

namespace Sandbox\WebsiteBundle\Form\PageParts;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * ImageThumbnailPagePartAdminType
 */
class ImageThumbnailPagePartAdminType extends \Symfony\Component\Form\AbstractType
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
        $builder->add('image', 'media', array(
            'pattern' => 'KunstmaanMediaBundle_chooser',
            'mediatype' => 'image',
            'required' => false,
        ));
        $builder->add('imageAltText', 'text', array(
            'required' => false,
        ));
        $builder->add('responsive', 'checkbox', array(
            'required' => false,
        ));
        $builder->add('alt', 'text', array(
            'required' => false,
        ));
        $builder->add('linkUrl', 'urlchooser', array(
            'required' => false,
        ));
        $builder->add('linkText', 'text', array(
            'required' => false,
        ));
        $builder->add('linkNewWindow', 'checkbox', array(
            'required' => false,
        ));


        ///config.yml: liip_imagine.filter_sets
        $builder->add('size', 'image_size');

        $builder->add('content', 'textarea', array('label' => 'pagepart.text.content', 'required' => false, 'attr' => array('rows' => 32, 'cols' => 600, 'class' => 'rich_editor')));

        $builder->add('title');
        $builder->add('wrapperClass');
        $builder->add('imgClass');
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'sandbox_websitebundle_imagethumbnailpageparttype';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => '\Sandbox\WebsiteBundle\Entity\PageParts\ImageThumbnailPagePart'
        ));
    }
}
