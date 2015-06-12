<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\NodeBundle\Controller\SlugActionInterface;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Sandbox\WebsiteBundle\Form\Pages\ContentPageAdminType;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;

/**
 * ContentPage
 *
 * @ORM\Entity()
 * @ORM\Table(name="sb_content_pages")
 */
class ContentPage extends AbstractPage  implements HasPageTemplateInterface, SlugActionInterface
{

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\MediaBundle\Entity\Media")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="picture_id", referencedColumnName="id")
     * })
     */
    private $picture;

    /**
     * Set picture
     *
     * @param Media $picture
     * @return ContentPage
     */
    public function setPicture(Media $picture = null)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return Media
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new ContentPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array (
            array(
                'name' => 'BookingcomPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\BookingcomPage'
            ),
            array(
                'name' => 'OffersOverviewPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\OffersOverviewPage'
            ),
            array(
                'name' => 'OfferPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\OfferPage'
            ),
            array(
                'name' => 'HotelOverviewPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\HotelOverviewPage'
            ),
            array(
                'name' => 'HotelPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\HotelPage'
            ),
            array(
                'name' => 'CompanyTypePage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\CompanyTypePage'
            ),
            array(
                'name'  => 'ContentPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\ContentPage'
            ),
            array(
                'name'  => 'SatelliteOverviewPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\SatelliteOverviewPage'
            ),
            array(
                'name' => 'Company',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage'
            ),
        );
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array('SandboxWebsiteBundle:main');
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:contentpage');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Pages\ContentPage:view.html.twig';
    }

    public function getControllerAction()
    {
        return "SandboxWebsiteBundle:BackwardCompatibility:service";
    }

    /**
     * @param ContainerInterface $container
     * @param Request $request
     * @param RenderContext $context
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|void
     */
    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        $locale = $request->getLocale();//page language code
        $placesLocale = [];//array of translated online nodes to return to template

        /** @var NodeTranslation $nodeTranslation */
        $nodeTranslation = $context->getArrayCopy()['nodetranslation'];
        $nodeChildren = $nodeTranslation->getNode()->getChildren();

        /** @var Node $node */
        foreach ($nodeChildren as $node) {
            $translation = $node->getNodeTranslation($locale);
            if($translation && $translation->isOnline()){
                $placesLocale[] = $translation;
            }
        }

        $context['places'] = $placesLocale;
        //$context['news'] = $news;
        $context['lang'] = $locale;

        parent::service($container, $request, $context);
    }

}
