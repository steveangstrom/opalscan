<?php
namespace opalscan;
/*
This file contains methods which calculate individual scores for :
> wp Core version lagging too far behing latest.
> wp security plugin detected

> wp plugins total
> wp plugins inactive
> wp plugins not at the latest version
> wp plugins abandoned by author
> wp plugins not upto date

> server PHP version
> Server SQL version - not used.
> Server SSL cert.

calculate total score, also sub scores normalised to 0-100, also
scores for segments "Security" "Maintenance", "Stability/Speed"
*/

function opal_do_score($decoded_scan){
# takes the scores for key components such as "WPcore (version outddated)" - which are scored in severity from 0-100
# and uses those combined with others to give an overall score to a section such as "security"
# so if WPcore and many plugins are out of date, and some plugins are abandoned we can say there's a security risk.
# the scoring is weighted depending on how vital each element is to the section
  $security_score = (
    $decoded_scan['scores']['wpcore']+
    $decoded_scan['scores']['plugins_abandoned'] +
    $decoded_scan['scores']['plugins_outdated'] +
    $decoded_scan['scores']['themes_outdated'] +
    $decoded_scan['scores']['serverSSL'] +
    $decoded_scan['scores']['wp_plugin_security']
  )/6;

    # WP core has a major effect on security so we use that to weight the security score.
    $sec_mult = $decoded_scan['scores']['wpcore']/100;
    $security_score *= $sec_mult;  # will use WP core to scale the total score.


  $maint_score= (
    $decoded_scan['scores']['wpcore']*1 +
    $decoded_scan['scores']['plugins_active']*1 +
    $decoded_scan['scores']['plugins_abandoned']*1 +
    $decoded_scan['scores']['plugins_outdated']*1 +
    $decoded_scan['scores']['themes_outdated']*1+
    $decoded_scan['scores']['themes_active']*1
  )/6;

  if( $decoded_scan['scores']['wpcore'] < 50){
    $maint_score = $decoded_scan['scores']['wpcore'];
  }elseif($decoded_scan['scores']['plugins_outdated'] < 50){
    $maint_score = $decoded_scan['scores']['plugins_outdated'];
  }

  $other_score= (
    $decoded_scan['scores']['wpcore']+
    $decoded_scan['scores']['serverPHP'] +
    $decoded_scan['scores']['serverDBsize'] +
    $decoded_scan['scores']['serverSSL']
  )/4;

  # PHP version has a major effect on speed so we use that to weight the  score.
  $speed_mult = $decoded_scan['scores']['serverPHP']/90;
  $other_score *= $speed_mult;  # will use WP core to scale the total score.


  $scores['total']=round(($security_score+$maint_score+$other_score)/3);
  $scores['security']=$security_score;
  $scores['maintenance']=$maint_score;
  $scores['other']=$other_score;
  $decoded_scan['scores']['analysis'] = $scores;
  # return $scores;
  return $decoded_scan;
}


function calculate_wp_score($scan_results){
  $score = 0;
  $wp_version = $scan_results['wp_version'];
  $wp_version_available = $scan_results['wp_version_available'];
  $score = op_version_difference($wp_version_available,$wp_version);
  $score *= 100;
  $score = $score>100 ? 100 : $score;
  $score = 100-$score;
  $score = $score< 0 ? 0 : $score;
  return $score;
}


function calculate_wpsecurity_score($ssl){
  $sslscore= $ssl*100;
  return   $sslscore;
}


