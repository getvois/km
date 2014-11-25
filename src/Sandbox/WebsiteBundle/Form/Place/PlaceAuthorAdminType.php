<?php

namespace Sandbox\WebsiteBundle\Form\Place;

use Kunstmaan\ArticleBundle\Form\AbstractAuthorAdminType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PlaceAuthorAdminType extends AbstractAuthorAdminType
{

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceAuthor'
        ));
    }

    /**
     * @return string
     */
    function getName()
    {
        return 'Placeauthor_form';
    }

}