<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\IHostable;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\TopImage;
use Sandbox\WebsiteBundle\Helper\FullNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TravelbaseController extends Controller
{
    private static $randomImage;

    /**
     * @param Request $request
     * @return array
     * @Template()
     */
    public function menuAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $root = $em->getRepository('KunstmaanNodeBundle:Node')->findOneBy(
          ['parent' => null, 'deleted' => 0, 'hiddenFromNav' => 0]
        );

        if(!$root)
            return[];

        $exclude = ['countries', 'companies']; //node internal name

        $pages = [];
        /** @var Node $node */
        foreach ($root->getChildren() as $node) {
            if(!$node->isDeleted() && !$node->isHiddenFromNav() && !in_array($node->getInternalName(), $exclude)){
                $translation = $node->getNodeTranslation($request->getLocale());
                if($translation){
                    $page = $translation->getRef($em);
                    if($page){
                        $pages[] = $page;
                    }
                }
            }
        }

        return ['pages' => $pages];
    }

    /**
     * @param Request $request
     *
     * @return array
     * @Template()
     */
    public function topTenAction(Request $request)
    {
        $lang = $request->getLocale();

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $host = $em->getRepository('SandboxWebsiteBundle:Host') //todo kosmos move host to service to minimize queries
            ->findOneBy(['name' => $request->getHost()]);

        $news = $this->get('nodehelper')//todo kosmos images loads as proxy
            ->getFullNodesWithParam('', [], 'Sandbox\WebsiteBundle\Entity\News\NewsPage', $lang, 0, 5, $host, 'p.date DESC');

        $articles = $this->get('nodehelper')
            ->getFullNodesWithParam('', [], 'Sandbox\WebsiteBundle\Entity\Article\ArticlePage', $lang, 0, 5, $host, 'p.date DESC');

        $fullNodes = array_merge($news, $articles);

        usort($fullNodes, function (FullNode $a, FullNode $b)
        {
            if ($a->getPage()->getDate()->getTimestamp() == $b->getPage()->getDate()->getTimestamp()) {
                return 0;
            }
            return ($a->getPage()->getDate()->getTimestamp() < $b->getPage()->getDate()->getTimestamp()) ? 1 : -1;
        });


//        $realNews = $em->getRepository('SandboxWebsiteBundle:News\NewsPage')
//            ->getArticles($lang, 0, 5, $host);
//        $realArticles = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')
//            ->getArticles($lang, 0, 5, $host);
//
//        $pages = array_merge($realNews, $realArticles);
//
//        usort($pages, function ($a, $b)
//        {
//            /** @var $a ArticlePage */
//            /** @var $b ArticlePage */
//            if ($a->getDate()->getTimestamp() == $b->getDate()->getTimestamp()) {
//                return 0;
//            }
//            return ($a->getDate()->getTimestamp() < $b->getDate()->getTimestamp()) ? 1 : -1;
//        });

        return ['fullNodes' => $fullNodes];
    }

    /**
     * Get random top image
     *
     * @return TopImage
     */
    public function getRandomTopImage()
    {
        if(self::$randomImage) return self::$randomImage;

        $em = $this->getDoctrine()->getManager();

        $topImages = $em->getRepository('SandboxWebsiteBundle:TopImage')->findBy(['visible' => 1]);
        if(!$topImages) return null;
        $id = rand(0, count($topImages)-1);

        self::$randomImage = $topImages[$id];
        return self::$randomImage;
    }

    /**
     * Get picture url
     *
     * @return Response
     */
    public function getRandomImageUrlAction()
    {
        $image = $this->getRandomTopImage();
        if(!$image){
            return new Response("");
        }

        return new Response($image->getPicture()->getUrl());
    }

    /**
     * Get picture title
     *
     * @param Request $request
     * @param null $image
     * @return Response
     */
    public function getRandomImageTitleAction(Request $request, $image = null)
    {
        $lang = $request->getLocale();
        if(!$image)
            $image = $this->getRandomTopImage();

        if(!$image)
            return new Response("");

        $place = $this->getRandomImagePlace($lang, $image);

        if($place){
            return new Response("<a href='/$place'>" . $image->getTitle() . "</a>");

        }

        if($image->getExternal()){
            return new Response("<a href='".$image->getExternal()."'>" . $image->getTitle() . "</a>");
        }

        if(is_numeric($image->getTitle()))
            return new Response("");

        return new Response($image->getTitle());
    }

    /**
     * Get picture place
     *
     * @param string $lang
     * @param null $image
     * @return Response
     */
    public function getRandomImagePlace($lang = 'en', $image = null)
    {
        if(!$image)
            $image = $this->getRandomTopImage();

        if(!$image) return "";

        $place = $image->getPlaces();
        $place = $place->first();
        if(!$place){
            return null;
        }


        $em = $this->getDoctrine()->getManager();
        $version = $em->getRepository('KunstmaanNodeBundle:NodeVersion')
            ->findOneBy(['refId' => $place->getId(),
                'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
                'type' => 'public'
            ]);

        if(!$version){
            //check external url
            if($image->getExternal())
                return $image->getExternal();

            return null;
        }

        $translation = $version->getNodeTranslation()->getNode()->getNodeTranslation($lang);

        if(!$translation) return null;

        return $lang . '/' . $translation->getFullSlug();
    }


    /**
     * @param AbstractPage $place
     * @param Host $host
     *
     * @return array
     */
    public function placeInHostAction(Request $request, AbstractPage $place, Host $host)
    {
        /** @var $place IHostable */
        if($place->getHosts()->contains($host)){
            //url
            return $this->render('@SandboxWebsite/Layout/toplacelink.html.twig', ['place' => $place]);
        }else{
            if($host->getFromPlaces()->contains($place)){
                //url
                return $this->render('@SandboxWebsite/Layout/toplacelink.html.twig', ['place' => $place]);
            }else{
                //check host place children
                $em = $this->getDoctrine()->getManager();

                foreach ($host->getFromPlaces() as $fromPlace) {
                    $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($fromPlace);
                    if(!$node){//should not happen
                        //span
                        return $this->render('@SandboxWebsite/Layout/toplacespan.html.twig', ['place' => $place]);
                    }
                    $url = $this->checkChildren($place, $node, $request->getLocale(), $em);

                    if($url)
                        //url
                        return $this->render('@SandboxWebsite/Layout/toplacelink.html.twig', ['place' => $place]);
                }

                return $this->render('@SandboxWebsite/Layout/toplacespan.html.twig', ['place' => $place]);
            }
        }
    }


    private function checkChildren(AbstractPage $aPlace, Node $node, $lang, $em)
    {
        foreach ($node->getChildren() as $child) {
            /** @var $child Node */
            if($child->isDeleted()) continue;

            if($child->getRefEntityName() == 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage'){
                //check in host
                $translation = $child->getNodeTranslation($lang);
                if(!$translation){
                    continue;
                }
                if(!$translation->isOnline()) continue;
                $place = $translation->getRef($em);
                if($place->getId() == $aPlace->getId()){
                    return $place;
                }
                $this->checkChildren($aPlace, $child, $lang, $em);
            }
        }
        return null;
    }


    /**
     * @param Request $request
     *
     * @Template()
     * @return array
     */
    public function placesFooterAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $host = $this->get('hosthelper')->getHost();

        $root = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getRoot($request->getLocale(), $host);

        if(!$root) return [];

        $places = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByRoot($root['id'], $request->getLocale(), $host);

        if(!$places) return [];

        return ['root' => $root, 'places' => $places];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Template()
     */
    public function articleFooterAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $host = $this->get('hosthelper')->getHost();

        $root = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')
            ->getRoot($request->getLocale(), $host);

        if(!$root) return [];

        $articles = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')
            ->getByRoot($root['id'], $request->getLocale(), $host, 10);

        if(!$articles) return [];

        return ['root' => $root, 'articles' => $articles];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Template()
     */
    public function companyFooterAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $root = $em->getRepository('SandboxWebsiteBundle:Company\CompanyPage')
            ->getRoot('companies', $request->getLocale());

        if(!$root) return [];

        $companies = $em->getRepository('SandboxWebsiteBundle:Company\CompanyPage')
            ->getByRoot($root['id'], $request->getLocale());

        if(!$companies) return [];

        return ['root' => $root, 'companies' => $companies];
    }
}
