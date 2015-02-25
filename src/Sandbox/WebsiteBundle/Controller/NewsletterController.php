<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\TranslatorBundle\Model\Translation;
use Kunstmaan\UtilitiesBundle\Helper\Slugifier;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\Company\CompanyPage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class NewsletterController extends Controller
{
    /**
     * @Route("/newsletter/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        //$pagePartCreator = $this->container->get('kunstmaan_pageparts.pagepart_creator_service');
        $user = 'mika.mendesee@gmail.com';
        $password = 'qwerty121284';
        $mailbox = "{imap.gmail.com:993/imap/ssl}INBOX";
        $inbox = imap_open($mailbox , $user , $password)  or die('Cannot connect to Gmail: ' . imap_last_error());

        $emails = imap_search($inbox,'UNSEEN');

        $output = '';

        $i = 0;
        foreach($emails as $mail) {
            $headerInfo = imap_headerinfo($inbox,$mail);

            if($headerInfo->fromaddress == 'Lux Express <luxexpress@luxexpress.eu>') continue;
            if($headerInfo->fromaddress == 'Estonian Air <noreply@estonian-air.ee>') continue;

            $elements = imap_mime_header_decode($headerInfo->subject);
            $subject = '';
            foreach ($elements as $element) {
                $subject .= utf8_encode($element->text);
            }
            //$subject = utf8_encode($elements[0]->text);//iconv_mime_decode($elements[0]->text,0,"UTF-8");
            $output .= "Subject: ". $subject .'<br/>';
            //$output .= "To: ".$headerInfo->toaddress.'<br/>';
            //$output .= "Date: ".$headerInfo->date.'<br/>';
            $output .= 'From: "'.$headerInfo->fromaddress.'"<br/>';
            //$output .= "To: ".$headerInfo->reply_toaddress.'<br/>';
            $emailStructure = imap_fetchstructure($inbox,$mail);
            $body = '';
            if($emailStructure->type === 1){//multipart
                foreach ($emailStructure->parts as $key => $part) {
                    if($part->subtype == 'HTML'){
                        $body = imap_qprint(imap_fetchbody($inbox, $mail, $key + 1));//FT_PEEK

                        $crawler = new Crawler($body);
                        $body = $crawler->html();

                        $styles = $crawler->filter("style");
                        for($j = 0 ; $j< $styles->count(); $j++){
                            $body = str_replace($styles->eq($j)->html(), '', $body);
                        }

                        //hotelli veb
                        $delete = $crawler->filter('.newsletter_hidden');//->first();
                            if($delete->count() > 0){
                                $delete = $delete->first()->html();
                                //$delete = str_replace('ä', '&auml;', $delete);
                                $body = str_replace($delete, '', $body);

                                $tds = $crawler->filter("td");
                                for($j = 2; $j<$tds->count(); $j++){//body in td
                                    if(preg_match('/Ei soovi rohkem kirju saada?/', $tds->eq($j)->text())){
                                        $delete = $tds->eq($j)->html();
                                        $body = str_replace($delete, '', $body);
                                    }
                                }
                            }

                        //tallink
                        $paragraphs = $crawler->filter('p, td, div');

                        $patterns = [
                            '/Kui soovid uudiskirja/',
                            '/Kui (Sa|Te) ei (soovi|näe)/',
                            '/uudiskirjast loobuda/',
                            '/ei näe (pilte|uudiskirja)/',
                            '/This email was sent to/',
                            '/Eemalda e-mail nimekirjast/'
                            //'/Ei soovi rohkem kirju saada?/',
                        ];
                        foreach ($patterns as $pattern) {
                            for($j = 1; $j<$paragraphs->count(); $j++){
                                if(preg_match($pattern, $paragraphs->eq($j)->text())
                                ){
                                    $delete = $paragraphs->eq($j)->html();
                                    if(strlen($delete) < 1300) {
                                        $body = str_replace($delete, '', $body);
                                    }
                                }
                            }
                        }




                        //estravel
                        $delete = $crawler->filter('.adminText');//->first();
                        if($delete->count() > 0){
                            //$body = $crawler->html();
                            $delete = $delete->first()->html();
                            $body = str_replace($delete, '', $body);
                        }


                        $output .= $body;
                        break;
                    }
                }

                $this->makePage($headerInfo, $subject, $body);


            }
            echo $output;
            $output = '';

            if($i >= 0) {
                break;
            }

            $i++;
        }

        // colse the connection
        imap_expunge($inbox);
        imap_close($inbox);

        return new Response("");
    }

    /**
     * @param $newsPage
     * @param $headerInfo
     */
    private function setCompany(NewsPage $newsPage, $headerInfo)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        //company name rules
        $company = preg_replace("/<[A-Za-z0-9_.]+@[A-Za-z0-9._]+>/", "", $headerInfo->fromaddress);
        $company = trim($company, " \t\n\r\0\x0B.\"");
        $company = explode(".", $company)[0];
        $companyPage = null;
        if (count(explode(" ", $company)) > 2) {
            $parts = explode(" ", $company);
            $company = $parts[0] . " " . $parts[1];
            $companyPage = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
                ->findOneBy(['title' => $company]);
            if (!$companyPage) {
                $company = $parts[1] . " " . $parts[2];
                $companyPage = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
                    ->findOneBy(['title' => $company]);
            }
        }

        if (!$companyPage) {
            $companyPage = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
                ->findOneBy(['title' => $company]);
        }
        if ($companyPage) {
            $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($companyPage);
            if ($node) {
                $translation = $node->getNodeTranslation('ee', true);
                if ($translation) {
                    /** @noinspection PhpParamsInspection */
                    $newsPage->addCompany($translation->getRef($em));
                }
            }
        }
    }

    /**
     * @param $newsPage
     * @param $body
     * @return Crawler
     */
    private function setPlace(NewsPage $newsPage, $body)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        //city rules
        /** @var PlaceOverviewPage[] $places */
        $places = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->findActiveOverviewPages();

        $crawler = new Crawler($body);
        $html = $crawler->html();
        /** @var Node[] $add */
        $add = [];
        foreach ($places as $place) {
            if (preg_match("/" . str_replace("/", " ", $place->getTitle()) . "/", $html)) {
                $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                if($node){
                    $add[$node->getId()] = $node;
                }
            }
        }

        foreach ($add as $node) {
            $translation = $node->getNodeTranslation('ee', true);
            if ($translation) {
                $place = $translation->getRef($em);
                if ($place) {
                    $newsPage->addPlace($place);
                }
            }
        }
    }

    /**
     * @param $headerInfo
     * @param $subject
     * @param $body
     */
    private function makePage($headerInfo, $subject, $body)
    {
        $pageCreator = $this->container->get('kunstmaan_node.page_creator_service');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //save to news
        $newsPage = new NewsPage();
        $newsPage->setTitle($subject);
        $newsPage->setPageTitle($subject);
        $newsPage->setHtml($body);
        $newsPage->setPriceFromLabel('newsletter');
        $newsPage->setDate(new \DateTime($headerInfo->date));

        $this->setHosts($newsPage);
        $this->setCompany($newsPage, $headerInfo);
        $this->setPlace($newsPage, $body);

        if($newsPage->getCompanies()->count() > 0){
            $month = $this->getMonth($headerInfo->date, 'ee');
            /** @var CompanyOverviewPage $company */
            $company = $newsPage->getCompanies()->first();
            $name = $company->getTitle();


            $summary = 'Hilisemad uudised firmalt '.$name.' - '.$month.'i pakkumised ja soodustused ning '.$name.'i praegused kehtivad sooduskampaaniad leiad siit.';
            //$summary = 'Latest newsletter from '.$name.'. Check out '.$name.' '.$month.' offers and discounts here.';
            $newsPage->setSummary($summary);
        }


        $translations = array();
        /** @noinspection PhpUnusedParameterInspection */
        $translations[] = array('language' => 'ee', 'callback' => function ($page, $translation, $seo) {
            /** @var NodeTranslation $translation */
            /** @var HasNodeInterface $page */
            $translation->setTitle($page->getTitle());
            $translation->setSlug(Slugifier::slugify($page->getTitle()));
        });

        $newsParent = $em->getRepository('KunstmaanNodeBundle:Node')
            ->findOneBy([
                'refEntityName' => "Sandbox\\WebsiteBundle\\Entity\\News\\NewsOverviewPage",
                'deleted' => 0,
                'hiddenFromNav' => 0
            ]);

        $options = array(
            'parent' => $newsParent,
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => 'Admin'
        );
        $pageCreator->createPage($newsPage, $translations, $options);


//                $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($newsPage);
//
//                $pageparts = array();
//                $pageparts['main'][] = $pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Kunstmaan\PagePartBundle\Entity\TextPagePart',
//                    array(
//                        'setContent' => $body
//                    )
//                );

        //$pagePartCreator->addPagePartsToPage($node, $pageparts, 'ee');
    }

    /**
     * @param $date
     * @param $locale
     * @return string
     */
    private function getMonth($date, $locale)
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

        return strftime('%B', strtotime($date));
    }

    /**
     * @param $newsPage
     */
    private function setHosts(NewsPage $newsPage)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var Host[] $hosts */
        $hosts = $em->getRepository('SandboxWebsiteBundle:Host')->findAll();
        foreach ($hosts as $host) {
            if (preg_match('/.ee/', $host->getName())) {
                $newsPage->addHost($host);
            }
        }
    }
}

