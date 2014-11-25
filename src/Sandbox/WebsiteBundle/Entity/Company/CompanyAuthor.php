<?php

namespace Sandbox\WebsiteBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ArticleBundle\Entity\AbstractAuthor;
use Sandbox\WebsiteBundle\Form\Company\CompanyAuthorAdminType;
use Symfony\Component\Form\AbstractType;

/**
 * The author for a Company
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Company\CompanyAuthorRepository")
 * @ORM\Table(name="sb_company_authors")
 */
class CompanyAuthor extends AbstractAuthor
{

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getAdminType()
    {
        return new CompanyAuthorAdminType();
    }

}