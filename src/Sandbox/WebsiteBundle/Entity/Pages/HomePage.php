<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Sandbox\WebsiteBundle\Form\Pages\HomePageAdminType;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Symfony\Component\Form\AbstractType;

/**
 * HomePage
 *
 * @ORM\Entity()
 * @ORM\Table(name="sb_home_pages")
 */
class HomePage extends AbstractPage  implements HasPageTemplateInterface
{

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new HomePageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array(
            array(
                'name' => 'TagPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\TagPage'
            ),
            array(
                'name' => 'PlacesPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\PlacesPage'
            ),
            array(
                'name'  => 'ContentPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\ContentPage'
            ),
            array(
                'name'  => 'FormPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\FormPage'
            ),
            array(
                'name'  => 'BehatTestPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\BehatTestPage'
            ),
            array(
                'name' => 'News Overview Page',
                'class'=> 'Sandbox\WebsiteBundle\Entity\News\NewsOverviewPage'
            ),
            array(
                'name' => 'Country',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage'
            ),
            array(
                'name' => 'Company',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage'
            ),
            array(
                'name' => 'Article',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Article\ArticleOverviewPage'
            )
        );
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array('SandboxWebsiteBundle:middle-column', 'SandboxWebsiteBundle:slider', 'SandboxWebsiteBundle:left-column', 'SandboxWebsiteBundle:right-column');
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:homepage', 'SandboxWebsiteBundle:homepage-no-slider');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Pages\HomePage:view.html.twig';
    }
}
