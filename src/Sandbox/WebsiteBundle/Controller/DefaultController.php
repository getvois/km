<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\TranslatorBundle\Entity\Translation;
use Sandbox\WebsiteBundle\Entity\Form\BookingForm;
use Sandbox\WebsiteBundle\Entity\Form\Passenger;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Form\Booking\BookingFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function filterAction(Request $request, $body)
    {
        if($request->getContent()){
            $filter = json_decode($request->getContent());

            if($body == 1 && in_array(4, $filter->type)){
                //import from skypicker first
                $url = 'http://80.69.162.118/api/skypicker.import/';
                $options = array(
                  'http' => array(
                    'header'  => "Host: api.travelwebpartner.com\r\nConnection: close\r\nContent-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => json_encode($filter),
                  ),
                );
                $context  = stream_context_create($options);
                file_get_contents($url, false, $context);
            }


            $url = 'http://80.69.162.118/api/item.filter/';

            $options = array(
              'http' => array(
                'header'  => "Host: api.travelwebpartner.com\r\nConnection: close\r\nContent-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => json_encode($filter),
              ),
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            $result = json_decode($result);

            $field = $filter->orderField;//sort field
            $fieldOrder = $filter->orderDirection;//sort field

            if($fieldOrder == 'asc') $fieldOrder = 'desc';
            else $fieldOrder = 'asc';

            $loading = "<span class='loading'></span>";

            $table = "";
            if($body){
                $table = $loading;
                $date = $this->get('translator')->trans('date', [], 'frontend');
                $price = $this->get('translator')->trans('price', [], 'frontend');
                if(in_array(4, $filter->type)){//flights only(skypicker)
                    $table .= '<div>
                            <div class="row table-header">
                                <div class="col-xs-1"><a href="#" data-order="'. (($field == 'date')?$fieldOrder:"asc") . '" data-field="date" '. (($field == 'date')?'class="active"':"") . '>' . $date .'</a></div>
                                <div class="col-xs-1"></div>
                                <div class="col-xs-2"><a href="#" data-order="'. (($field == 'departure')?$fieldOrder:"asc") . '" data-field="departure" '. (($field == 'departure')?'class="active"':"") . '></a></div>
                                <div class="col-xs-3"> </div>
                                <div class="col-xs-2"><a href="#" data-order="'. (($field == 'destination')?$fieldOrder:"asc") . '" data-field="destination" '. (($field == 'destination')?'class="active"':"") . '></a></div>
                                <div class="col-xs-2"><a href="#" data-order="'. (($field == 'company')?$fieldOrder:"asc") . '" data-field="company" '. (($field == 'company')?'class="active"':"") . '></a></div>
                                <div class="col-xs-1"><a href="#" data-order="'. (($field == 'price')?$fieldOrder:"asc") . '" data-field="price" '. (($field == 'price')?'class="active"':"") . '>' . $price .'</a></div>
                            </div>

                            ';

//                    $table .= '<table><tr>' .
//                        '<th><a href="#" data-field="date" '. (($field == 'date')?'class="active"':"") . '>Date</a></th>' .
//                        '<th>Dur./Stops</th>' .
//                        '<th><a href="#" data-field="departure" '. (($field == 'departure')?'class="active"':"") . '>From</a></th>' .
//                        '<th></th>' .
//                        '<th><a href="#" data-field="destination" '. (($field == 'destination')?'class="active"':"") . '>To</a></th>' .
//                        '<th><a href="#" data-field="price" '. (($field == 'price')?'class="active"':"") . '>Price</a></th>';

                }else{
                    $duration = $this->get('translator')->trans('duration', [], 'frontend');
                    $company = $this->get('translator')->trans('company', [], 'frontend');

                    $table .= '<div>
                            <div class="row table-header">
                                <div class="col-xs-1"><a href="#" data-order="'. (($field == 'date')?$fieldOrder:"asc") . '" data-field="date" '. (($field == 'date')?'class="active"':"") . '>' . $date .'</a></div>
                                <div class="col-xs-2"><a href="#" data-order="'. (($field == 'departure')?$fieldOrder:"asc") . '" data-field="departure" '. (($field == 'departure')?'class="active"':"") . '></a></div>
                                <div class="col-xs-2"></div>
                                <div class="col-xs-2"><a href="#" data-order="'. (($field == 'destination')?$fieldOrder:"asc") . '" data-field="destination" '. (($field == 'destination')?'class="active"':"") . '></a></div>
                                <div class="col-xs-1"><a href="#" data-order="'. (($field == 'hotel')?$fieldOrder:"asc") . '" data-field="hotel" '. (($field == 'hotel')?'class="active"':"") . '></a></div>
                                <div class="col-xs-1"><a href="#" data-order="'. (($field == 'duration')?$fieldOrder:"asc") . '" data-field="duration" '. (($field == 'duration')?'class="active"':"") . '>' . $duration .'</a></div>
                                <div class="col-xs-2"><a href="#" data-order="'. (($field == 'company')?$fieldOrder:"asc") . '" data-field="company" '. (($field == 'company')?'class="active"':"") . '>' . $company .'</a></div>
                                <div class="col-xs-1"><a href="#" data-order="'. (($field == 'price')?$fieldOrder:"asc") . '" data-field="price" '. (($field == 'price')?'class="active"':"") . '>' . $price .'</a></div>

                            </div>

                            ';

//                    $table .= '<table><tr>' .
//                        '<th><a href="#" data-field="date" '. (($field == 'date')?'class="active"':"") . '>Date</a></th>' .
//                        //'<th><a href="#" data-field="company">Company</a></th>' .
//                        '<th><a href="#" data-field="departure" '. (($field == 'departure')?'class="active"':"") . '>From</a></th>' .
//                        '<th><a href="#" data-field="destination" '. (($field == 'destination')?'class="active"':"") . '>To</a></th>' .
//                        '<th><a href="#" data-field="hotel" '. (($field == 'hotel')?'class="active"':"") . '>Info</a></th>' .
//                        //'<th><a href="#" data-field="info">Info</a></th>' .
//                        '<th><a href="#" data-field="duration" '. (($field == 'duration')?'class="active"':"") . '>Duration</a></th>' .
//                        '<th><a href="#" data-field="price" '. (($field == 'price')?'class="active"':"") . '>Price</a></th>' .
//                        '<th><a href="#" data-field="company" '. (($field == 'company')?'class="active"':"") . '>Link</a></th></tr>';
                }


            }

            if(!$result)
                return new JsonResponse(['total' => 0, 'html' => "<div>No items found</div>"]);

            $data = $result->items;

            foreach ($data as $item) {
                $table .= $this->itemToRow($item, $filter);
            }

            $table .= '<script>$(".my-popover").popover();</script>';

            if($body)
                $table .= '</div>';

            if($body && $result->total > count($data))//add load more button
                $table .= '<button id="loadMore" onclick="loadMore()"><span class="fa fa-angle-double-down"></span></button>';


            if($body && $result->total == 0){
                $table = "<div>No items found</div>";
            }


            return new JsonResponse(['total' => $result->total, 'html' => $table]);
        }
        return new JsonResponse(['total' => 0, 'html' => "<div>No items found</div>"]);
    }


    private $prevDate = "";
    private $dateCount = 0;
    private function itemToRow($item, $filter){

        if(!$item->info) $item->info = "";
        if(!$item->duration) $item->duration = "";
        $hotel = "Joker hotel";
        if($item->hotel != false) $hotel= $item->hotel->name;

        if($hotel == "Joker hotel" && ($item->duration == "" || $item->duration == "1")){
            $item->duration = 'One way';
        }

        if($hotel == 'Flyg och första hotellnatt på Phuket' || $hotel == 'Flyg och första hotellnatt i Ao Nang'){
            $hotel = 'Hotel with 1 night stay only';
        }

        $date = substr($item->dDate, 8,2). "." .substr($item->dDate, 5,2). "." . substr($item->dDate, 2,2);

        $class = '';
        if($this->prevDate == '')
            $this->prevDate = $date;
        $this->dateCount++;
        if($this->prevDate != $date) {
            $prevDate = $date;
            if($this->dateCount > 5)
                $class = 'day-sep';
            $this->dateCount = 0;
        }

        $company = $item->company->name;

        if($company == 'SkyPicker') $class .= " skypicker-toggle";

        //if(UrlExists("/sites/all/modules/travelbase/img/" + $item.company.name.toLowerCase() + ".png"))
            $company = "<img src='/sites/all/modules/travelbase/img/" . strtolower($item->company->name) . ".png' alt='" . $item->company->name . "' title='".$item->company->name."' />";


        $lastCol = $company;//"<a href='" . $item->link . "'>" . $company . "</a>";

        if($item->company->name == 'SkyPicker'){
            $lastCol = "";
            for($i=0;$i<count($item->airline); $i++){
                $lastCol .= "<img src='/bundles/sandboxwebsite/img/airlines/".$item->airline[$i].".gif' title=".$item->airline[$i]." alt=".$item->airline[$i].">" ;
            break;
            if($i < count($item->airline) - 1) $lastCol .= " ";
        }
    }

        if(in_array(4, $filter->type)) {//flights only(skypicker)
            $aDate = "";
            if($item->aDate && $item->aDate != "-0001-11-30"){
                if($item->aDate != $item->dDate){
                    $aDate = $item->aTime . " (".date('d.m.', strtotime($item->aDate)).")";

                }else{
                    $aDate = $item->aTime;

                }

            }else{
                if(!$item->aDate && $item->duration > 0){
                    $aDate = date('d.m.', strtotime($item->dDate) + $item->duration * 24 * 60 * 60);
                }
            }

            $dTime = "";
            if($item->dTime != "00:00"){
                $dTime = $item->dTime;
            }

            $stops = "";
            if($stops > 0){
                $stops = $item->stops . " stops";
            }
            //flights only(skypicker)

            $duration = $item->flyDuration;
            if($item->type->id == 4)
            {
                //$duration = "Direct";
            }

            $row = '<div class=" trip row '.$class.'" data-itemid="'. $item->id .'" data-from="'.$item->departure->airportCode.'" data-to="'.$item->destination->airportCode.'">
                        <div class="col-xs-1 trip-duration">'.$date.'</div>';

            //$row .= '<div class="col-xs-1 trip-duration nowrap"><strong>'. $duration . "<br/>" . $stops .'</strong></div>';

            if($item->type->id == 3){
                $row .= '<div class="col-xs-8 trip-field">

                            <table>
                                <tr>
                                    <td width="1%">
                                    <strong>'. $item->departure->cityNameFi . "</strong> <span class='text-muted'> " . $dTime . "</span>" .'
                                    </td>
                                    <td>
                                        <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 90%;">
                                            <span class="trip-path-spacer-line">
                                                <div></div>
                                            </span>
                                            <span class="trip-path-spacer-arrow"></span>
                                        </div>
                                    </td>
                                    <td width="2%" class="nowrap">
                                    <strong>'. $item->destination->cityNameFi . "</strong>" .'
                                    </td>
                                    <td>
                                        <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 90%;">
                                            <span class="trip-path-spacer-line">
                                                <div></div>
                                            </span>
                                            <span class="trip-path-spacer-arrow"></span>
                                        </div>
                                    </td>
                                    <td width="1%">
                                    <strong>'. $item->departure->cityNameFi . "</strong> <span class='text-muted'> " . $aDate . "</span>" .'
                                    </td>
                                </tr>
                            </table>

                        </div>';
            }else{
                $row .= '<div class="col-xs-8 trip-field">

                            <table>
                                <tr>
                                    <td width="1%">
                                    <strong>'. $item->departure->cityNameFi . "</strong> <span class='text-muted'> " . $dTime . "</span>" .'
                                    </td>
                                    <td>
                                        <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 90%;">
                                            <span class="trip-path-spacer-line">
                                                <div></div>
                                            </span>
                                            <span class="trip-path-spacer-arrow"></span>
                                        </div>
                                    </td>
                                    <td width="2%" class="nowrap">
                                    <strong>'. $item->destination->cityNameFi . "</strong><span class='text-muted'> " . $aDate . "</span>" .'
                                    </td>
                                </tr>
                            </table>

                        </div>';
            }



            $row .= '
                        <div class="col-xs-2 trip-field">'. $item->company->name . '</div>
                        <div class="col-xs-1 price text-right trip-cost"><p>' . round($item->price) . '€</p> <a href="'.$item->link.'" class="btn btn-info trip-btn-cost">' . round($item->price) . '€</a></div>

                    </div>';

//            $row = "<tr class='" . $class . "' data-itemid='". $item->id ."' data-from='".$item->departure->airportCode."' data-to='".$item->destination->airportCode."' >" .
//                "<td>" . $date . "</td>" .
//                "<td><strong>" . $item->flyDuration . "<br/>" . $stops . "</strong></td>" .
//                "<td><strong>" . $item->departure->cityNameFi . "</strong> <span class='text-muted'> " . $dTime . "</span></td>" .
//                "<td style='width:100%'>
//
//                <div class='trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init' style='width: 100%;'>
//                                        <span class='trip-path-spacer-line'>
//                                            <div></div>
//                                        </span>
//                                        <span class='trip-path-spacer-arrow'></span>
//                                    </div>
//
//                </td>" .
//                "<td><strong>" . $item->destination->cityNameFi . "</strong><span class='text-muted'> " . $aDate . "</span></td>" .
//                "<td class='price'>" . round($item->price) . "</td>" .
//                "</tr>";
        }else{

            $row = '<div class="row trip '.$class.'" data-itemid="'. $item->id .'">
                        <div class="col-xs-1 trip-duration">'.$date.'</div>

                        <div class="col-xs-6 trip-field">

                            <table>
                                <tr>
                                    <td width="1%">
                                    <strong>'. $item->departure->cityNameFi .'</strong>
                                    </td>
                                    <td style="widht: 100%">
                                        <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 90%;">
                                            <span class="trip-path-spacer-line">
                                                <div></div>
                                            </span>
                                            <span class="trip-path-spacer-arrow"></span>
                                        </div>
                                    </td>
                                    <td width="2%" class="nowrap">
                                    <strong>'. $item->destination->cityNameFi .'</strong>
                                    </td>
                                    <td style="widht: 100%">
                                        <div class="trip-path-spacer-arrow-wrapper trip-path-spacer-arrow-wrapper-init" style="width: 90%;">
                                            <span class="trip-path-spacer-line">
                                                <div></div>
                                            </span>
                                            <span class="trip-path-spacer-arrow"></span>
                                        </div>
                                    </td>
                                    <td width="1%">
                                    <strong>'. $item->departure->cityNameFi .'</strong>
                                    </td>
                                </tr>
                            </table>

                        </div>

                        <div class="col-xs-1 trip-field"><a href="#" onclick="return false;" class="my-popover" data-toggle="popover" title="'.$hotel.'" data-content="'.$item->info.'" ><span class="glyphicon glyphicon-home"></span></a></div>
                        <div class="col-xs-1 trip-field">'. $item->duration .' days</div>
                        <div class="col-xs-2 trip-field">'.$lastCol.'</div>
                        <div class="col-xs-1 price text-right trip-cost"><p>' . round($item->price) . '€</p> <a href="'.$item->link.'" class="btn btn-info trip-btn-cost">' . round($item->price) . '€</a></div>
                    </div>';

//            $row = "<tr class='" . $class . "' data-itemid='". $item->id ."' >" .
//                "<td>" . $date . "</td>" .
//                //"<td>" + $company + "</td>" +
//                "<td>" . $item->departure->cityNameFi . "</td>" .
//                "<td>" . $item->destination->cityNameFi . "</td>" .
//                "<td><a href='#' onclick='return false;' class='my-popover' data-toggle='popover' title='".$hotel."' data-content='".$item->info."' >" . $hotel . "</a></td>" .
//                //"<td>" + $item.info + "</td>" +
//                "<td>" . $item->duration . "</td>" .
//                "<td>" . round($item->price) . "</td>" .
//                "<td>".$lastCol."</td>" .
//                "</tr>";
        }



        return $row;
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
     *
     */
    public function testAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $tags = $em->getRepository('KunstmaanTaggingBundle:Tag')
            ->findAll();

        foreach ($tags as $tag) {
            $translation = $em->getRepository('KunstmaanTranslatorBundle:Translation')
                ->findOneBy(['domain' => 'tag', 'keyword' => $tag->getName()]);

            if(!$translation){
                $translationId = $em->getRepository('KunstmaanTranslatorBundle:Translation')->getUniqueTranslationId();

                foreach (explode('|', 'fi|en|de|fr|ru|se|ee') as $lang) {
                    $t = new Translation();
                    $t->setLocale($lang);
                    $t->setDomain('tag');
                    $t->setCreatedAt(new \DateTime());
                    $t->setFlag(Translation::FLAG_NEW);
                    $t->setTranslationId($translationId);
                    $t->setKeyword($tag->getName());
                    $t->setText($tag->getName());
                    $em->persist($t);

                }
                $em->flush();
                printf("<div>translated: %s</div>", $tag->getName());
            }
        }



//        var_dump(date('H:i', 1418807117));
//        $diff = time() - 1418807117;
//        var_dump($diff / 60 );
        //if($diff/60 < 60)

        return new Response("");
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
}
