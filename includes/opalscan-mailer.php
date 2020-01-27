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
          $randomised_filename = get_option( 'opalsupport_log_location' );
          $attfile = plugin_dir_path( __DIR__  ) ."reports/opalscan-$randomised_filename.log";
          $html_log_file = plugin_dir_path( __DIR__  ) . "reports/opal-scanner-report-$randomised_filename.html";

          $attachments = array($attfile, $html_log_file);
          $h_css= 'style="font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size: 24px;"';
          $p_css= 'style="font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size: 16px;"';
          $subject = 'You scanned your site '.get_bloginfo('name');
          $headers = array('Content-Type: text/html; charset=UTF-8','From: My Name <pheriche@pheriche.com>' );
          // $headers = 'From: My Name <pheriche@pheriche.com>' . "\r\n";
          $message ='';

          $message .="<h2 $h_css>Here's the scan of your site</h2>";
          $message .= "<p $p_css>Hi,<br>thanks for using our scanner. Our team will aim to be in touch with you ASAP so we can advise on securing and speeding up your site.</p>";
          $message .= "<p $p_css>Websites need maintenance and security just like your offices do, so you've engaged us to take a quick no-cost & no-obligation assesment of your needs. You wouldn't leave your office door unlocked, or your premises in disrepair - your website needs the same care to keep it safe, secure and speedy.</p>";
          $message .= "<p $p_css>We'll take a look at your scan results and provide an initial analysis in a follow up mail. We'll  explainin what the problems are, whether the tasks are technical, and what your risks currently are. We may advise you engage a professional to resolve any issues, but you are under no obligation to do so.</p>";
          $message .= "<p $p_css>The attached log is for your information only $attfile</p>";
          $message .= "<p $p_css>Best regards,<br> Steve and the Team at OpalSupport</p>";


          $mailout = wp_mail('steve@pheriche.com', $subject, $message, $headers, $attachments);
          echo 'we just tried to send a mail,'. $attfile.', if it failed there should be an error ' .$mailout;
        }
      #
    //  echo 'we just tried to send a mail, this is step one , the url = '.plugin_dir_url( __DIR__  ) . 'reports/opalscan.log';
    }
   die();
}
add_action( 'wp_ajax_opalreportmail', 'opalscan\opalreportmail' );
