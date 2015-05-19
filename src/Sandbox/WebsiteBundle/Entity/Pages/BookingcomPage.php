<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Sandbox\WebsiteBundle\Form\Pages\BookingcomPageAdminType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * BookingcomPage
 *
 * @ORM\Table(name="sb_bookingcom_pages")
 * @ORM\Entity
 */
class BookingcomPage extends AbstractPage implements HasPageTemplateInterface
{
    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);

        $context['ss'] = $request->query->get('ss', '');
        $context['checkin_monthday'] = $request->query->get('checkin_monthday', '1');
        $context['checkin_year_month'] = $request->query->get('checkin_year_month', date('Y-n'));
        $context['checkout_monthday'] = $request->query->get('checkout_monthday', '1');
        $context['checkout_year_month'] = $request->query->get('checkout_year_month', date('Y-n'));
        $context['idf'] = $request->query->get('idf', '')?'true':'false';

    }


    /**
     * Returns the default backend form type for this page
     *
     * @return BookingcomPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new BookingcomPageAdminType();
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
        return 'SandboxWebsiteBundle:Pages:BookingcomPage/view.html.twig';
    }
}