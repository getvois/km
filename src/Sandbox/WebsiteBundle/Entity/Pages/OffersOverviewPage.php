<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Controller\SlugActionInterface;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Sandbox\WebsiteBundle\Form\Pages\OffersOverviewPageAdminType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * OffersOverviewPage
 *
 * @ORM\Table(name="sb_offers_overview_pages")
 * @ORM\Entity
 */
class OffersOverviewPage extends AbstractPage implements HasPageTemplateInterface, SlugActionInterface
{
    public function getControllerAction()
    {
        return "SandboxWebsiteBundle:BackwardCompatibility:service";
    }

    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);

        $em = $container->get('doctrine.orm.entity_manager');

        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages($request->getLocale());

        if(!$offers) $offers = [];

        $context['offers'] = $offers;
    }


    /**
     * Returns the default backend form type for this page
     *
     * @return OffersOverviewPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new OffersOverviewPageAdminType();
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
            'SandboxWebsiteBundle:main',
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
        return 'SandboxWebsiteBundle:OffersOverview:view.html.twig';
    }
}