<?php

namespace Sandbox\WebsiteBundle\Twig\Extension;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Twig_SimpleFunction;

class HotelInfoWindowHTMLExtension extends \Twig_Extension{

    /** @var  EntityManager */
    private $em;
    /** @var  ChainRouter */
    private $router;

    function __construct(EntityManager $em, ChainRouter $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('hotel_info_window', array($this, 'getHTML')),
        );
    }

    /**
     * @param HotelPage $hotelPage
     * @return null|PackagePage
     */
    public function getHTML(HotelPage $hotelPage){

        $translation = $this->em->getRepository('KunstmaanNodeBundle:NodeTranslation')
            ->getNodeTranslationFor($hotelPage);

        $html = '';

        $html .= '<div class="info_content"><div class="row">';

        if($hotelPage->getCheapestPackage()){

            $translationPackage = $this->em->getRepository('KunstmaanNodeBundle:NodeTranslation')
                ->getNodeTranslationFor($hotelPage->getCheapestPackage());


            $title = $hotelPage->getCheapestPackage()->getTitle();
            if($hotelPage->getCheapestPackage()->getTitleTranslated())
                $title = $hotelPage->getCheapestPackage()->getTitleTranslated();

            $title = str_replace("'", "\'", $title);

            if($hotelPage->getCheapestPackage()->getImage()){
                $html .= '<div class="col-xs-3">';
                $html .= '<img src="'.$hotelPage->getCheapestPackage()->getImage().'" class="img-responsive">';
                $html .= '</div>';
                $html .= '<div class="col-xs-6">';
                $html .= "<h4>$title</h4>";
                $html .= "<p><a href=\"" . $this->router->generate('_slug', ['url' => $translationPackage->getFullSlug()]) . "\">book now</a></p>";
                $html .= "<p><a href=\"" . $this->router->generate('_slug', ['url' => $translation->getFullSlug()]) . "\">hotel</a></p>";
                $html .= '</div>';
                $html .= '<div class="col-xs-3">';
                $html .= "<p class=\"info-window-price\">" . $hotelPage->getCheapestPackage()->getMinprice() . "</p>";
                $html .= '</div>';
            }else{
                $html .= '<div class="col-xs-9">';
                $html .= "<h4>$title</h4>";
                $html .= "<p><a href=\"" . $this->router->generate('_slug', ['url' => $translationPackage->getFullSlug()]) . "\">book now</a></p>";
                $html .= "<p><a href=\"" . $this->router->generate('_slug', ['url' => $translation->getFullSlug()]) . "\">hotel</a></p>";
                $html .= '</div>';
                $html .= '<div class="col-xs-3">';
                $html .= "<p class=\"info-window-price\">" . $hotelPage->getCheapestPackage()->getMinprice() . "</p>";
                $html .= '</div>';
            }

        }else{

            $title = $hotelPage->getTitle();
            $title = str_replace("'", "\'", $title);

            $html .= '<div class="col-xs-12">';
            $html .= "<h4>" . $title . "</h4>";
            $html .= "<p>" . $hotelPage->getShortDescription() . "</p>";
            $html .= "<p><a href=\"" . $this->router->generate('_slug', ['url' => $translation->getFullSlug()]) . "\">hotel</a></p>";
            $html .= '<p><a href="http://www.booking.com/searchresults.et.html?lang=et&si=ai%2Cco%2Cci%2Cre%2Cdi&ss={{ hotel.title }}">booking.com</a></p>';
            $html .= '</div>';
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'hotel_info_window';
    }
}