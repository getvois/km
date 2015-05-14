<?php

namespace Sandbox\WebsiteBundle\Command;


class EmailInfoSend {
    public static function sendEmail($emailBody, $subject)
    {
        $to = "info@markmedia.fi";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: report@kuumatmatkat.fi';
        mail($to,$subject,$emailBody,$headers);
    }
}