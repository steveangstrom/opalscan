<?php
namespace opalscan;
function opalreportmail() {
    if ( isset($_REQUEST) ) {

      if ( ! check_ajax_referer( 'opalscan-security-nonce', 'security' ) ) {
         wp_send_json_error( 'Invalid security token sent.' );
         wp_die();
       }

        $mailaction = $_REQUEST['mailaction'];
        if ( $mailaction == 'sendmail' ) {
          $attfile = plugin_dir_path( __DIR__  ) . 'reports/opalscan.log';
          $attachments = array($attfile);
           $headers = 'From: My Name <pheriche@pheriche.com>' . "\r\n";
           $message = 'this is the test message that I am testing the testy plugin of '.$attfile;
           $mailout = wp_mail('steve@pheriche.com', 'subject', $message, $headers, $attachments);
          echo 'we just tried to send a mail,'. $attfile.', this did it send ' .$mailout;
        }
      #

    //  echo 'we just tried to send a mail, this is step one , the url = '.plugin_dir_url( __DIR__  ) . 'reports/opalscan.log';
    }
   die();
}
add_action( 'wp_ajax_opalreportmail', 'opalscan\opalreportmail' );
