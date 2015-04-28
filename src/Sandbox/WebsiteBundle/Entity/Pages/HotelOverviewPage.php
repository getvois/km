<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sandbox\WebsiteBundle\Form\Pages\HotelOverviewPageAdminType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * HotelOverviewPage
 *
 * @ORM\Table(name="sb_hotel_overview_pages")
 * @ORM\Entity
 */
class HotelOverviewPage extends AbstractPage implements HasPageTemplateInterface
{
    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);
        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.entity_manager');

        $page = $context['page'];

        $node = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($page);

        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPagesByParent($request->getLocale(), $node);

        if(!$hotels) $hotels = [];

        $adapter = new ArrayAdapter($hotels);
        $pagerfanta = new Pagerfanta($adapter);

        $pagenumber = $request->get('page');
        if (!$pagenumber || $pagenumber < 1) {
            $pagenumber = 1;
        }
        $pagerfanta->setMaxPerPage(500);
        $pagerfanta->setCurrentPage($pagenumber);
        $context['pagerfanta'] = $pagerfanta;
    }

    /**
     * Returns the default backend form type for this page
     *
     * @return HotelOverviewPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new HotelOverviewPageAdminType();
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
            ),);
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
        return 'SandboxWebsiteBundle:HotelOverview:view.html.twig';
    }
}