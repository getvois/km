<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Sandbox\WebsiteBundle\Form\Pages\CompanyPlacePageAdminType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * CompanyPlacePage
 *
 * @ORM\Table(name="sb_company_place_pages")
 * @ORM\Entity
 */
class CompanyPlacePage extends AbstractPage implements HasPageTemplateInterface
{
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
        $context['lang'] = $locale;

        parent::service($container, $request, $context);
    }

    /**
     * Returns the default backend form type for this page
     *
     * @return CompanyPlacePageAdminType
     */
    public function getDefaultAdminType()
    {
        return new CompanyPlacePageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array(
            array(
                'name' => 'PackageOverviewPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\PackageOverviewPage'
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
                'name' => 'CompanyType',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\CompanyTypePage'
            ),
        );
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(
            'SandboxWebsiteBundle:home',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:contentpage');
    }

    /**
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Pages:CompanyPlace/view.html.twig';
    }
}