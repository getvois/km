<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContactPage
 *
 * @ORM\Table(name="sb_contact_pages")
 * @ORM\Entity
 */
class ContactPage extends \Kunstmaan\FormBundle\Entity\AbstractFormPage implements \Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface
{

    /**
     * Returns the default backend form type for this page
     *
     * @return \Sandbox\WebsiteBundle\Form\Pages\ContactPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new \Sandbox\WebsiteBundle\Form\Pages\ContactPageAdminType();
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