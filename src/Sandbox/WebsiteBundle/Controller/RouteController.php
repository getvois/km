<?php

namespace Sandbox\WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RouteController extends Controller
{
    /**
     * @Route("/{path}/")
     * @param $path
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pathAction(Request $request, $path)
    {
        $em = $this->getDoctrine()->getManager();
        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

        $originalLocale = substr($path, 0, 2);

        $locale = substr($path, 0, 2);
        if($host){
            if(!$host->getMultiLanguage()){
                $locale = $host->getLang();
            }
        }
        if(!$locale) $locale = substr($path, 0, 2);


        $path = preg_replace($originalLocale . "/", "", $path, 1);
        $path = preg_replace($originalLocale , "", $path, 1);

        //redirect to host lang
        if($locale != $originalLocale){
            $request->setLocale($locale);
            return $this->redirect("http://" . $request->getHost() . $request->getBaseUrl() . "/" . $locale);
        }

        return $this->forward("KunstmaanNodeBundle:Slug:slug", ['_locale' => $locale, 'url' => $path]);

    }
}
