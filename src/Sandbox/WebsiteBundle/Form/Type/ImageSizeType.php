<?php
namespace Sandbox\WebsiteBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImageSizeType extends AbstractType {

    private $imageFilters;

    function __construct($imageFilters)
    {
        $imageFilters = array_slice($imageFilters, 6, null, true);
        
        $filters = [];
        foreach ($imageFilters as $filter => $value) {
            $filters[$filter] = $filter;
        }
        $this->imageFilters = $filters;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(['choices' => $this->imageFilters]);
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
        return 'image_size';
    }
}