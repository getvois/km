<?php

namespace Sandbox\WebsiteBundle\Entity\Article;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ArticleBundle\Entity\AbstractAuthor;
use Sandbox\WebsiteBundle\Form\Article\ArticleAuthorAdminType;
use Symfony\Component\Form\AbstractType;

/**
 * The author for a Article
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Article\ArticleAuthorRepository")
 * @ORM\Table(name="sb_article_authors")
 */
class ArticleAuthor extends AbstractAuthor
{

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getAdminType()
    {
        return new ArticleAuthorAdminType();
    }

}