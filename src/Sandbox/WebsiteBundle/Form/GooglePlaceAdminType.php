<?php

namespace Sandbox\WebsiteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * The type for GooglePlace
 */
class GooglePlaceAdminType extends AbstractType
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
        $builder->add('title', 'text', ['required' => true]);
        $builder->add('type', 'text', ['required' => false]);
        $builder->add('latitude', 'text', ['required' => false]);
        $builder->add('longitude', 'text', ['required' => false]);
//        $builder->add('description', 'text', ['required' => false]);
        $builder->add('url', 'text', ['required' => false]);

        ///config.yml: liip_imagine.filter_sets
//        $builder->add('size', 'image_size');

        $builder->add('content', 'textarea', array('label' => 'pagepart.text.content', 'required' => false, 'attr' => array('rows' => 32, 'cols' => 600, 'class' => 'rich_editor')));

//        $builder->add('wrapperClass');
//        $builder->add('imgClass');

        $builder->add('image', 'media', array(
            'pattern' => 'KunstmaanMediaBundle_chooser',
            'mediatype' => 'image',
            'required' => false,
        ));
//        $builder->add('imageAltText', 'text', array(
//            'required' => false,
//        ));
//        $builder->add('responsive', 'checkbox', array(
//            'required' => false,
//        ));
//        $builder->add('alt', 'text', array(
//            'required' => false,
//        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\GooglePlace'
        ));    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'googleplace_form';
    }

}
