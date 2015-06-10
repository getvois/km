<?php

namespace Sandbox\WebsiteBundle\Controller;


use Doctrine\ORM\EntityManager;
use Sandbox\WebsiteBundle\Entity\LinkStatistics;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class LinkStatisticsController extends Controller{

    /**
     * @Route("/go/")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function goAction(Request $request)
    {
        $url = $request->query->get('url', '');
        if(!$url) return $this->redirectToRoute("_slug");

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $linkStat = $em->getRepository('SandboxWebsiteBundle:LinkStatistics')
            ->findOneBy(['url' => $url]);

        if(!$linkStat){
            $linkStat = new LinkStatistics();
            $linkStat->setUrl($url);
            $linkStat->setClicks(1);
        }else{
            $linkStat->setClicks($linkStat->getClicks() + 1);
        }

        $em->persist($linkStat);
        $em->flush();

        return $this->redirect($url);
    }
}