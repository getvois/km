<?php

namespace Sandbox\WebsiteBundle\Controller;

use Sandbox\WebsiteBundle\Entity\TopImage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TravelbaseController extends Controller
{
    private $randomImage;

    /**
     * @param Request $request
     *
     * @return array
     * @Template()
     */
    public function topTenAction(Request $request)
    {
        return [];
    }


    /**
     * Get random top image
     *
     * @return TopImage
     */
    public function getRandomTopImage()
    {
        if($this->randomImage) return $this->randomImage;

        $em = $this->getDoctrine()->getManager();

        $topImages = $em->getRepository('SandboxWebsiteBundle:TopImage')->findAll();
        if(!$topImages) return null;
        $id = rand(0, count($topImages)-1);

        return $topImages[$id];
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

        $place = $image->getPlace();

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
