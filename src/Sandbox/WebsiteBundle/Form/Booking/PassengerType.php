<?php
namespace Sandbox\WebsiteBundle\Form\Booking;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PassengerType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name');
        $builder->add('last_name');
        $builder->add('sex', 'choice', ['choices' => ['male' => 'Male', 'female' => 'Female']]);
        $builder->add('birth_day');
        $builder->add('nationality');
        $builder->add('bnum', 'choice', ['choices' => ['0' => '0', '1' => '1', '2' => '2', '3' => '3'], 'label'=>'Number of Bags']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\Form\Passenger',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'passenger';
    }
}