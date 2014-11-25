<?php

namespace Sandbox\WebsiteBundle\AdminList\News;

use Doctrine\ORM\QueryBuilder;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticleAuthorAdminListConfigurator;

/**
 * The AdminList configurator for the NewsAuthor
 */
class NewsAuthorAdminListConfigurator extends AbstractArticleAuthorAdminListConfigurator
{

    /**
     * Return current bundle name.
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'SandboxWebsiteBundle';
    }

    /**
     * Return current entity name.
     *
     * @return string
     */
    public function getEntityName()
    {
        return 'News\NewsAuthor';
    }

}
