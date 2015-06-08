<?php

namespace Sandbox\WebsiteBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PlaceOverviewPageController extends Controller{

    public function serviceAction(Request $request)
    {


        $page = $request->attributes->get('_page');

        foreach ($request->attributes->all() as $key => $value) {
            var_dump($key);
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $context = [];
        $request->attributes->set('_renderContext',$context);
    }
}