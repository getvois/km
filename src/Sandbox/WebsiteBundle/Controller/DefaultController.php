<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\TranslatorBundle\Entity\Translation;
use Kunstmaan\UtilitiesBundle\Helper\Slugifier;
use Sandbox\WebsiteBundle\Entity\Form\BookingForm;
use Sandbox\WebsiteBundle\Entity\Form\Passenger;
use Sandbox\WebsiteBundle\Entity\HotelCriteria;
use Sandbox\WebsiteBundle\Entity\HotelImage;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\PackageCategory;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Form\Booking\BookingFormType;
use Sandbox\WebsiteBundle\Helper\CurrencyConverter;
use Sandbox\WebsiteBundle\Helper\FacebookHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityRetrievalStrategyInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DefaultController extends Controller
{
    /**
     * @Route("/api-filter/{body}")
     * @param Request $request
     * @param $body
     * @return JsonResponse
     */
    public function filterAction(Request $request, $body)
    {
        $noItemsFoundTitle = $this->get('translator')->trans('no.items.found.title', [], 'frontend');
        $noItemsFoundText = $this->get('translator')->trans('no.items.found.text', [], 'frontend');
        $loadingMessage = $this->get('translator')->trans('loading.message', [], 'frontend');
        $loading = "<span class='loading'>
            <div class='loading-container'>
                <h3 class='loading-message'>
                    $loadingMessage
                </h3>
            </div>
        </span>";

        $noItemsFoundHTML = "$loading<div class='loaded-message'><h2>$noItemsFoundTitle</h2><p>$noItemsFoundText</p></div>";

        if($request->getContent()){
            $filter = json_decode($request->getContent());

            if($body == 1 && (in_array(4, $filter->type) || in_array(3, $filter->type))){
                //import from skypicker first
                $url = 'http://api.travelwebpartner.com/api/skypicker.import/';
                $options = array(
                  'http' => array(
                    'header'  => "Host: api.travelwebpartner.com\r\nConnection: close\r\nContent-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => json_encode($filter),
                  ),
                );
                $context  = stream_context_create($options);
                @file_get_contents($url, false, $context);
            }

            $url = 'http://api.travelwebpartner.com/api/item.filter/';

            $options = array(
              'http' => array(
                'header'  => "Host: api.travelwebpartner.com\r\nConnection: close\r\nContent-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => json_encode($filter),
              ),
            );
            $context  = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);

            if(!$result)
                return new JsonResponse(['total' => 0, 'html' => $noItemsFoundHTML]);

            $result = json_decode($result);

            $field = $filter->orderField;//sort field
            $fieldOrder = $filter->orderDirection;//sort field

            if($fieldOrder == 'asc') $fieldOrder = 'desc';
            else $fieldOrder = 'asc';

            $table = "";
            if($body){
                $table = $loading;
                //$date = $this->get('translator')->trans('date', [], 'frontend');
                //$price = $this->get('translator')->trans('price', [], 'frontend');

                $table .= '<div><div class="row table-header sort-controls">';
                $table .= '<div class="col-xs-6">';
                $table .= '    <div class="btn-group" role="group" aria-label="...">
	                             <div class="btn btn-default ' . (($field=='date' && $fieldOrder=='desc')?"active":"") . '"><a href="#" data-field="date" data-order="asc"><span class="glyphicon glyphicon-arrow-down"></span></a></div>
	                             <div class="btn btn-default ' . (($field=='date' && $fieldOrder=='asc')?"active":"") . '"><a href="#" data-field="date" data-order="desc"><span class="glyphicon glyphicon-arrow-up"></span></a></div>
	                           </div>
                           </div>
                           <div class="col-xs-6">';
	            $table .= '    <div class="btn-group btn-group-right" role="group" aria-label="...">
	                              <div class="btn btn-default ' . (($field=='price' && $fieldOrder=='desc')?"active":"") . '"><a href="#" data-field="price" data-order="asc"><span class="glyphicon glyphicon-arrow-down"></span></a></div>
	                              <div class="btn btn-default ' . (($field=='price' && $fieldOrder=='asc')?"active":"") . '"><a href="#" data-field="price" data-order="desc"><span class="glyphicon glyphicon-arrow-up"></span></a></div>
	                            </div>
                           </div>';
                $table .= '</div>';

            }

            if(!$result)
                return new JsonResponse(['total' => 0, 'html' => $noItemsFoundHTML]);

            $data = $result->items;

            foreach ($data as $item) {
                $table .= $this->itemToRow($item, $filter, $request);
            }

            $table .= '<script>$(".my-popover").popover();</script>';

            if($body)
                $table .= '</div>';

            if($body && $result->total > 2)//add load more button
                $table .= '<div class="loadDiv"><div class="loadDiv-line"></div><button id="loadMore" data-total="'. $result->total .'" onclick="loadMore(this)"><span class="fa fa-angle-double-down"></span></button></div>';
            if($body && $result->total == 0){
                $table = $noItemsFoundHTML;
            }


            return new JsonResponse(['total' => $result->total, 'html' => $table]);
        }
        return new JsonResponse(['total' => 0, 'html' => $noItemsFoundHTML]);
    }

    private function itemToRow($item, $filter, Request $request){
        $days_short = $this->get('translator')->trans('days.short', [], 'frontend');

        if(!$item->info) $item->info = "";
        if(!$item->duration) $item->duration = "";
        $jokerHotel = $this->get('translator')->trans('joker.hotel', [], 'frontend');
        $jokerHotelDescription = $this->get('translator')->trans('joker.hotel.description', [], 'frontend');
        $hotelDescription = $this->get('translator')->trans('hotel.description', [], 'frontend');

        $hotel = $jokerHotel;
        if($item->hotel != false){
            $hotel= $item->hotel->name;
        }else{
            //item type package and hotel not specified
            if($item->type->id == 1){
                $hotel = $this->get('translator')->trans('unspecified.hotel.title', [], 'frontend');
                $item->info = $this->get('translator')->trans('unspecified.hotel.description', [], 'frontend');
            }
        }

        if($hotel == $jokerHotel && ($item->duration == "" || $item->duration == "1")){
            $item->duration = 'One way';
        }

        if($hotel == 'Flyg och första hotellnatt på Phuket' || $hotel == 'Flyg och första hotellnatt i Ao Nang'){
            $hotel = 'Hotel with 1 night stay only';
        }

        $date = $item->dDate;
        $day = $item->dDate;
        $month = $item->dDate;
        $day = $this->getDate($day, $request->getLocale(), '%d');
        $month = $this->getDate($month, $request->getLocale(), '%b');

        if(date('Y', strtotime($date)) == date("Y")){
            $date = $this->getDate($date, $request->getLocale());
        }else{
            $date = date('d.m.Y', strtotime($date));
        }

        $class = '';

        $company = $item->company->name;

        if($company == 'SkyPicker') $class .= " skypicker-toggle";

        $company = "<div class='company company-" . Slugifier::slugify(strtolower($item->company->name)) . "' ></div>";

        $fullNodes = $this->get('nodehelper')->getFullNodesWithParam("p.companyId = :companyId", [':companyId' => $item->company->id ], 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage', $request->getLocale());
        if($fullNodes){
            /** @noinspection Symfony2PhpRouteMissingInspection */
            $url = $this->generateUrl("_slug", ['url' => $fullNodes[0]->getTranslation()->getFullSlug(), '_locale' => $request->getLocale()]);
            $company = "<a href='$url' >$company</a>";
        }

        $lastCol = $company;//"<a href='" . $item->link . "'>" . $company . "</a>";

        if($item->company->name == 'SkyPicker'){
            $lastCol = "";
            for($i=0;$i<count($item->airline); $i++){
                $lastCol .= "<img src='/bundles/sandboxwebsite/img/airlines/".$item->airline[$i].".gif' title=".$item->airline[$i]." alt=".$item->airline[$i].">" ;
                break;
            }
        }

        $departure = $this->getTitle($item->departure, $request->getLocale(), false);
        if(strlen($item->departure->airportCode) < 4) $departure .= " <span class='airport-code text-muted'>" . $item->departure->airportCode . "</span>";

        $destination = $this->getTitle($item->destination, $request->getLocale(), false);
        if(strlen($item->destination->airportCode) < 4) $destination = " <span class='airport-code text-muted'>" . $item->destination->airportCode . "</span> " . $destination;

        $departureInverted = $this->getTitle($item->departure, $request->getLocale(), false);
        if(strlen($item->departure->airportCode) < 4) $departureInverted = " <span class='airport-code text-muted'>" . $item->departure->airportCode . "</span> " . $departureInverted;


        //TYPE 3,4 SECOND TAB
        if(in_array(4, $filter->type) || in_array(3, $filter->type)) {//flights only(skypicker)
            $aDate = "";
            if($item->aDate && $item->aDate != "-0001-11-30"){
                if($item->aDate != $item->dDate){
                    $aDate = $item->aTime . " (+1)";

                }else{
                    $aDate = $item->aTime;

                }

            }else{
                if(!$item->aDate && $item->duration > 0){
                    $aDate = date('d.m.Y', strtotime($item->dDate) + $item->duration * 24 * 60 * 60);
                    $aDate = $this->getDate($aDate, $request->getLocale());
                }
            }

            $dTime = "";
            $row = '';
            if($item->type->id == 3){
                $row .= '<div class="col-xs-10 col-sm-9 nopadding trip-field">

                            <table>
                                <tr>
                                    <td width="1%">
                                    <strong><span class="text-muted"> ' . $date . '</span></strong> &nbsp;&nbsp;';
                                    $row .= '<strong>' . $departure . " </strong>";
                                    if ($dTime){
                                    $row .= '&nbsp;<span class="text-muted"> ' . $dTime . '</span>'; 
                                    }
                                    $row .= '</td>
                                    <td>
                                        <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 100%;">
                                            <span class="trip-path-spacer-line">
                                                <div></div>
                                            </span>
                                            <span class="trip-path-spacer-arrow"></span>
                                        </div>
                                    </td>

                                    <td width="1%" class="nowrap">';

                if($item->company->name == 'SkyPicker'){

                    if(date('Y', strtotime($item->rdDate)) == date("Y")){
                        $rdate = $this->getDate($item->rdDate, $request->getLocale());
                    }else{
                        $rdate = date('d.m.Y', strtotime($item->rdDate));
                    }

                    if($rdate == '01.01.1970') $rdate = "";
                    //$rdate = "";
                    $time = $item->rdTime;
                    $row .= '<span> ' . $aDate . '</span>
                    <strong>'. $destination . "&nbsp;
                        <span class='text-muted'> " . $rdate . "</span>
                    </strong><span class='text-muted'> " . $time . "</span>";

                }else{
                    $row .= '<strong>'. $destination . "&nbsp; <span class='text-muted'> " . $aDate . "</span></strong>";
                }

                                    $row .= '</td>
                                    <td>
                                        <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 100%;">
                                            <span class="trip-path-spacer-line">
                                                <div></div>
                                            </span>
                                            <span class="trip-path-spacer-arrow"></span>
                                        </div>
                                    </td>
                                    <td width="1%">';

                if($item->company->name == 'SkyPicker'){
                    $time = '';//$item->raTime;
                    if($item->raDate && $item->raDate != $item->rdDate){
                        $time .= '(+1)';
                    }
                    $row .= '<span class="text-muted"> ' . $time . '</span>
                    <strong>'. $departure . "&nbsp;</strong>";

                }else{
                    $row .= '<strong>'. $departureInverted . "</strong> &nbsp;";
                }

                                    $row .= '</td>
                                </tr>
                            </table>

                        </div>';
            }else{
                $row .= '<div class="col-xs-10 col-sm-9 nopadding trip-field">

                            <table>
                                <tr>
                                    <td width="1%">
                                    <strong><span class="text-muted"> ' . $date . '</span></strong> &nbsp;&nbsp;';
                                    $row .= '<strong>' . $departure . " </strong>";
                                    if ($dTime){
									$row .= '&nbsp;<span class="text-muted"> ' . $dTime . '</span>'; 
                                    }
                                    $row .= '</td>
                                    <td>
                                        <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 100%;">
                                            <span class="trip-path-spacer-line">
                                                <div></div>
                                            </span>
                                            <span class="trip-path-spacer-arrow"></span>
                                        </div>
                                    </td>
                                    <td width="1%" class="nowrap">
                                    <span class="text-muted"> </span>&nbsp; <strong>'. $destination . "</strong>" .'
                                    </td>
                                </tr>
                            </table>

                        </div>';
            }

            $row .= '<div class="hidden-xs col-sm-2 trip-field text-center nopadding">'. $lastCol . '</div>';

            $row .= '<div class="col-xs-2 col-sm-1 price text-right trip-cost"><p>'. round($item->price).'€</p>
            <a target="_blank" href="'. $item->link .'?partner=' . $request->getHost() . ($this->getUser()?"&user=" . $this->getUser()->getId():"") . '" class="btn btn-info trip-btn-cost">'. round($item->price).'€</a>
            </div>';
        }else{
            //TYPE 1,2 FIRST TAB
            $row = '';


            if($item->hotel) {
                $url = 'http://www.booking.com/searchresults.et.html?lang=et&si=ai%2Cco%2Cci%2Cre%2Cdi&ss=';
                $url .= str_replace(" ", "+", $item->hotel->name);
                $url .= "+" . $item->destination->countryName;

                //stars
                $stars = '';
                for($i = 0; $i<floor($item->hotel->stars); $i++){
                    $stars .= "<span class='glyphicon glyphicon-star'></span>";
                }
                if($item->hotel->stars - floor($item->hotel->stars) > 0){
                    $stars .= "<span class='glyphicon glyphicon-plus'></span>";
                }
                $qwe = '<div class="trip-field nowrap">
                            <a href="#" onclick="return false;" class="my-popover" data-html="true" data-trigger="focus" data-toggle="popover"
                            title="' . $hotel . $stars . '"
                            data-content="' . ($hotel==$jokerHotel?$jokerHotelDescription:$hotelDescription) . ' ' . $item->info . ' ' . $item->seatsLeft . " <a href='" . $url . "' target='_blank'><img src='/bundles/sandboxwebsite/img/icons/booking-icon.png'>".'</a>" >
                            <span class=\'fa fa-suitcase\'></span> ';

                $hotelCol = $qwe;
                $hotelCol .= $stars;
                $hotelCol .= '</a></div>';
            }
            else {//joker hotel?
                $hotelCol = '<div class="trip-field">
                            <a href="#" onclick="return false;" class="my-popover" data-trigger="focus" data-toggle="popover" title="' . $hotel . '" data-content="' . ($hotel==$jokerHotel?$jokerHotelDescription:$hotelDescription) . ' '  . $item->info . '" >
                                <span class="fa fa-suitcase"></span>';
                $hotelCol .= '</a></div>';
            }

            $row .= $this->renderView('@SandboxWebsite/Travelbase/itemtorowsecondtab.html.twig',
                [
                    'days_short' => $days_short,
                    'lastCol' => $lastCol,
                    'item' => $item,
                    'date' => $date,
                    'day' => $day,
                    'month' => $month,
                    'departure' => $departure,
                    'destination' => $destination,
                    'hotelcol' => $hotelCol,
                ]);
        }

        //todo kosmos move html to template
        return $this->renderView('@SandboxWebsite/Travelbase/itemtorow.html.twig',
            [
                'html' => $row,
                'itemType' => $item->type->id,
                'class' => $class,
                'item' => $item,
            ]);
    }


    /**
     * @Route("/book-form/")
     * @Template()
     *
     * @param Request $request
     * @return array
     *
     */
    public function skypickerFormAction(Request $request)
    {
        //form submitted
        if($request->query->has('booking_form')){
            $form = $request->query->get('booking_form');

            $formData = new BookingForm();

            $bags = 0;
            if(!array_key_exists('passengers', $form) || count($form['passengers']) == 0){
                //error
                return new JsonResponse(['error' => 1, 'msg' => 'No passengers specified']);
            }else{
                foreach ($form['passengers'] as $passenger) {
                    if(!$passenger['first_name']){
                        return new JsonResponse(['error' => 1, 'msg' => 'Empty first name']);
                    }
                    if(!$passenger['last_name']){
                        return new JsonResponse(['error' => 1, 'msg' => 'Empty last name']);
                    }
                    if(!$passenger['sex']){
                        return new JsonResponse(['error' => 1, 'msg' => 'No sex specified']);
                    }
                    if(!$passenger['birth_day']){
                        return new JsonResponse(['error' => 1, 'msg' => 'Empty birthday']);
                    }
                    if(!$passenger['nationality']){
                        return new JsonResponse(['error' => 1, 'msg' => 'No nationality specified']);
                    }
                    if(!$passenger['bnum']){
                        return new JsonResponse(['error' => 1, 'msg' => 'Number of bags not specified']);
                    }

                    $p = new Passenger();
                    $p->setFirstName($passenger['first_name']);
                    $p->setLastName($passenger['last_name']);
                    $p->setSex($passenger['sex']);
                    $p->setBirthDay($passenger['birth_day']);
                    $p->setNationality($passenger['nationality']);
                    $p->setBNum($passenger['bnum']);
                    $bags += (int)$passenger['bnum'];
                    
                    $formData->addPassenger($p);
                }

            }

            if(!$request->query->get('price')){
                return new JsonResponse(['error' => 1, 'msg' => 'No price specified']);
            }
            if(!$request->query->get('flights')){
                return new JsonResponse(['error' => 1, 'msg' => 'No flights specified']);
            }
            if(!$form['email']){
                return new JsonResponse(['error' => 1, 'msg' => 'No email specified']);
            }
            if(!$form['phone']){
                return new JsonResponse(['error' => 1, 'msg' => 'No phone specified']);
            }
            if(!$form['cc_number']){
                return new JsonResponse(['error' => 1, 'msg' => 'No credit card number specified']);
            }
            if(!$form['cc_name']){
                return new JsonResponse(['error' => 1, 'msg' => 'No credit card name specified']);
            }
            if(!$form['cc_exp_month']){
                return new JsonResponse(['error' => 1, 'msg' => 'No credit card expiration month specified']);
            }
            if(!$form['cc_exp_year']){
                return new JsonResponse(['error' => 1, 'msg' => 'No credit card expiration year specified']);
            }
            if(!$form['cc_cvc']){
                return new JsonResponse(['error' => 1, 'msg' => 'No credit card cvc specified']);
            }

            $formData->setEmail($form['email']);
            $formData->setPhone($form['phone']);
            $formData->setCcNumber($form['cc_number']);
            $formData->setCcName($form['cc_name']);
            $formData->setCcExpMonth($form['cc_exp_month']);
            $formData->setCcExpYear($form['cc_exp_year']);
            $formData->setCcCVC($form['cc_cvc']);

            $passengers = [];

            foreach ($formData->getPassengers() as $passenger) {
                $passengers[] = [
                    "surname" => $passenger->getLastName(),
                    "name" => $passenger->getFirstName(),
                    "title" => $passenger->getSex()=="male"?"mr":"ms",
                    "birthday" => strtotime($passenger->getBirthDay()),
                    "nationality" => $passenger->getNationality(),
                    "insurance" => "none",
                    "checkin" => "REMOVED, DEPRECATED",
                    "issuer" => "REMOVED, DEPRECATED",
                    "cardno" => null,
                    "expiration" => null,
                    "email" => $formData->getEmail(),
                    "phone" => $formData->getPhone()
                ];
            }


            $posData = [
                "lang" => "en",
                "bags" => $bags,
                "passengers" => $passengers,
                "price" => $request->query->get('price'),
                "currency" => "eur",
                "flights" => explode("|", $request->query->get('flights')),
                "customerLoginID" => "unknown",
                "customerLoginName" => "unknown",
                "affily" => "picky",
                "locale" => "en"
                
            ];

            return new JsonResponse($posData);
            
//            $form = $this->createForm(new BookingFormType(), $formData);
//            $form->submit($form);
//            if($form->isValid()){
//                var_dump('valid');
//            }else{
//                foreach ($form->getErrors() as $error) {
//                    var_dump($error);
//                }
//            }
        }
        $form = $this->createForm(new BookingFormType());

        return ['form' => $form->createView()];
    }




    /**
     * @Route("/test/")
     * @param Request $request
     * @return Response
     * @Template()
     */
    public function testAction(Request $request)
    {
        set_time_limit(0);
        return [];
    }

    /**
     * @Route("/gibcb/{city}/{trLat}/{trLong}/{blLat}/{blLong}/{category}")
     * @param Request $request
     * @param $trLat
     * @param $trLong
     * @param $blLat
     * @param $blLong
     * @return JsonResponse
     */
    public function getItemsByCityBoundsAction(Request $request, $city, $trLat, $trLong, $blLat, $blLong, $category)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $mapCategory = $em->getRepository('SandboxWebsiteBundle:MapCategory')
            ->findOneBy(['name' => $category]);

        if(!$mapCategory){
            return new JsonResponse([]);
        }

        /** @var HotelPage[] $hotels */
        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong);

        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong, $mapCategory->getId());

        $companies = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
            ->getCompaniesByBounds($request->getLocale(), $trLat, $trLong, $blLat, $blLong, $mapCategory->getId());

        $data = [];

        foreach ($hotels as $hotel) {
            if(!$hotel->hasCoordinates()) continue;

            if($hotel->getCheapestPackage()){
                if($hotel->getCheapestPackage()->getMapCategory() && $hotel->getCheapestPackage()->getMapCategory()->getId() == $mapCategory->getId()){
                    $hotelData = [];
                    $hotelData['title'] = $hotel->getCheapestPackage()->getTitle();
                    $hotelData['lat'] = $hotel->getLatitude();
                    $hotelData['long'] = $hotel->getLongitude();
                    $hotelData['html'] = "<div class='map-window-item map-window-item-$category' style='background-image: url({$mapCategory->getImage()})'>" . (int)$hotel->getCheapestPackage()->getMinprice() . "</div>";
                    $data[] = $hotelData;
                }
            }else{
//                $hotelData = [];
//                $hotelData['title'] = $hotel->getTitle();
//                $hotelData['lat'] = $hotel->getLatitude();
//                $hotelData['long'] = $hotel->getLongitude();
//                $hotelData['html'] = "<div class='map-window-item'>" . $hotel->getTitle() . "</div>";
//                $data[] = $hotelData;
            }
        }

        foreach ($offers as $hotel) {
            if(!$hotel->hasCoordinates()) continue;

            $hotelData = [];
            $hotelData['title'] = $hotel->getTitle();
            $hotelData['lat'] = $hotel->getLatitude();
            $hotelData['long'] = $hotel->getLongitude();
            $hotelData['icon'] = 'http://google-maps-icons.googlecode.com/files/redblank.png';
            $hotelData['html'] = "<div class='map-window-item map-window-item-$category'  style='background-image: url({$mapCategory->getImage()})'>" . (int)$hotel->getPrice() . "</div>";

            $data[] = $hotelData;
        }

        foreach ($companies as $hotel) {
            if(!$hotel->hasCoordinates()) continue;

            $hotelData = [];
            $hotelData['title'] = $hotel->getTitle();
            $hotelData['lat'] = $hotel->getLatitude();
            $hotelData['long'] = $hotel->getLongitude();
            $hotelData['icon'] = 'http://google-maps-icons.googlecode.com/files/redblank.png';
            $hotelData['html'] = "<div class='map-window-item map-window-item-$category'  style='background-image: url({$mapCategory->getImage()})'>&nbsp;</div>";

            $data[] = $hotelData;
        }
        return new JsonResponse($data);
    }


    /**
     * @Route("/gibcb/{city}/{trLat}/{trLong}/{blLat}/{blLong}/{category}")
     * @param Request $request
     * @param $trLat
     * @param $trLong
     * @param $blLat
     * @param $blLong
     * @return JsonResponse
     */
    public function getItemsCountByCityBounds(Request $request, $city, $trLat, $trLong, $blLat, $blLong, $category)
    {
        $count = 0;

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $mapCategory = $em->getRepository('SandboxWebsiteBundle:MapCategory')
            ->findOneBy(['name' => $category]);

        if(!$mapCategory){
            return 0;
        }

        /** @var HotelPage[] $hotels */
        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong);

        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong, $mapCategory->getId());

        $companies = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
            ->getCompaniesByBounds($request->getLocale(), $trLat, $trLong, $blLat, $blLong, $mapCategory->getId());

        foreach ($hotels as $hotel) {
            if(!$hotel->hasCoordinates()) continue;

            if($hotel->getCheapestPackage()) {
                if ($hotel->getCheapestPackage()->getMapCategory() && $hotel->getCheapestPackage()->getMapCategory()->getId() == $mapCategory->getId()) {
                    $count++;
                }
            }
        }

        foreach ($offers as $hotel) {
            if(!$hotel->hasCoordinates()) continue;
            $count++;
        }

        foreach ($companies as $hotel) {
            if(!$hotel->hasCoordinates()) continue;
            $count++;
        }
        return $count;
    }

    /**
     * @Route("/ghbcb/{city}/{trLat}/{trLong}/{blLat}/{blLong}")
     * @param Request $request
     * @param $trLat
     * @param $trLong
     * @param $blLat
     * @param $blLong
     * @return JsonResponse
     */
    public function getHotelsByCityBoundsAction(Request $request, $city, $trLat, $trLong, $blLat, $blLong)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $mapCategory = $em->getRepository('SandboxWebsiteBundle:MapCategory')
            ->findOneBy(['name' => 'hotel']);

        if(!$mapCategory){
            return new JsonResponse([]);
        }

        /** @var HotelPage[] $hotels */
        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong, $mapCategory->getId());

        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong, $mapCategory->getId());

        $companies = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
            ->getCompaniesByBounds($request->getLocale(), $trLat, $trLong, $blLat, $blLong, $mapCategory->getId());

        /** @var OfferPage[] $offerHotels */
        $offerHotels = [];

        foreach ($offers as $offer) {
            foreach ($offer->getCategories() as $category) {
                if($category->getName() == 'hotel'){
                    $offerHotels[] = $offer;
                }
            }
        }


        $data = [];

        foreach ($hotels as $hotel) {
            //$city = $hotel->getCity() ? $hotel->getCity(): $hotel->getCityParish();
            //$data[$city] = ['city' => $city, 'html' => $this->mapHtml($city)];

            if(!$hotel->hasCoordinates()) continue;

            $hotelData = [];
            $hotelData['title'] = $hotel->getTitle();
            $hotelData['lat'] = $hotel->getLatitude();
            $hotelData['long'] = $hotel->getLongitude();
            $hotelData['html'] = "<div class='map-window-item'>" . $hotel->getTitle() . "</div>";


            $data[] = $hotelData;
        }

        foreach ($offerHotels as $hotel) {
            //$city = $hotel->getCity() ? $hotel->getCity(): $hotel->getCityParish();
            //$data[$city] = ['city' => $city, 'html' => $this->mapHtml($city)];

            if(!$hotel->hasCoordinates()) continue;

            $hotelData = [];
            $hotelData['title'] = $hotel->getTitle();
            $hotelData['lat'] = $hotel->getLatitude();
            $hotelData['long'] = $hotel->getLongitude();
            $hotelData['icon'] = 'http://google-maps-icons.googlecode.com/files/redblank.png';
            $hotelData['html'] = "<div class='map-window-item'>" . $hotel->getTitle() . "</div>";

            $data[] = $hotelData;
        }

        foreach ($companies as $hotel) {

            if(!$hotel->hasCoordinates()) continue;

            $hotelData = [];
            $hotelData['title'] = $hotel->getTitle();
            $hotelData['lat'] = $hotel->getLatitude();
            $hotelData['long'] = $hotel->getLongitude();
            $hotelData['icon'] = 'http://google-maps-icons.googlecode.com/files/redblank.png';
            $hotelData['html'] = "<div class='map-window-item'>" . $hotel->getTitle() . "</div>";

            $data[] = $hotelData;
        }
        //$data = array_values($data);
        return new JsonResponse($data);
    }


    public function getHotelsCountByCityBounds(Request $request, $city, $trLat, $trLong, $blLat, $blLong)
    {
        $count = 0;
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var HotelPage[] $hotels */
        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong);

        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong);

        /** @var OfferPage[] $offerHotels */
        $offerHotels = [];

        foreach ($offers as $offer) {
            foreach ($offer->getCategories() as $category) {
                if($category->getName() == 'hotel'){
                    $offerHotels[] = $offer;
                }
            }
        }

        foreach ($hotels as $hotel) {
            if(!$hotel->hasCoordinates()) continue;
            $count++;
        }

        foreach ($offerHotels as $hotel) {
            if(!$hotel->hasCoordinates()) continue;
            $count++;
        }

        return $count;
    }

    /**
     * @Route("/gabcb/{city}/{trLat}/{trLong}/{blLat}/{blLong}")
     * @param Request $request
     * @param $trLat
     * @param $trLong
     * @param $blLat
     * @param $blLong
     * @return JsonResponse
     */
    public function getActivitiesByCityBoundsAction(Request $request, $city, $trLat, $trLong, $blLat, $blLong)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong);

        /** @var OfferPage[] $realOffers */
        $realOffers = [];

        foreach ($offers as $offer) {
            $isHotel = false;
            foreach ($offer->getCategories() as $category) {
                if($category->getName() == 'hotel'){
                    $isHotel = true;
                }
            }

            if(!$isHotel){
                $realOffers[] = $offer;
            }
        }


        $data = [];

        foreach ($realOffers as $offer) {
            //$city = $hotel->getCity() ? $hotel->getCity(): $hotel->getCityParish();
            //$data[$city] = ['city' => $city, 'html' => $this->mapHtml($city)];

            if(!$offer->hasCoordinates()) continue;

            $offerData = [];
            $offerData['title'] = $offer->getTitle();
            $offerData['lat'] = $offer->getLatitude();
            $offerData['long'] = $offer->getLongitude();
            $offerData['html'] = "<div class='map-window-item'>" . $offer->getTitle() . " " . $offer->getPrice() . "</div>";

            $data[] = $offerData;
        }

        //$data = array_values($data);
        return new JsonResponse($data);
    }


    public function getActivitiesCountByCityBounds(Request $request, $city, $trLat, $trLong, $blLat, $blLong)
    {
        $count = 0;
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPagesByCityBounds($request->getLocale(), $city, $trLat, $trLong, $blLat, $blLong);

        /** @var OfferPage[] $realOffers */
        $realOffers = [];

        foreach ($offers as $offer) {
            $isHotel = false;
            foreach ($offer->getCategories() as $category) {
                if($category->getName() == 'hotel'){
                    $isHotel = true;
                }
            }

            if(!$isHotel){
                $realOffers[] = $offer;
            }
        }

        foreach ($realOffers as $offer) {
            if(!$offer->hasCoordinates()) continue;
            $count++;
        }

        return $count;
    }

    /**
     * @Route("/gbb/{trLat}/{trLong}/{blLat}/{blLong}/{mapZoom}")
     * @param Request $request
     * @param $trLat
     * @param $trLong
     * @param $blLat
     * @param $blLong
     * @return JsonResponse
     */
    public function getbyboundsAction(Request $request, $trLat, $trLong, $blLat, $blLong, $mapZoom)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPagesByBounds($request->getLocale(), $trLat, $trLong, $blLat, $blLong);

        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPagesByBounds($request->getLocale(), $trLat, $trLong, $blLat, $blLong);

        $data = [];

        foreach ($hotels as $hotel) {

            $city = $hotel->getCity() ? $hotel->getCity(): $hotel->getCityParish();

            if(!array_key_exists($city, $data)){
                $data[$city] = ['city' => $city, 'html' => $this->mapHtml($city, $trLat, $trLong, $blLat, $blLong, $request, $mapZoom)];
            }


//            $hotelData = [];
//            $hotelData['title'] = $hotel->getTitle();
//            $hotelData['lat'] = $hotel->getLatitude();
//            $hotelData['long'] = $hotel->getLongitude();
//
//            $data[] = $hotelData;
        }

        foreach ($offers as $offer) {
            if($offer->getCity() && !array_key_exists($offer->getCity(), $data)){
                $data[$offer->getCity()] = ['city' => $offer->getCity(), 'html' => $this->mapHtml($offer->getCity(), $trLat, $trLong, $blLat, $blLong, $request, $mapZoom)];
            }
        }

        $data = array_values($data);
        return new JsonResponse($data);
    }

    private function mapHtml($city, $trLat, $trLong, $blLat, $blLong, $request, $mapZoom)
    {
        $activities = $this->getItemsCountByCityBounds($request, $city, $trLat, $trLong, $blLat, $blLong, 'offer');
        $hotels = $this->getItemsCountByCityBounds($request, $city, $trLat, $trLong, $blLat, $blLong, 'hotel');

        if($mapZoom > 11){
            $content = $this->getSslPage('https://www.airbnb.com/search/search_results?location='.$city.'&price_max=85&search_by_map=true&zoom=11&sw_lat='.$blLat.'&sw_lng='.$blLong.'&ne_lat='.$trLat.'&ne_lng='.$trLong);
            var_dump($content);
        }else{
            $content = null;
        }

        if($mapZoom < 9){

            $badge = $activities > $hotels? $activities:$hotels;
            //only bigest badge
//            $lat = ($blLat + $trLat) / 2;
//            $long = ($blLong + $trLong) / 2;

            $html = "<div class='map-window-all-mini'><a href='#' onclick='return zoomMap(9, this)'>" . '<span class="badge">'.$badge.'</span>' . "</a></div>";

            return $html;
        }


        if($content){
            $data = json_decode($content);

            $results = $data->visible_results_count;
            //$price = CurrencyConverter::getPrice("USD", $data->average_price);

            $airbnb = '<a href="https://www.airbnb.com/s/' . $city . '" target="_blank">airbnb <span class="badge airbnb-badge hide">'.$results.'</span></a>';

        }else{
            $airbnb = '<a href="https://www.airbnb.com/s/' . $city . '" target="_blank">airbnb</a>';
        }


        $html = '<a href="#" data-city="' . $city . '" onclick="return loadHotelsByCity(this)">' . $city . '</a><br/>';
        if($hotels > 0)
            $html .= '<a href="#" data-city="' . $city . '" data-category="hotel" onclick="return loadItemsByCity(this)">hotel<span class="badge">'.$hotels.'</span></a><br/>';
        if($activities > 0)
            $html .= '<a href="#" data-city="' . $city . '" data-category="offer" onclick="return loadItemsByCity(this)">offers<span class="badge">'.$activities.'</span></a><br/>';
        $html .= $airbnb;

        $html = "<div class='map-window-all'>" . $html . "</div>";

        return $html;
    }

    function getSslPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @Route("/import/")
     * @Template()
     */
    public function importAction()
    {
        $execTime = ini_get('max_execution_time');
        ini_set('max_execution_time', 0);
        $url = 'http://api.travelwebpartner.com/api/item.getAll';
        $data = [];//array('key1' => 'value1', 'key2' => 'value2');

//        var $filter = {
//        hotel: [6],
//        departure: [6125],
//        company: [1,2],
//        date: {
//            start: "2014-10-01",
//            end: "2015-10-01"
//        },
//        duration: [1,7],
//        price: {
//            min: 0,
//            max: 500
//        },
//        type: [1,2,3,4],
//        destination_country: [1541, 4076, 405],
//        destination_city: [1541, 4076, 405],
//        limit: 20,
//        offset: 0,
//        orderField: 'id',
//        orderDirection: 'asc'
//    };

// use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        $data = json_decode($result);
        /**
         *   'FI' =>
                object(stdClass)[3888]
                public 'id' => int 6125
                public 'countryCode' => string 'FI' (length=2)
                public 'countryName' => string 'Finland' (length=7)
                public 'countryNameRu' => string '' (length=0)
                public 'cityCode' => string 'HEL' (length=3)
                public 'airportCode' => string 'HEL' (length=3)
                public 'airportNameEn' => string 'Vantaa' (length=6)
                public 'airportNameEt' => string 'Vantaa' (length=6)
                public 'airportNameFi' => string 'Vantaa' (length=6)
                public 'cityName' => string 'Helsinki' (length=8)
                public 'cityNameEt' => string 'Helsinki' (length=8)
                public 'cityNameFi' => string 'Helsinki' (length=8)
                public 'cityNameEn' => string 'Helsinki' (length=8)
                public 'cityNameRu' => string 'Ð¥ÐµÐ»ÑŒÑÐ¸Ð½ÐºÐ¸' (length=18)
         */
        $countries = $this->getCountries($data);
        $cities = $this->getCities($data);

        //create countries
        foreach ($countries as $country) {
            if($this->createCountry($country)){
                $this->get('session')->getFlashBag()->add('info', 'Country ' . $country->countryName . '(' . $country->countryCode . ') added.');
            }
        }

        foreach ($cities as $city) {
            if($this->createCity($city)) {
                $this->get('session')->getFlashBag()->add('info', 'City ' . $city->cityNameEn . '(' . $city->id . ') added.');
            }
        }

        //var_dump($countries);
        ini_set('max_execution_time', $execTime);
        return ['countries' => $countries, 'cities' =>$cities];
    }

    private function getCountries($items)
    {
        $countries = [];
        foreach ($items as $item) {
            if(!array_key_exists($item->departure->countryCode, $countries))
                $countries[$item->departure->countryCode] = $item->departure;
            if(!array_key_exists($item->destination->countryCode, $countries))
                $countries[$item->destination->countryCode] = $item->destination;
        }

        return $countries;
    }

    private function getCities($items)
    {
        $cities = [];
        foreach ($items as $item) {
            if(!array_key_exists($item->departure->id, $cities))
                $cities[$item->departure->id] = $item->departure;
            if(!array_key_exists($item->destination->id, $cities))
                $cities[$item->destination->id] = $item->destination;
        }

        return $cities;
    }

    private function createCountry($country){
        $em = $this->getDoctrine()->getManager();
        $parentNode = $em->getRepository('KunstmaanNodeBundle:Node')
            ->findOneBy(
                [
                    'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
                    'parent' => 1,
                    'deleted' => 0
                ]
            );

        return $this->createPlaceOverView($country, $parentNode, $country->countryName, true);
    }

    private function createCity($city)
    {
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();

        //get page by country code and country name(title)
        $placeOverviewPage = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->findOneBy(['countryCode' => $city->countryCode, 'title' => $city->countryName]);
        if(!$placeOverviewPage){
            $this->get('session')->getFlashBag()->add('info', 'PlaceOverviewPage not found');
            return false;
        }

        //get node version by page id
        $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')
            ->findOneBy(['refId' => $placeOverviewPage->getId(), 'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage']);

        if(!$nodeVersion) {
            $this->get('session')->getFlashBag()->add('info', 'NodeVersion not found');
            return false;
        }

        //get node
        $parentNode = $nodeVersion->getNodeTranslation()->getNode();

        return $this->createPlaceOverView($city, $parentNode, $city->cityNameEn, false);
    }

    /**
     * @param Node   $node       The node
     * @param string $permission The permission to check for
     *
     * @throws AccessDeniedException
     */
    private function checkPermission(Node $node, $permission)
    {
        /** @noinspection YamlDeprecatedClasses */
        /** @noinspection PhpDeprecationInspection */
        if (false === $this->get('security.context')->isGranted($permission, $node)) {
            throw new AccessDeniedException();
        }
    }


    private function createPlaceOverView($country, Node $parentNode, $title, $isCountry)
    {
        if(!$title) {//empty title
            $this->get('session')->getFlashBag()->add('info', 'Empty title');
            return false;
        }

        $locales = $this->container->getParameter('kuma_translator.managed_locales');
        $locales = array_diff($locales, ['en']);//remove en from locales and process it first as default lang

        $locale = 'en';

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('KunstmaanAdminBundle:User')->find(1);

        //check existence
        /** @var NodeTranslation[] $translations */
        $translations = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')
            ->findBy(
                [
                    'lang' => $locale,
                    'title' => $title
                ]
            );

        if($translations){//already in db
            //check if node deleted
            foreach ($translations as $translation) {
                if(!$translation->getNode()->isDeleted()){//node exists and not deleted
                    $langs = [];
                    /** @var NodeTranslation $trans */
                    foreach ($translation->getNode()->getNodeTranslations(true) as $trans) {
                        $langs[$trans->getLang()] = 1;
                    }

                    $langs = array_keys($langs);

                    $missingLanguages = array_diff($locales, $langs);

                    foreach ($missingLanguages as $lang) {
                        //create translation
                        $otherLanguageNodeTranslation = $translation;
                        $otherLanguageNodeNodeVersion = $otherLanguageNodeTranslation->getPublicNodeVersion();
                        $otherLanguagePage = $otherLanguageNodeNodeVersion->getRef($em);
                        //current hosts will be copied to new page with this command
                        $myLanguagePage = $this->get('kunstmaan_admin.clone.helper')->deepCloneAndSave($otherLanguagePage);
                        /* @var NodeTranslation $nodeTranslation */
                        $nodeTranslation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')->createNodeTranslationFor($myLanguagePage, $lang, $translation->getNode(), $user);

                        $titleNew = $this->getTitle($country, $lang, $isCountry);
                        if(!$titleNew) $titleNew = $title;

                        $nodeTranslation->setOnline(true);
                        $nodeTranslation->setTitle($titleNew);
                        $nodeTranslation->setSlug($titleNew);
                        $nodeTranslation->setUrl($nodeTranslation->getFullSlug());
                        $em->persist($nodeTranslation);
                    }

                    $em->flush();

                    return false;
                }
            }
        }

        // Check with Acl
        $this->checkPermission($parentNode, PermissionMap::PERMISSION_EDIT);

        $parentNodeTranslation = $parentNode->getNodeTranslation($locale, true);
        $parentNodeVersion = $parentNodeTranslation->getPublicNodeVersion();
        /** @var PlaceOverviewPage $parentPage */
        $parentPage = $parentNodeVersion->getRef($em);

        $placeOverviewPage = new PlaceOverviewPage();
        $placeOverviewPage->setTitle($title);
        $placeOverviewPage->setCityId($country->id);
        $placeOverviewPage->setCountryCode($country->countryCode);

        //copy hosts from parent
        foreach ($parentPage->getHosts() as $host) {
            $placeOverviewPage->addHost($host);
        }

        $placeOverviewPage->setParent($parentPage);

        $em->persist($placeOverviewPage);
        $em->flush();

        /* @var Node $nodeNewPage */
            $nodeNewPage = $em->getRepository('KunstmaanNodeBundle:Node')->createNodeFor($placeOverviewPage, $locale, $user);
            $nodeTranslation = $nodeNewPage->getNodeTranslation($locale, true);
            $nodeTranslation->setOnline(true);
            $em->persist($nodeTranslation);
        $em->flush();


        //create all translations
        foreach ($locales as $lang) {
            $otherLanguageNodeTranslation = $nodeNewPage->getNodeTranslation($locale, true);
            $otherLanguageNodeNodeVersion = $otherLanguageNodeTranslation->getPublicNodeVersion();
            $otherLanguagePage = $otherLanguageNodeNodeVersion->getRef($em);
            //hosts will be copied with this command
            $myLanguagePage = $this->get('kunstmaan_admin.clone.helper')->deepCloneAndSave($otherLanguagePage);
            /* @var NodeTranslation $nodeTranslation */
            $nodeTranslation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')->createNodeTranslationFor($myLanguagePage, $lang, $nodeNewPage, $user);

            $titleNew = $this->getTitle($country, $lang, $isCountry);
            if(!$titleNew) $titleNew = $title;

            $nodeTranslation->setOnline(true);
            $nodeTranslation->setTitle($titleNew);
            $nodeTranslation->setSlug($titleNew);
            $nodeTranslation->setUrl($nodeTranslation->getFullSlug());
            $em->persist($nodeTranslation);
        }

        $em->flush();

        /* @var MutableAclProviderInterface $aclProvider */
        $aclProvider = $this->container->get('security.acl.provider');
        /* @var ObjectIdentityRetrievalStrategyInterface $strategy */
        $strategy = $this->container->get('security.acl.object_identity_retrieval_strategy');
        $parentIdentity = $strategy->getObjectIdentity($parentNode);
        $parentAcl = $aclProvider->findAcl($parentIdentity);

        $newIdentity = $strategy->getObjectIdentity($nodeNewPage);
        $newAcl = $aclProvider->createAcl($newIdentity);

        $aces = $parentAcl->getObjectAces();
        /* @var EntryInterface $ace */
        foreach ($aces as $ace) {
            $securityIdentity = $ace->getSecurityIdentity();
            if ($securityIdentity instanceof RoleSecurityIdentity) {
                $newAcl->insertObjectAce($securityIdentity, $ace->getMask());
            }
        }
        $aclProvider->updateAcl($newAcl);

        return true;
    }


    private function getTitle($country, $lang, $isCountry)
    {
        if($isCountry) {//country title translation
            $title = $country->countryName;

            switch($lang){
                case 'fi': $title = $country->countryNameFi;
                    break;
                case 'en': $title = $country->countryName;
                    break;
                case 'de': $title = $country->countryNameDe;
                    break;
                case 'fr': $title = $country->countryNameFr;
                    break;
                case 'ru': $title = $country->countryNameRu;
                    break;
                case 'se': $title = $country->countryNameSe;
                    break;
                case 'ee': $title = $country->countryNameEe;
                    break;
            }

            if(!$title) $title = $country->countryName;
        }else{//city title translation
            $title = $country->cityName;

            switch($lang){
                case 'fi': $title = $country->cityNameFi;
                    break;
                case 'en': $title = $country->cityNameEn;
                    break;
                case 'de': $title = $country->cityNameEn;
                    break;
                case 'fr': $title = $country->cityNameEn;
                    break;
                case 'ru': $title = $country->cityNameRu;
                    break;
                case 'se': $title = $country->cityNameEn;
                    break;
                case 'ee': $title = $country->cityNameEt;
                    break;
            }
            if(!$title) $title = $country->cityNameEn;
        }

        return $title;
    }

    /**
     * @param $date
     * @param $locale
     * @return string
     */
    private function getDate($date, $locale, $format = '%d.%m. %a')
    {
        setlocale(LC_TIME, "");//reset locale

        if($locale == 'ee')
            setlocale(LC_TIME, 'et_EE', 'Estonian_Estonia', 'Estonian');
        elseif($locale == 'en')
            setlocale(LC_TIME, 'en', 'English_Australia', 'English');
        elseif($locale == 'fi')
            setlocale(LC_TIME, 'fi_FI', 'Finnish_Finland', 'Finnish');
        elseif($locale == 'fr')
            setlocale(LC_TIME, 'fr_FR', 'French', 'French_France');
        elseif($locale == 'de')
            setlocale(LC_TIME, 'de_DE', 'German', 'German_Germany');
        elseif($locale == 'se')
            setlocale(LC_TIME, 'sv_SE', 'Swedish_Sweden', 'Swedish');
        elseif($locale == 'ru')
            setlocale(LC_TIME, 'ru_RU', 'Russian_Russia', 'Russian');

        return strftime($format, strtotime($date));
    }
}
