<?php

namespace Sandbox\WebsiteBundle\Entity\Place;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ArticleBundle\Entity\AbstractAuthor;
use Sandbox\WebsiteBundle\Form\Place\PlaceAuthorAdminType;
use Symfony\Component\Form\AbstractType;

/**
 * The author for a Place
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Place\PlaceAuthorRepository")
 * @ORM\Table(name="sb_place_authors")
 */
class PlaceAuthor extends AbstractAuthor
{

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getAdminType()
    {
        return new PlaceAuthorAdminType();
    }

}