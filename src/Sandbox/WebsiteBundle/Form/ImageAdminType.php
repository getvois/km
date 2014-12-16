<?php

namespace Sandbox\WebsiteBundle\Form;

use Sandbox\WebsiteBundle\Repository\Place\PlaceOverviewPageRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * The type for TopImage
 */
class ImageAdminType extends AbstractType
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
        $builder->add('title', 'text', ['required' => false]);
        $builder->add('copyright', 'text', ['required' => false]);
        $builder->add('wrapperClass', 'text', ['required' => false]);
        $builder->add('class', 'text', ['required' => false]);
        $builder->add(
            'picture',
            'media',
            array(
                'pattern'  => 'KunstmaanMediaBundle_chooser',
                'required' => false,
            )
        );
        ///config.yml: liip_imagine.filter_sets
        $builder->add('size', 'image_size');
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\Image'
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'topimage_form';
    }

}
