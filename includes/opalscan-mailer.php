<?php
function opalreportmail() {
    if ( isset($_REQUEST) ) {
        $mailaction = $_REQUEST['mailaction'];
        if ( $mailaction == 'sendmail' ) {
          $attachments = array(plugin_dir_url( __DIR__  ) . 'reports/scanlog.txt');
           $headers = 'From: My Name <pheriche@pheriche.com>' . "\r\n";
           $message = 'this is the test message that I am testing the testy plugin of';
           $mailout = wp_mail('steve@pheriche.com', 'subject', $message, $headers, $attachments);
          echo 'we just tried to send a mail, this is step one ,' .$mailout;
        }
      #

    //  echo 'we just tried to send a mail, this is step one , the url = '.plugin_dir_url( __DIR__  ) . 'reports/scanlog.txt';
    }
   die();
}
add_action( 'wp_ajax_opalreportmail', 'opalreportmail' );
