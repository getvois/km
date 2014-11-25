<?php

namespace Sandbox\WebsiteBundle\Form\Article;

use Kunstmaan\ArticleBundle\Form\AbstractArticleOverviewPageAdminType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * The admin type for Articleoverview pages
 */
class ArticleOverviewPageAdminType extends AbstractArticleOverviewPageAdminType
{

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\Article\ArticleOverviewPage'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ArticleOverviewPage';
    }
}
