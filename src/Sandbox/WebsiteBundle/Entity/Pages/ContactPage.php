<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\FormBundle\Entity\AbstractFormPage;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Sandbox\WebsiteBundle\Form\Pages\ContactPageAdminType;

/**
 * ContactPage
 *
 * @ORM\Table(name="sb_contact_pages")
 * @ORM\Entity
 */
class ContactPage extends AbstractFormPage implements HasPageTemplateInterface
{

    /**
     * Returns the default backend form type for this page
     *
     * @return ContactPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new ContactPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array();
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(
            'SandboxWebsiteBundle:contactpage',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:contactpage');
    }

    /**
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Pages\ContactPage:view.html.twig';
    }
}