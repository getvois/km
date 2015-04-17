<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sandbox\WebsiteBundle\Form\Pages\PackageOverviewPageAdminType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * PackageOverviewPage
 *
 * @ORM\Table(name="sb_package_overview_pages")
 * @ORM\Entity
 */
class PackageOverviewPage extends AbstractPage implements HasPageTemplateInterface
{
    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);

        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.entity_manager');

        $packages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagePages($request->getLocale());

        if(!$packages) $packages = [];

        $adapter = new ArrayAdapter($packages);
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
     * @return PackageOverviewPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new PackageOverviewPageAdminType();
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
        return 'SandboxWebsiteBundle:PackageOverview:view.html.twig';
    }
}