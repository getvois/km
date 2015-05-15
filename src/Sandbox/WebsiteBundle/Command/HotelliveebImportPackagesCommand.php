<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\PagePartBundle\Helper\Services\PagePartCreatorService;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\HotelImage;
use Sandbox\WebsiteBundle\Entity\PackageCategory;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class HotelliveebImportPackagesCommand extends ContainerAwareCommand{
    protected function configure()
    {
        $this
            ->setName('travelbase:import:hotelliveeb:packages')
            ->setDescription('Import packages to hotelliveeb hotels')
        ;
    }

    private $company = [];
    /** @var  PagePartCreatorService */
    private $pagePartCreator;
    /** @var  EntityManager */
    private $em;

    private $emailBody;


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pagePartCreator = $this->getContainer()->get('kunstmaan_pageparts.pagepart_creator_service');

        /** @var EntityManager em */
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $nodes = $this->em->getRepository('KunstmaanNodeBundle:Node')
            ->findBy(['deleted' => 0, 'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Pages\HotelPage']);

        if(!$nodes) $nodes = [];

        $this->cacheCompany();

        foreach ($nodes as $node) {
            //$init = microtime();
            $this->addPackages($node);

            //var_dump('packages added to hotel in ' . (microtime() - $init));
        }

        if($this->emailBody)
        {
            $email = "HV Packcages added/updated:<br/>" . $this->emailBody;
            EmailInfoSend::sendEmail($email, 'twp: HV Packages info');
        }

    }
    private function addPackages(Node $node)
    {
        $hotelPage = $this->getPage($node);
        if(!$hotelPage) return;

        if(!$hotelPage->getHotelId()) return;

        //$init = microtime();
        $url = 'http://www.hotelliveeb.ee/xml.php?type=package&hotel=' . $hotelPage->getHotelId();
        $content = @file_get_contents($url);
        //var_dump('page downloaded in ' . (microtime() - $init));
        if(!$content){
            var_dump('couldnot load: '. $url);
            return;
        }

        $crawler = new Crawler($content);
        $packages = $crawler->filter('package');

        if($packages->count() == 0) return;

        //get avaliable packages
        $packagePages = $this->getHotelPackages($hotelPage);

        $packageIds = [];
        foreach ($packagePages as $page) {
            $packageIds[$page->getPackageId()] = $page->getPackageId();
        }

        for($i=0;$i<$packages->count();$i++){
            $package = $packages->eq($i);
            $packageId = $package->filter('id')->first()->text();

            //remove packages that are available
            if(array_key_exists($packageId, $packageIds)){
                unset($packageIds[$packageId]);
            }

            $this->createPageFromPackage($package, $hotelPage);
        }

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        //set packages to unpublished that are left in $packageIds
        foreach ($packageIds as $id) {
            foreach ($packagePages as $page) {
                if($page->getId() == $id){
                    //unpublish page
                    $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($page);
                    if($node){
                        /** @var NodeTranslation[] $translations */
                        $translations = $node->getNodeTranslations();
                        if($translations){
                            foreach ($translations as $translation) {
                                $translation->setOnline(false);
                                $em->persist($translation);
                            }
                            $em->flush();
                        }
                    }
                    break;
                }
            }

        }
    }

    private function cacheCompany()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        //set company to hotelliveeb
        /** @var CompanyOverviewPage $companyPage */
        $companyPage = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
            ->findOneBy(['title' => 'hotelliveeb']);

        if($companyPage) {
            $node = $em->getRepository('KunstmaanNodeBundle:Node')
                ->getNodeFor($companyPage);

            if($node){
                /** @var NodeTranslation $tr */
                foreach ($node->getNodeTranslations(true) as $tr) {
                    $companyPage = $tr->getRef($em);
                    if($companyPage) {
                        $this->company[$tr->getLang()] = $companyPage;
                    }
                }
            }
        }
    }

    private function setPackageCompany(Node $node)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var NodeTranslation[] $translations */
        $translations = $node->getNodeTranslations(true);

        foreach ($translations as $translation) {
            $lang = $translation->getLang();
            if(array_key_exists($lang, $this->company)){
                $packagePage = $translation->getRef($em);
                if($packagePage) {
                    $packagePage->setCompany($this->company[$lang]);
                    $em->persist($packagePage);
                }
            }
        }
        $em->flush();

    }

    private function createPageFromPackage(Crawler $package, HotelPage $hotelPage)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $packageId = $package->filter('id')->first()->text();
        $packagePage = $this->packageExists($packageId);
        if($packagePage){
            $this->updatePackageFields($package);
//            $this->setPackagePageFields($package, $packagePage);
//            $em->persist($packagePage);
//            $em->flush();

            //probably company will never change
            //$node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($packagePage);
            //$this->setPackageCompany($node);
            return;
        }

        $packagePage = new PackagePage();
        $packagePage->setTitle($package->filter('title')->first()->text());

        var_dump($packagePage->getTitle());

        $node = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($hotelPage);

        if(!$node){
            var_dump('Node for page not found');
            return;
        }

        $this->setPackagePageFields($package, $packagePage);

        $init = time();
        $newNode = $this->createPackageTranslations($node , $packagePage, $package);
        var_dump('createPackageTranslations in ' . (time() - $init));

        $this->emailBody  .= "NEW node: ". $newNode->getId(). " title:". $packagePage->getTitle() . "<br/>";

        $this->setPackageCompany($newNode);

        $init = time();
        $this->createPackagePageParts($package, $newNode);
        var_dump('createPackagePageParts in ' . (time() - $init));

    }


    private function createPackagePageParts(Crawler $package, Node $node)
    {
        $langs = ['ee', 'en', 'ru', 'fi', 'se'];
        $rooms = $package->filter("room");
        //add page parts to all languages
        foreach ($langs as $lang) {

            $init = time();

            //add rooms and information
            // Add pageparts

            $pageparts = array();
            //add gallery

            if($rooms->count() > 0){
                for($k=0;$k<$rooms->count();$k++){
                    $room = $rooms->eq($k);

                    $params = [];

                    $roomID = $room->filter('id');
                    if($roomID->count() > 0){
                        $params['setRoomId'] = $roomID->first()->text();
                    }
                    $roomName = $room->filter('name');
                    if($roomName->count() > 0){
                        $params['setName'] = $roomName->first()->text();
                    }
                    $roomImage = $room->filter('image');
                    if($roomImage->count() > 0){
                        $params['setImage'] = $roomImage->first()->text();
                    }
                    $roomContent = $room->filter('content');
                    if($roomContent->count() > 0){
                        $params['setContent'] = $roomContent->first()->text();
                    }

                    $pageparts['rooms'][] = $this->pagePartCreator
                        ->getCreatorArgumentsForPagePartAndProperties('Sandbox\WebsiteBundle\Entity\PageParts\RoomPagePart',
                            $params
                        );
                }
            }

            //add info
            $info = $package->filter('information item');

            if($info->count() > 0){

                for($k=0;$k<$info->count();$k++){
                    $name = $info->eq($k)->filter('name');
                    $description = $info->eq($k)->filter('description');
                    $image = $info->eq($k)->filter('image');

                    $realName = '';
                    if($name->count() > 0){$realName = $name->first()->text();}
                    $realDescription = '';
                    if($description->count() > 0){$realDescription = $description->first()->text();}

                    $imgArr = [];
                    if($image->count() > 0){
                        $url = $image->first()->text();
                        if($url){
                            $image = new HotelImage();
                            $image->setImageUrl($url);
                            $imgArr[] = $image;
                            $this->em->persist($image);
                            $this->em->flush();
                        }
                    }

                    $pageparts['information'][] = $this->pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Sandbox\WebsiteBundle\Entity\PageParts\HotelInformationPagePart',
                        array(
                            'setImages' => $imgArr,
                            'setName' => $realName,
                            'setDescription' => $realDescription,
                        )
                    );
                }
            }

            //$init = time(); //todo kosmos minimim 2sec per lang = 5langs * 2 = min 10 sec
            $this->pagePartCreator->addPagePartsToPage($node, $pageparts, $lang);
            //var_dump('pagepart created in ' . $lang . " " . (time() - $init));
        }
    }
    /**
     * @param $node
     * @param PackagePage $packagePage
     * @param Crawler $package
     * @return Node
     */
    private function createPackageTranslations($node, PackagePage $packagePage, Crawler $package)
    {
        $langs = ['ee', 'en', 'ru', 'fi', 'se'];
        $translations = [];
        foreach ($langs as $lang) {
            $translations[] = array('language' => $lang, 'callback' => function($page, $translation, $seo) {
                /** @var $page PackagePage */
                /** @var $translation NodeTranslation */
                $translation->setTitle($page->getTitle());
                $translation->setWeight(20);
            });
        }

        //add categories
        $categories = $package->filter('category');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($packagePage->getCategories() as $category) {
            $packagePage->removeCategory($category);
            $em->remove($category);
        }

        $em->flush();


        $added = [];
        if($categories->count() > 0){
            for($j=0; $j<$categories->count(); $j++){
                $category = $categories->eq($j)->text();

                //var_dump($category);
                $c = $em->getRepository('SandboxWebsiteBundle:PackageCategory')
                    ->findOneBy(['name' => $category]);

                if(!$c){
                    //add new
                    $c = new PackageCategory();
                    $c->setName($category);
                    $em->persist($c);
                    $em->flush();
                }

                if(!array_key_exists($c->getId(), $added)){
                    $added[$c->getId()] = $c->getId();
                    $packagePage->addCategory($c);
                }

            }
        }

        $options = array(
            'parent' => $node,
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => 'Admin'
        );

        /** @var Node $node */
        $node = $this->getContainer()->get('kunstmaan_node.page_creator_service')->createPage($packagePage, $translations, $options);

        //bind package to place from hotel
        foreach ($langs as $lang) {
            $translation = $node->getNodeTranslation($lang, true);
            if(!$translation) continue;

            /** @var PackagePage $page */
            $page = $translation->getRef($em);
            if(!$page) continue;

            $hotelTrans = $node->getParent()->getNodeTranslation($lang, true);
            if($hotelTrans){
                $hotelPage = $hotelTrans->getRef($em);
                if($hotelPage){
                    foreach ($hotelPage->getPlaces() as $place) {
                        $page->addPlace($place);
                        $em->persist($page);
                    }
                    $em->flush();
                }
            }
        }

        return $node;

    }

    private function setPackagePageFields(Crawler $package, PackagePage $packagePage)
    {
        $packageId = $package->filter('id');
        if ($packageId->count() > 0) {
            $packagePage->setPackageId($packageId->first()->text());
        }
        $number_adults = $package->filter('number_adults');
        if ($number_adults->count() > 0) {
            $packagePage->setNumberAdults($number_adults->first()->text());
        }
        $number_children = $package->filter('number_children');
        if ($number_children->count() > 0) {
            $packagePage->setNumberChildren($number_children->first()->text());
        }
        $duration = $package->filter('duration');
        if ($duration->count() > 0) {
            $packagePage->setDuration($duration->first()->text());
        }
        $description = $package->filter('description');
        if ($description->count() > 0) {
            $packagePage->setDescription($description->first()->text());
        }
        $checkin = $package->filter('checkin');
        if ($checkin->count() > 0) {
            $packagePage->setCheckin($checkin->first()->text());
        }
        $checkout = $package->filter('checkout');
        if ($checkout->count() > 0) {
            $packagePage->setCheckout($checkout->first()->text());
        }
        $minprice = $package->filter('minprice');
        if ($minprice->count() > 0) {
            $packagePage->setMinprice($minprice->first()->text());
        }
        $image = $package->filter('image');
        if ($image->count() > 0) {
            $packagePage->setImage($image->first()->text());
        }

        $packagePage->setOriginalLanguage('ee');

        //set payment methods
        $payment = $package->filter('paymentmethod');
        if ($payment->count() > 0) {
            for($i=0;$i<$payment->count();$i++){
                if($payment->eq($i)->text() == 'bank'){
                    $packagePage->setBankPayment(true);
                }
                if($payment->eq($i)->text() == 'creditcard'){
                    $packagePage->setCreditcardPayment(true);
                }
                if($payment->eq($i)->text() == 'onthespot'){
                    $packagePage->setOnthespotPayment(true);
                }
            }
        }
    }
    /**
     * @param $packageId
     * @return null|PackagePage
     */
    private function packageExists($packageId)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var PackagePage[] $pages */
        $pages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->findBy(['packageId' => $packageId]);
        if($pages){
            foreach ($pages as $page) {
                /** @var Node $node */
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($page);

                //if node exists and not deleted skip
                if($node && !$node->isDeleted()) {
                    return $page;
                }
            }
        }

        return null;
    }
    /**
     * @param Node $node
     * @return null|HotelPage
     */
    private function getPage(Node $node)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $page = null;
        $translation = $node->getNodeTranslation('ee', true);
        if($translation){
            $page = $translation->getRef($em);
        }

        return $page;
    }


    /**
     * @param $hotelPage
     * @return array|\Sandbox\WebsiteBundle\Entity\Pages\PackagePage[]
     */
    private function getHotelPackages($hotelPage)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $hotelNode = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($hotelPage);

        if(!$hotelNode) return [];

        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\PackagePage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";
        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\PackagePage\'
