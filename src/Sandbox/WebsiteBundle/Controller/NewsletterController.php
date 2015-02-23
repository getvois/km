<?php

namespace Sandbox\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $user = 'mika.mendesee@gmail.com';
        $password = 'qwerty121284';
        $mailbox = "{imap.gmail.com:993/imap/ssl}INBOX";
        $inbox = imap_open($mailbox , $user , $password)  or die('Cannot connect to Gmail: ' . imap_last_error());

        $emails = imap_search($inbox,'UNSEEN');

        $output = '';

        $i = 0;
        foreach($emails as $mail) {
            $headerInfo = imap_headerinfo($inbox,$mail);

            if($headerInfo->fromaddress == 'Lux Express') continue;

            $output .= "Subject: ".$headerInfo->subject.'<br/>';
            //$output .= "To: ".$headerInfo->toaddress.'<br/>';
            $output .= "Date: ".$headerInfo->date.'<br/>';
            $output .= "From: ".$headerInfo->fromaddress.'<br/>';
            //$output .= "To: ".$headerInfo->reply_toaddress.'<br/>';
            $emailStructure = imap_fetchstructure($inbox,$mail);

            if($emailStructure->type === 1){//multipart
                foreach ($emailStructure->parts as $key => $part) {
                    if($part->subtype == 'HTML'){
                        $body = imap_qprint(imap_fetchbody($inbox, $mail, $key + 1, FT_PEEK));

                        $crawler = new Crawler($body);
                        $body = $crawler->html();
                        //hoteli veb
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
                            '/uudiskirjast loobuda/'
                            //'/Ei soovi rohkem kirju saada?/',
                        ];
                        foreach ($patterns as $pattern) {
                            for($j = 1; $j<$paragraphs->count(); $j++){
                                if(preg_match($pattern, $paragraphs->eq($j)->text())
                                ){
                                    $delete = $paragraphs->eq($j)->html();
                                    $body = str_replace($delete, '', $body);
                                }
                            }
                        }




                        //estravel
                        $delete = $crawler->filter('.adminText');//->first();
                        if($delete->count() > 0){
                            $body = $crawler->html();
                            $delete = $delete->first()->html();
                            $body = str_replace($delete, '', $body);
                        }


                        $output .= '<br/><br/><br/><br/>body' . $body;
                        break;
                    }
                }
            }
            //var_dump($emailStructure);
//            if(!isset($emailStructure->parts)) {
//                $output .= "Body: ".imap_body($inbox, $mail, FT_PEEK);
//            } else {
//                $output .= imap_qprint("Body: ".imap_body($inbox, $mail, FT_PEEK));
//            }
            echo $output;
            $output = '';

            if($i >= 6) {
                break;
            }

            $i++;
        }

        // colse the connection
        imap_expunge($inbox);
        imap_close($inbox);

        return new Response("");
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
