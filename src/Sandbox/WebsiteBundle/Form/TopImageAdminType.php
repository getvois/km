<?php

namespace Sandbox\WebsiteBundle\Form;

use Sandbox\WebsiteBundle\Repository\Place\PlaceOverviewPageRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * The type for TopImage
 */
class TopImageAdminType extends AbstractType
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
        $builder->add('title');
        $builder->add('external');
        $builder->add('place', 'entity', [
                'class' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage', 'required' => false,
                'query_builder' => function(PlaceOverviewPageRepository $er) {
                    $locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                    return $er->getByLang($locale);
                }
            ]
        );
        $builder->add(
            'picture',
            'media',
            array(
                'pattern'  => 'KunstmaanMediaBundle_chooser',
                'required' => false,
            )
        );
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