/* ALL - return all messages matching the rest of the criteria
  ANSWERED - match messages with the \\ANSWERED flag set
  BCC "string" - match messages with "string" in the Bcc: field
  BEFORE "date" - match messages with Date: before "date"
  BODY "string" - match messages with "string" in the body of the message
  CC "string" - match messages with "string" in the Cc: field
  DELETED - match deleted messages
  FLAGGED - match messages with the \\FLAGGED (sometimes referred to as Important or Urgent) flag set
  FROM "string" - match messages with "string" in the From: field
  KEYWORD "string" - match messages with "string" as a keyword
  NEW - match new messages
  OLD - match old messages
  ON "date" - match messages with Date: matching "date"
  RECENT - match messages with the \\RECENT flag set
  SEEN - match messages that have been read (the \\SEEN flag is set)
  SINCE "date" - match messages with Date: after "date"
  SUBJECT "string" - match messages with "string" in the Subject:
  TEXT "string" - match messages with text "string"
  TO "string" - match messages with "string" in the To:
  UNANSWERED - match messages that have not been answered
  UNDELETED - match messages that are not deleted
  UNFLAGGED - match messages that are not flagged
  UNKEYWORD "string" - match messages that do not have the keyword "string"
  UNSEEN - match messages which have not been read yet*/
