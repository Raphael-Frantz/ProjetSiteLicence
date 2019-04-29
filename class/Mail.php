<?php
// *****************************************************************************************************
// * Class used to send mail
// *****************************************************************************************************
class Mail {

    /**
     * Send an email to a receiver.
     * @param receiver the email address of the receiver
     * @param subject the email subject
     * @param data the email data
     **/
    static function send($receiver, $subject, $data) {
        $header = "MIME-Version: 1.0\r\n"; 
        $header .= "Content-Type: text/html; charset=UTF-8\r\n"; 
        $header .= "From: ".SITE_MAIL."\r\n"; 
        $header .= "X-Sender: <".SITE_MAIL."\r\n"; 
        $header .= "X-Mailer: PHP\n"; 
        $header .= "X-auth-smtp-user: ".SITE_MAIL."\r\n"; 
        $header .= "X-abuse-contact: ".SITE_MAIL."\r\n"; 
        $header .= "Reply-to: ".SITE_MAIL."\r\n";
        
        return @mail($receiver, mb_encode_mimeheader(utf8_decode($subject),"UTF-8"), $data, $header);
    }    

} // class Mail