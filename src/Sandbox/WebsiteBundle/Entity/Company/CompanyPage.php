<?php

namespace Sandbox\WebsiteBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ArticleBundle\Entity\AbstractArticlePage;
use Sandbox\WebsiteBundle\Entity\Company\CompanyAuthor;
use Sandbox\WebsiteBundle\Form\Company\CompanyPageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\Company\CompanyPagePagePartAdminConfigurator;
use Symfony\Component\Form\AbstractType;

/**
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Company\CompanyPageRepository")
 * @ORM\Table(name="sb_company_pages")
 * @ORM\HasLifecycleCallbacks
 */
class CompanyPage extends AbstractArticlePage
{

    /**
     * @var CompanyAuthor
     *
     * @ORM\ManyToOne(targetEntity="CompanyAuthor")
     * @ORM\JoinColumn(name="company_author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new CompanyPageAdminType();
    }

    /**
     * @return array
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new CompanyPagePagePartAdminConfigurator());
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Company/CompanyPage:view.html.twig';
    }

    /**
     * Before persisting this entity, check the date.
     * When no date is present, fill in current date and time.
     *
     * @ORM\PrePersist
     */
    public function _prePersist()
    {
        // Set date to now when none is set
        if ($this->date == null) {
            $this->setDate(new \DateTime());
        }
    }
}
