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
     * @Route("/api-filter/")
     * @param Request $request
     */
    public function filterAction(Request $request)
    {
        if($request->getContent()){
            $filter = json_decode($request->getContent());

            $url = 'http://api.travel.markmedia.fi/api/item.filter/';

            $options = array(
              'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => json_encode($filter),
              ),
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            $result = json_decode($result);

            $field = $filter->orderField;//sort field


            $table = '<table><tr>' .
              '<th><a href="#" data-field="date" '. (($field == 'date')?'class="active"':"") . '>Date</a></th>' .
              //'<th><a href="#" data-field="company">Company</a></th>' .
              '<th><a href="#" data-field="departure" '. (($field == 'departure')?'class="active"':"") . '>From</a></th>' .
              '<th><a href="#" data-field="destination" '. (($field == 'destination')?'class="active"':"") . '>To</a></th>' .
              '<th><a href="#" data-field="hotel" '. (($field == 'hotel')?'class="active"':"") . '>Info</a></th>' .
              //'<th><a href="#" data-field="info">Info</a></th>' .
              '<th><a href="#" data-field="duration" '. (($field == 'duration')?'class="active"':"") . '>Duration</a></th>' .
              '<th><a href="#" data-field="price" '. (($field == 'price')?'class="active"':"") . '>Price</a></th>' .
              '<th><a href="#" data-field="company" '. (($field == 'company')?'class="active"':"") . '>Link</a></th></tr>';

            $data = $result->items;

            foreach ($data as $item) {

            }

            $table .= '<script>$(".my-popover").popover();</script><script></script>';
            $table .= '</table>';


            if($result->total > count($data))//add load more button
                $table .= '<button id="loadMore" onclick="loadMore()"><span class="fa fa-angle-double-down"></span></button>';


            if($result->total == 0){
                $table = "<div>No items found</div>";
            }


            return new Response($table);
        }
        return "hello";
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
        $url = 'http://api.travel.markmedia.fi/api/item.getAll';
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
