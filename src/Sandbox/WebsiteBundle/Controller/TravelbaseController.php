<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Sandbox\WebsiteBundle\Entity\IHostable;
use Sandbox\WebsiteBundle\Entity\TopImage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TravelbaseController extends Controller
{
    private static $randomImage;

    /**
     * @param Request $request
     *
     * @return array
     * @Template()
     */
    public function topTenAction(Request $request)
    {
        $lang = $request->getLocale();

        $em = $this->getDoctrine()->getManager();
        /** @var NodeVersion[] $nodeVersions */
        $nodeVersions = $em->getRepository('KunstmaanNodeBundle:NodeVersion')
            ->createQueryBuilder('v')
            ->select('v')
            ->andWhere('v.refEntityName like :news')
            ->setParameter('news', "Sandbox\\\\WebsiteBundle\\\\Entity\\\\News\\\\NewsPage")
            ->orWhere('v.refEntityName like :article')
            ->setParameter('article', "Sandbox\\\\WebsiteBundle\\\\Entity\\\\Article\\\\ArticlePage")
            ->andWhere('v.type like \'public\'')
            ->orderBy('v.created')
            ->getQuery()
            ->getResult();

        if(!$nodeVersions) return [];

        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

        $pages = [];
        foreach ($nodeVersions as $nodeVersion) {
            $nodeTranslation = $nodeVersion->getNodeTranslation();
            if($nodeTranslation && $nodeTranslation->isOnline() && $nodeTranslation->getLang() == $lang && $nodeTranslation->getNode()->isDeleted() == false){
                /** @var IHostable $page */
                $page = $nodeVersion->getRef($em);

                //check host
                if($host){
                    foreach ($page->getHosts() as $h) {
                        if($h->getName() == $host){
                            $pages[$nodeTranslation->getNode()->getId()] = $page;
                            break;
                        }
                    }
                }else{
                    $pages[$nodeTranslation->getNode()->getId()] = $page;
                }

                if(count($pages) == 10) break;
            }
        }


        return ['pages' => $pages];
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

        $place = $image->getPlace();

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
}
