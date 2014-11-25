<?php

namespace Sandbox\WebsiteBundle\Form\Article;

use Kunstmaan\ArticleBundle\Form\AbstractAuthorAdminType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ArticleAuthorAdminType extends AbstractAuthorAdminType
{

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\Article\ArticleAuthor'
        ));
    }

    /**
     * @return string
     */
    function getName()
    {
        return 'Articleauthor_form';
    }

}