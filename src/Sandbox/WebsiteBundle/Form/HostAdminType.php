<?php

namespace Sandbox\WebsiteBundle\Form;

use Sandbox\WebsiteBundle\Repository\Place\PlaceOverviewPageRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * The type for Host
 */
class HostAdminType extends AbstractType
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
        $builder->add('name');
        $builder->add('multiLanguage');
        $builder->add('lang', 'choice',
            [  'required' => false,
                'choices'   =>
                    [
                        'fi' => 'fi',
                        'en' => 'en',
                        'de' => 'de',
                        'fr' => 'fr',
                        'ru' => 'ru',
                        'se' => 'se',
                        'ee' => 'ee'
                    ],
            ]);

        $builder->add('preferredCountries', 'entity', [
            'multiple' => true,
            'class' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage', 'required' => false,
            'query_builder' => function(PlaceOverviewPageRepository $er) {

                if(array_key_exists('REQUEST_URI', $_SERVER)){
                    $locale = (substr($_SERVER['REQUEST_URI'], 1, 2));//get locale from url(not the best way)
                }
                else if (array_key_exists('PATH_INFO', $_SERVER)){
                    $locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                }
                else{
                    $locale = 'en';
                }
                //$locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                return $er->getByLang($locale);
            }
        ]);

        $builder->add('locale');
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'host_form';
    }

}
