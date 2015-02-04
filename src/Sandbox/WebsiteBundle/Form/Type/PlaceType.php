<?php

namespace Sandbox\WebsiteBundle\Form\Type;


use Sandbox\WebsiteBundle\Repository\Place\PlaceOverviewPageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PlaceType extends AbstractType{

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'multiple' => true,
            'class' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage', 'required' => false,
            'query_builder' => function(PlaceOverviewPageRepository $er) {
                if(array_key_exists('REQUEST_URI', $_SERVER)){
                    $locale = (substr(str_replace("app_dev.php/", "", $_SERVER['REQUEST_URI']), 1, 2));//get locale from url(not the best way)
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
    }

    public function getParent()
    {
        return 'entity';
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'place';
    }
}