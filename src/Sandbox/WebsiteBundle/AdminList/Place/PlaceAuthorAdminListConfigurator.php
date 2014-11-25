<?php

namespace Sandbox\WebsiteBundle\AdminList\Place;

use Doctrine\ORM\QueryBuilder;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticleAuthorAdminListConfigurator;

/**
 * The AdminList configurator for the PlaceAuthor
 */
class PlaceAuthorAdminListConfigurator extends AbstractArticleAuthorAdminListConfigurator
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
        return 'Place\PlaceAuthor';
    }

}
