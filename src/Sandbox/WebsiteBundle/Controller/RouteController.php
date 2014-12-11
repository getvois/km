<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
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
        /** @var ObjectManager $em */
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


        $newPath = preg_replace('/' . $originalLocale . "\//", "", $path, 1);
        if($newPath == $path)
            $path = preg_replace('/' . $originalLocale . '/' , "", $path, 1);
        else
            $path = $newPath;

        //redirect to host lang
        if($locale != $originalLocale){
            $request->setLocale($locale);
            return $this->redirect("http://" . $request->getHost() . $request->getBaseUrl() . "/" . $locale);
        }


//        //check for tag as last argument of path
//        $args = explode('/', $path);
//        $lastArg = $args[count($args)-1];
//
//        $tag = $em->getRepository('KunstmaanTaggingBundle:Tag')
//            ->findOneBy(['name' => $lastArg]);
//
//        if($tag){
//
//        }

        return $this->forward("KunstmaanNodeBundle:Slug:slug", ['_locale' => $locale, 'url' => $path]);

    }
}
