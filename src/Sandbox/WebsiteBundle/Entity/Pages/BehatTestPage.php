<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Sandbox\WebsiteBundle\Form\Pages\BehatTestPageAdminType;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Symfony\Component\Form\AbstractType;

/**
 * BehatTestPage
 *
 * @ORM\Entity()
 * @ORM\Table(name="sb_behat_test_pages")
 */
class BehatTestPage extends AbstractPage  implements HasPageTemplateInterface
{

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new BehatTestPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array(
            array(
                'name' => 'CompanyPlacePage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\CompanyPlacePage'
            ),
            array(
                'name' => 'CompanyTypePage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\CompanyTypePage'
            ),
            array(
                'name' => 'TagPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\TagPage'
            ),
            array(
                'name' => 'PlacesPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\PlacesPage'
            ),
            array(
                'name'  => 'HomePage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\HomePage'
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
                'name'  => 'SatelliteOverviewPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\SatelliteOverviewPage'
            ),
        );
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:behat-test-page');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return '';
    }
}
