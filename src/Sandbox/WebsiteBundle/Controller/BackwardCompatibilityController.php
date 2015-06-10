<?php

namespace Sandbox\WebsiteBundle\Controller;


use Kunstmaan\NodeBundle\Entity\PageInterface;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BackwardCompatibilityController extends Controller{

    public function serviceAction(Request $request)
    {
        /** @var PageInterface $page */
        $page = $request->attributes->get('_entity');
        $translation = $request->attributes->get('_nodeTranslation');
        $container = $this->container;

        $context = new RenderContext();
        $context['page'] = $page;
        $context['nodetranslation'] = $translation;

        $page->service($container, $request, $context);

        $host = $this->get('hosthelper')->getHost();
        $ads = $this->get('adhelper')->getAds($page, $request->getLocale(), $host);
        $context['ads'] = $ads;

        $request->attributes->set('_renderContext',$context);
    }
}