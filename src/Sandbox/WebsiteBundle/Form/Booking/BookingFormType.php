<?php
namespace Sandbox\WebsiteBundle\Form\Booking;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BookingFormType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('passengers', 'collection', array(
            'type' => new PassengerType(),
            'allow_add'    => true,
            'by_reference' => false,
            'allow_delete' => true,
        ));

        $builder->add('email');
        $builder->add('phone');
        $builder->add('cc_name');
        $builder->add('cc_number');
        $builder->add('cc_exp_month');
        $builder->add('cc_exp_year');
        $builder->add('cc_cvc');

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\Form\BookingForm',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'booking_form';
    }
}