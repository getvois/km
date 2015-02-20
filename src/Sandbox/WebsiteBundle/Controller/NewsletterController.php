<?php

namespace Sandbox\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        foreach($emails as $mail) {
            $headerInfo = imap_headerinfo($inbox,$mail);
            $output .= "Subject: ".$headerInfo->subject.'<br/>';
            //$output .= "To: ".$headerInfo->toaddress.'<br/>';
            $output .= "Date: ".$headerInfo->date.'<br/>';
            $output .= "From: ".$headerInfo->fromaddress.'<br/>';
            //$output .= "To: ".$headerInfo->reply_toaddress.'<br/>';
            $emailStructure = imap_fetchstructure($inbox,$mail);
            if(!isset($emailStructure->parts)) {
                $output .= "Body: ".imap_body($inbox, $mail, FT_PEEK);
            } else {
                //
            }
            echo $output;
            $output = '';
        }

        // colse the connection
        imap_expunge($inbox);
        imap_close($inbox);

        return new Response("");
    }
}