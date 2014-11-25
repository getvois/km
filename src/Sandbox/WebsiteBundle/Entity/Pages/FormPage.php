<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Sandbox\WebsiteBundle\Form\Pages\FormPageAdminType;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\FormBundle\Entity\AbstractFormPage;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Symfony\Component\Form\AbstractType;

/**
 * FormPage
 *
 * @ORM\Entity()
 * @ORM\Table(name="sb_form_pages")
 */
class FormPage extends AbstractFormPage implements HasPageTemplateInterface
{

    /**
     * Returns the default backend form type for this form
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new FormPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array(
            array(
                'name'  => 'ContentPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\ContentPage'
            ),
            array (
                'name'  => 'FormPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\FormPage'
            )
        );
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array('SandboxWebsiteBundle:form');
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:formpage');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Pages\FormPage:view.html.twig';
    }
}
