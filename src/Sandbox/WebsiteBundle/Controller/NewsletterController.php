<?php

namespace Sandbox\WebsiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $mailbox = "{imap.gmail.com:993/imap/ssl/novalidate-cert}";
        $mbx = imap_open($mailbox , $user , $password);
        $ck = imap_check($mbx);
        $mails = imap_fetch_overview($mbx,"1:5");
        return $this->render('');
    }
}