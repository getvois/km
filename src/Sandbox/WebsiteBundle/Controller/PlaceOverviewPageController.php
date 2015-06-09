<?php

namespace Sandbox\WebsiteBundle\Controller;


use Kunstmaan\NodeBundle\Helper\RenderContext;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PlaceOverviewPageController extends Controller{

    public function serviceAction(Request $request)
    {
        /** @var PlaceOverviewPage $page */
        $page = $request->attributes->get('_entity');
        $translation = $request->attributes->get('_nodeTranslation');
        $container = $this->container;

        $context = new RenderContext();
        $context['page'] = $page;
        $context['nodetranslation'] = $translation;

        $page->service($container, $request, $context);

        $request->attributes->set('_renderContext',$context);
    }
}