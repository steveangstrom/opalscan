<?php
namespace opalscan;
function opalreportmail() {
  # sends the mail containing the site report to the user and the opalsupport team.
  # activated via AJAX
  # secured with a nonce and a user capabilities check

  if ( !check_ajax_referer( 'opalscan-security-nonce', 'security' ) ) {
     wp_send_json_error( 'Invalid security token sent.' );
     wp_die();
   }
   if (!current_user_can( 'edit_posts' )) {
      wp_send_json_error( 'Invalid user' );
      wp_die();
    }

    $randomised_filename = get_option( 'opalsupport_log_location' );
    $attfile = plugin_dir_path( __DIR__  ) ."reports/opalscan-$randomised_filename.log";
    $html_log_file = plugin_dir_path( __DIR__  ) . "reports/opal-scanner-report-$randomised_filename.html";

    $sitename = esc_html(get_bloginfo('name'));
    $siteURL = esc_url(get_bloginfo('wpurl'));

    $attachments = array($attfile, $html_log_file);
    $h_css= 'style="font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size: 24px;"';
    $p_css= 'style="font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size: 16px;"';
    $c_subject = 'You scanned your site '.$sitename;
    $c_headers = array('Content-Type: text/html; charset=UTF-8','From: Opal Web Support <wpscansupport@opalsphere.com>' );

    $c_message ="<h2 $h_css>Here's the scan of your site</h2>";
    $c_message .= "<p $p_css>Hi,<br>thanks for using our scanner. Our team will aim to be in touch with you ASAP so we can advise on securing and speeding up your site.</p>";
    $c_message .= "<p $p_css>We'll take a look at your scan results and provide an initial analysis in a follow up mail. We'll explain what the problems are, whether the tasks are technical, and what your risks currently are. We may advise you engage a professional to resolve any issues, but you are under no obligation to do so.</p>";
    $c_message .= "<p $p_css>The attached log is for your information only.</p>";
    $c_message .= "<p $p_css>Best regards,<br> Steve and the Team at OpalSupport</p>";
    $client_mail = get_option('admin_email');

    if(is_email($client_mail)){
      $c_mailout = wp_mail($client_mail, $c_subject, $c_message, $c_headers, $attachments);
    }else{
      echo 'the client email address didnt validate as in the right format';
    }


    /* CREATE A TICKET */
    $current_user = wp_get_current_user();
    $user_nicename = esc_html( $current_user->display_name);
    $clientmail = get_option('admin_email');
    $c_user_mail = esc_html( $current_user->user_email );

    $a_headers = array('Content-Type: text/html; charset=UTF-8','From: '.$sitename.' <'.$clientmail.'>' );
    $opal_subject = 'Scanned site '.$sitename.' requests analysis';
    $opal_message = "<h2 $h_css>Scan Analysis Request for $sitename</h2>";
    $opal_message .= "<p $p_css>Please check these logs for $siteURL and analyse this website for maintenance and security to keep it safe, secure and speedy.</p>";
    $opal_message .= "<p $p_css>The requesting user was $user_nicename and their email is $c_user_mail </p>";
    $opal_mail = 'wpscansupport@opalsphere.com'; // this sends to our ticket system

    if(is_email($opal_mail)){
      $a_mailout = wp_mail($opal_mail, $opal_subject, $opal_message, $a_headers, $attachments);
    }else{
      echo 'the opal email address didnt validate as in the right format';
    }

    /* return a console.log message to the front end */
    echo 'we just tried to send a mail and hopefully it succeeded with a boolean true : ' .esc_html($c_mailout);
   die();
}
add_action( 'wp_ajax_opalreportmail', 'opalscan\opalreportmail' );
