<?php

namespace Sandbox\WebsiteBundle\Form\News;

use Kunstmaan\ArticleBundle\Form\AbstractArticleOverviewPageAdminType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * The admin type for Newsoverview pages
 */
class NewsOverviewPageAdminType extends AbstractArticleOverviewPageAdminType
{

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\News\NewsOverviewPage'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'NewsOverviewPage';
    }
}