function calculate_server_score($scan_results){
  $sql_version = $scan_results['sql_version'];

  $PHPscore =  100;
  if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $PHPscore =   90;
  }
  if (version_compare(PHP_VERSION, '7.3.0', '<')) {
      $PHPscore =  80;
  }
  if (version_compare(PHP_VERSION, '7.2.0', '<')) {
      $PHPscore =  70;
  }
  if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    $PHPscore =  30;
  }
  if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    $PHPscore = 10;
  }
  $scan_results['scores']['serverPHP'] = $PHPscore;

  $sql_size = $scan_results['sql_size'];
  switch(true){ // score the outdated of plugins
    case ($sql_size <15):
      $DBscore=100;
      break;
    case ($sql_size <50):
      $DBscore=90;
      break;
    case ($sql_size <70):
      $DBscore=60;
      break;
    case ($sql_size <90):
      $DBscore=40;
      break;
    default:
      $DBscore = 0;
  }
  $scan_results['scores']['serverDBsize'] = $DBscore;

  /****** SSL check *****/
  $ssl_expiry = $scan_results['ssl']['days'];

  if(!isset($ssl_expiry) || $ssl_expiry == 0){
    $scan_results['scores']['serverSSL'] = 0; # old SSL catchall.
  }

  if ($ssl_expiry >=1){
    switch(true){ // score the SSL expiry. TODO - add issuer deduction for self-signed.
      case ($ssl_expiry <1):
        $SSL_score=10;
        break;
      case ($ssl_expiry <15):
        $SSL_score=30;
        break;
      case ($ssl_expiry <20):
        $SSL_score=50;
        break;
      case ($ssl_expiry <30):
        $SSL_score=80;
        break;
      default:
        $SSL_score = 100;
    }

      $scan_results['scores']['serverSSL'] = $SSL_score;
  }
  return $scan_results;
}

  function calculate_theme_score($scan_results){
    $score = 0;
    $t_amount = $scan_results['theme_amount'];
    $t_outdated = $scan_results['theme_outdated'];

    switch(true){ // score the amount of plugins
      case ($t_amount <2):
        $ta_score=100;
        break;
      case ($t_amount <5):
        $ta_score=90;
        break;
      case ($t_amount <8):
        $ta_score=70;
        break;
      case ($t_amount <12):
        $ta_score=35;
        break;
      case ($t_amount <18):
        $ta_score=10;
        break;
      default:
        $ta_score = 0;
    }

    switch(true){ // score the amount of themes
      case ($t_outdated <1):
        $to_score=100;
        break;
      case ($t_outdated <5):
        $to_score=90;
        break;
      case ($t_outdated <10):
        $to_score=70;
        break;
      case ($t_outdated <15):
        $to_score=50;
        break;
      default:
        $to_score = 0;
    }

    $scan_results['scores']['themes_active'] = $ta_score;
    $scan_results['scores']['themes_outdated'] = $to_score;
    return $scan_results;

  }

  function calculate_plugin_score($scan_results){
    $score = 0;
    $p_amount = $scan_results['plugin_amount'];
    $p_outdated = $scan_results['plugin_outdated'];
    $p_noupdate = $scan_results['plugin_noupdates'];
    $p_active = $scan_results['plugin_active_amount'];

    switch(true){ // score the amount of plugins
      case ($p_amount <15):
        $pa_score=100;
        break;
      case ($p_amount <25):
        $pa_score=90;
        break;
      case ($p_amount <30):
        $pa_score=50;
        break;
      case ($p_amount <40):
        $pa_score=35;
        break;
      case ($p_amount <50):
        $pa_score=10;
        break;
      default:
        $pa_score = 0;
    }
    $scan_results['scores']['plugins_active'] = $pa_score;# push it into the array

    switch(true){ // score the outdated plugins
      case ($p_outdated <1):
        $po_score=100;
        break;
      case ($p_outdated <5):
        $po_score=90;
        break;
      case ($p_outdated <9):
        $po_score=70;
        break;
      case ($p_outdated <12):
        $po_score=50;
        break;
      case ($p_outdated <16):
        $po_score=10;
        break;
      default:
        $po_score = 0;
    }
      $scan_results['scores']['plugins_outdated'] = $po_score;# push it into the array

    switch(true){ // score the abandoned plugins
      case ($p_noupdate <1):
        $pn_score=100;
        break;
      case ($p_noupdate <3):
        $pn_score=90;
        break;
      case ($p_noupdate <5):
        $pn_score=60;
        break;
      case ($p_noupdate <8):
        $pn_score=30;
        break;
      case ($p_noupdate <15):
        $pn_score=10;
        break;
      default:
        $pn_score = 0;
    }
    $scan_results['scores']['plugins_abandoned'] = $pn_score; # push it into the array

    $p_inactive = $p_amount - $p_active;
    switch(true){ // score the outdated of plugins
      case ($p_inactive <1):
        $pi_score=100;
        break;
      case ($p_inactive <3):
        $pi_score=85;
        break;
      case ($p_inactive <7):
        $pi_score=70;
        break;
      case ($p_inactive <15):
        $pi_score=60;
        break;
      case ($p_inactive <20):
        $pi_score=30;
        break;
      default:
        $pi_score = 0;
    }
      $scan_results['scores']['plugins_inactive'] = $pi_score;# push it into the array

    $wp_sec=0;
    if ($scan_results['wp_plugin_security'][1]>0){
      $wp_sec=100;
    }
    $scan_results['scores']['wp_plugin_security']=$wp_sec; # push it into the array

    return $scan_results;
  }


