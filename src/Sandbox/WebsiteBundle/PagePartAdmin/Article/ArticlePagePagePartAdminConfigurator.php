<?php

namespace Sandbox\WebsiteBundle\PagePartAdmin\Article;

use Kunstmaan\ArticleBundle\PagePartAdmin\AbstractArticlePagePagePartAdminConfigurator;

/**
 * The PagePartAdminConfigurator for the ArticlePage
 */
class ArticlePagePagePartAdminConfigurator extends AbstractArticlePagePagePartAdminConfigurator
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
                array(
                    'name' => 'GoogleMap',
                    'class'=> 'Sandbox\WebsiteBundle\Entity\PageParts\GoogleMapPagePart'
                ),
                array(
                    'name' => 'Gallery',
                    'class'=> 'Sandbox\WebsiteBundle\Entity\PageParts\GalleryPagePart'
                ),
                array(
                    'name' => 'Source',
                    'class'=> 'Sandbox\WebsiteBundle\Entity\PageParts\SourcePagePart'
                ),
            ), $this->pagePartTypes
        );
    }

}
