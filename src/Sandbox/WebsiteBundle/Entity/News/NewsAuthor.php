<?php

namespace Sandbox\WebsiteBundle\Entity\News;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ArticleBundle\Entity\AbstractAuthor;
use Sandbox\WebsiteBundle\Form\News\NewsAuthorAdminType;
use Symfony\Component\Form\AbstractType;

/**
 * The author for a News
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\News\NewsAuthorRepository")
 * @ORM\Table(name="sb_news_authors")
 */
class NewsAuthor extends AbstractAuthor
{

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getAdminType()
    {
        return new NewsAuthorAdminType();
    }

}