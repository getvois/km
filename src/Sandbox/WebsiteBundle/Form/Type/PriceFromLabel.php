<?php

namespace Sandbox\WebsiteBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PriceFromLabel extends AbstractType {
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['choices' => [
            '' => '',
            'day' => 'day',
            'trip' => 'trip',
            'stay' => 'stay',
            'room' => 'room',
            'one way' => 'one way',
            'return' => 'return',
            'off' => 'off',
        ]]);
    }

    public function getParent()
    {
        return 'choice';
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'priceFromLabel';
    }
}