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

          $sitename = get_bloginfo('name');
          $siteURL = get_bloginfo('wpurl');

          $attachments = array($attfile, $html_log_file);
          $h_css= 'style="font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size: 24px;"';
          $p_css= 'style="font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size: 16px;"';
          $c_subject = 'You scanned your site '.$sitename;
          $headers = array('Content-Type: text/html; charset=UTF-8','From: Opal Web Support <wpscansupport@opalsphere.com>' );
          // $headers = 'From: My Name <pheriche@pheriche.com>' . "\r\n";
          $message ='';

          $c_message .="<h2 $h_css>Here's the scan of your site</h2>";
          $c_message .= "<p $p_css>Hi,<br>thanks for using our scanner. Our team will aim to be in touch with you ASAP so we can advise on securing and speeding up your site.</p>";
          $c_message .= "<p $p_css>Just like offices all professional sites need maintenance and security to keep things in tip top condition. You've engaged us to take a quick no-cost & no-obligation assesment of your needs. You wouldn't leave your office door unlocked, or your premises in disrepair - your website needs the same care to keep it safe, secure and speedy.</p>";
          $c_message .= "<p $p_css>We'll take a look at your scan results and provide an initial analysis in a follow up mail. We'll explain what the problems are, whether the tasks are technical, and what your risks currently are. We may advise you engage a professional to resolve any issues, but you are under no obligation to do so.</p>";
          $c_message .= "<p $p_css>The attached log is for your information only.</p>";
          $c_message .= "<p $p_css>Best regards,<br> Steve and the Team at OpalSupport</p>";
          $client_mail = get_option('admin_email');
          $c_mailout = wp_mail($client_mail, $c_subject, $c_message, $headers, $attachments);

          /* CREATE A TICKET */
          $opal_subject = 'Scanned site '.$sitename.' requests analysis';
          $opal_message .="<h2 $h_css>Scan Analysis Request for $sitename</h2>";
          $opal_message .= "<p $p_css>Please check these logs for $siteURL and analyse this website for maintenance and security to keep it safe, secure and speedy.</p>";
          $opal_mail = 'wpscansupport@opalsphere.com';
          $a_mailout = wp_mail($opal_mail, $opal_subject, $opal_message, $headers, $attachments);

          /* return a console.log message to the front end */
          echo 'we just tried to send a mail,'. $attfile.', if it failed there should be an error ' .$c_mailout;
        }
      #
    //  echo 'we just tried to send a mail, this is step one , the url = '.plugin_dir_url( __DIR__  ) . 'reports/opalscan.log';
    }
   die();
}
add_action( 'wp_ajax_opalreportmail', 'opalscan\opalreportmail' );
