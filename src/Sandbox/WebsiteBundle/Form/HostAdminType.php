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

        $builder->add('preferredCountries', 'place');

        $builder->add('fromPlaces', 'place');

        $builder->add('locale');
        $builder->add('app_id');
        $builder->add('app_secret');
        $builder->add('fb_block');
        $builder->add('group_id');
        $builder->add('page_access_token');
        $builder->add('vk_app_id');
        $builder->add('vk_app_secret');
        $builder->add('vk_group_id');
        $builder->add('vk_access_token');
        $builder->add('ga_tracking_id');


        $builder->add('tabs', 'choice', ['choices' => [
            'flight.hotel' => 'flight.hotel',
            'flight.only' => 'flight.only',
            'hotel' => 'hotel',
            'ferry' => 'ferry',
            'cruise' => 'cruise',
            'club' => 'club',
            //'offer' => 'offer',
            'package' => 'package',
            'baltica' => 'baltica',
        ], 'required' => false, 'multiple' => true]);
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
