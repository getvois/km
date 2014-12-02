<?php

namespace Sandbox\WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RouteController extends Controller
{
    /**
     * @Route("/{path}/")
     * @param $path
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pathAction($path)
    {
        $locale = substr($path, 0, 2);

        $path = str_replace($locale . "/" , "", $path);
        $path = str_replace($locale , "", $path);

        return $this->forward("KunstmaanNodeBundle:Slug:slug", ['locale' => $locale, 'url' => $path]);

    }
}
