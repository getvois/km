<?php

namespace Sandbox\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OfferController extends Controller
{
    /**
     * @Template()
     * @return array
     */
    public function indexAction()
    {
        $content = @file_get_contents('http://api.travelwebpartner.com/app_dev.php/api/offer.filter/');
        if(!$content)
            return [];

        $data = json_decode($content);

        return ['offers' => $data];
    }
}