function fileSizeInfo($filesize) {
    $bytes = array('KB', 'KB', 'MB', 'GB', 'TB');
    if ($filesize < 1024)
        $filesize = 1;
    for ($i = 0; $filesize > 1024; $i++)
        $filesize /= 1024;

    $dbSizeInfo['size'] = round($filesize, 3);
    $dbSizeInfo['type'] = $bytes[$i];

    return $dbSizeInfo;
}

function calculate_database_size() {
    global $wpdb;
    $dbsize = 0;
    $rows = $wpdb->get_results("SHOW table STATUS");
    foreach($rows as $row)
        $dbsize += $row->Data_length + $row->Index_length;
    return fileSizeInfo($dbsize);
}

function detect_plugin_security($slug, $current='',$key){
  if ($current !=''){return $current ;}
  $haystack = array(
    'astra',
    'all-in-one-wp-security-and-firewall',
    'better-wp-security',
    'defender-security',
    'ninjafirewall',
    'secupress',
    'security-ninja',
    'sucuri-scanner',
    'wp-cerber',
    'wp-simple-firewall',
    'wordfence',
    'bulletproof-security',
    'malcare',
    'webarx',
    'wp-fail2ban'
  );

  if (in_array($slug, $haystack)) {
    $activePlugins = get_option('active_plugins');
    $active = 0;

   if(in_array($key,$activePlugins)){
      $active = 1;
    }

    $out = Array($slug,$active);
      return $out;
  }

}

function op_version_difference($available, $current){
  # function serving as a cheap version compare. uses string subtraction for first two numbers. array only if revision exists.
  $availableA = explode(".",$available);
  $currentA = explode(".",$current);

  $diff=$available-$current;
  if (array_key_exists('2',$availableA) && $diff ==0){
    # if theres a Revision then calculate it.
    return ($availableA[2] - $currentA[2])*0.01;
  }
  return   $diff;
}


function SSLcheck(){
  # checking the status of the SSL cert and epxpiry  if it exists.
	$https_url_with = site_url( null, 'https' );
	$https_url_without = explode("://",$https_url_with);
	$https_url_without = $https_url_without[1];
	$orignal_parse = parse_url($https_url_with, PHP_URL_HOST);

	$get = stream_context_create(array("ssl" => array(
      "capture_peer_cert" => TRUE,
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true)
      ));
  $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
	if(!$read){
  //  echo 'ERROR: Unable to check SSL certificate validity.';
    return;
  }else{
		$cert = stream_context_get_params($read);
		$certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
    $localts = $certinfo['validTo_time_t'];
   // echo 'valid until '.$localts;
		$days_to_expiry = $localts - time();
		$days_to_expiry = $days_to_expiry / 60 / 60 / 24;
    $out=Array('days'=>$days_to_expiry, 'issuer'=>$certinfo['issuer']);
		//return $days_to_expiry;
    //$out = $certinfo;
    return $out;
	}
}
