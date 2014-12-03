<?php

namespace Sandbox\WebsiteBundle\PagePartAdmin\News;

use Kunstmaan\ArticleBundle\PagePartAdmin\AbstractArticlePagePagePartAdminConfigurator;

/**
 * The PagePartAdminConfigurator for the NewsPage
 */
class NewsPagePagePartAdminConfigurator extends AbstractArticlePagePagePartAdminConfigurator
{
    public function __construct(array $pagePartTypes = array())
    {
        parent::__construct($pagePartTypes);
        $this->pagePartTypes = array_merge(
            array(
                array(
                    'name' => 'Image thumbnail',
                    'class'=> 'Sandbox\WebsiteBundle\Entity\PageParts\ImageThumbnailPagePart'
                ),
            ), $this->pagePartTypes
        );
    }
}