AND nt.online = 1 AND n.parent = ' . $hotelNode->getId();


        /** @var PackagePage[] $pages */
        $pages = $em->createQuery($dql)
            ->getResult();

        if(!$pages) return [];

        return $pages;
    }

    /**
     * Update existing packages if any data changed
     * @param Crawler $package
     * @return bool
     */
    private function updatePackageFields(Crawler $package)
    {

        $emailAdd = '';
        $packageId = $package->filter('id');
        //if no id return
        if ($packageId->count() <= 0) {
            return false;
        }
        $packageId = $packageId->first()->text();

        $packagePage = $this->em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagePage('ee', $packageId);
        if(!$packagePage) return false;

        $needUpdate = false;

        $qb = $this->em->createQueryBuilder()
            ->update('SandboxWebsiteBundle:Pages\PackagePage', 'p');

        $number_adults = $package->filter('number_adults');
        if ($number_adults->count() > 0) {
            if($packagePage->getNumberAdults() != $number_adults->first()->text()){
                $emailAdd .= 'number_adults changed from ' . $packagePage->getNumberAdults() . ' to ' . $number_adults->first()->text() . " <br/>";
                $needUpdate = true;
                $qb->set('p.numberAdults', $number_adults->first()->text());
            }
        }
        $title = $package->filter('title');
        if ($title->count() > 0) {
            if($packagePage->getTitle() != $title->first()->text()){
                $emailAdd .= 'Title changed from ' . $packagePage->getTitle() . ' to ' . $title->first()->text() . " <br/>";
                $needUpdate = true;
                $qb->set('p.title', $qb->expr()->literal($title->first()->text()));
                //just inform about update
                //$qb->set('p.titleTranslated', $qb->expr()->literal(''));
            }
        }
        $number_children = $package->filter('number_children');
        if ($number_children->count() > 0) {
            if($packagePage->getNumberChildren() != $number_children->first()->text()){
                $emailAdd .= 'number_children changed from ' . $packagePage->getNumberChildren() . ' to ' . $number_children->first()->text() . " <br/>";
                $needUpdate = true;
                $qb->set('p.numberChildren', $number_children->first()->text());
            }
        }
        $duration = $package->filter('duration');
        if ($duration->count() > 0) {
            if($packagePage->getDuration() != $duration->first()->text()) {
                $emailAdd .= 'duration changed from ' . $packagePage->getDuration() . ' to ' . $duration->first()->text() . " <br/>";
                $needUpdate = true;
                $qb->set('p.duration', $duration->first()->text());
            }
        }
        $description = $package->filter('description');
        if ($description->count() > 0) {
            if($packagePage->getDescription() != $description->first()->text()){
                $emailAdd .= 'description changed from ' . $packagePage->getDescription() . ' to ' . $description->first()->text() . " <br/>";
                $needUpdate = true;
                $qb->set('p.description', $qb->expr()->literal($description->first()->text()));
            }
        }
        $checkin = $package->filter('checkin');
        if ($checkin->count() > 0) {
            if($packagePage->getCheckin() != $checkin->first()->text()){
                $emailAdd .= 'checkin changed from ' . $packagePage->getCheckin() . ' to ' . $checkin->first()->text() . " <br/>";
                $needUpdate = true;
                $qb->set('p.checkin', $qb->expr()->literal($checkin->first()->text()));
            }
        }
        $checkout = $package->filter('checkout');
        if ($checkout->count() > 0) {
            if($packagePage->getCheckout() != $checkout->first()->text()){
                $emailAdd .= 'checkout changed from ' . $packagePage->getCheckout() . ' to ' . $checkout->first()->text() . " <br/>";
                $needUpdate = true;
                $qb->set('p.checkout', $qb->expr()->literal($checkout->first()->text()));
            }
        }
        $minprice = $package->filter('minprice');
        if ($minprice->count() > 0) {
            if($packagePage->getMinprice() != $minprice->first()->text()){
                $emailAdd .= 'minprice changed from ' . $packagePage->getMinprice() . ' to ' . $minprice->first()->text() . " <br/>";
                $needUpdate = true;
                $qb->set('p.minprice', $minprice->first()->text());
            }
        }
        $image = $package->filter('image');
        if ($image->count() > 0) {
            if($packagePage->getImage() != $image->first()->text()){
                $emailAdd .= 'image changed from ' . $packagePage->getImage() . ' to ' . $image->first()->text() . " <br/>";
                $needUpdate = true;
                $qb->set('p.image', $qb->expr()->literal($image->first()->text()));
            }
        }

        //$qb->set('p.originalLanguage', 'ee');

        //set payment methods
        $payment = $package->filter('paymentmethod');
        if ($payment->count() > 0) {
            for($i=0;$i<$payment->count();$i++){
                if($payment->eq($i)->text() == 'bank'){
                    if($packagePage->getBankPayment() != true){
                        $emailAdd .= 'bank changed from false to true <br/>';
                        $needUpdate = true;
                        $qb->set('p.bankPayment', true);
                    }
                }
                if($payment->eq($i)->text() == 'creditcard'){
                    if($packagePage->getCreditcardPayment() != true){
                        $emailAdd .= 'creditcard changed from false to true <br/>';
                        $needUpdate = true;
                        $qb->set('p.creditcardPayment', true);
                    }
                }
                if($payment->eq($i)->text() == 'onthespot'){
                    if($packagePage->getOnthespotPayment() != true){
                        $emailAdd .= 'onthespot changed from false to true <br/>';
                        $needUpdate = true;
                        $qb->set('p.onthespotPayment', true);
                    }
                }
            }
        }


        if($needUpdate){
            $qb->where('p.packageId = ' . $packageId);
            $qb->getQuery()->execute();

            //if package updated add it to email
            $node = $this->em->getRepository('KunstmaanNodeBundle:Node')
                ->getNodeFor($packagePage);
            $this->emailBody  .= "UPDATED node: ". $node->getId(). " title:". $packagePage->getTitle() . "<br/>";
            $this->emailBody .= $emailAdd;
        }

        return $needUpdate;

    }
}