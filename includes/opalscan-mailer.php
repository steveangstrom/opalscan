<?php
function opalreportmail() {
    // The $_REQUEST contains all the data sent via ajax
    if ( isset($_REQUEST) ) {
        $mailaction = $_REQUEST['mailaction'];
        if ( $mailaction == 'sendreport' ) {
          $attachments = array(plugin_dir_url( __DIR__  ) . 'reports/scanlog.txt');
           $headers = 'From: My Name <pheriche@pheriche.com>' . "\r\n";
           $message = 'this is the test message that I am testing the testy plugin of';
           wp_mail('steve@pheriche.com', 'subject', $message, $headers, $attachments);
        }
      #
      echo 'we just tried to send a mail, this is step one , teh url = '.plugin_dir_url( __DIR__  ) . 'reports/scanlog.txt';
    }
   die();
}
add_action( 'wp_ajax_opalreportmail', 'opalreportmail' );
