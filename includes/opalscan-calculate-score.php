<?php

/*
calculate individual scores for :
> wp Core version lagging too far behing latest.
> wp security plugin detected

> wp plugins total (too many?)
> wp plugins inactive (too many?)
> wp plugins not at the latest version
> wp plugins abandoned by author
> wp plugins not upto date

> server PHP version
> Server SQL version
> Server SSL cert.

calculate total score, WP score, Plugins score, Server score.
*/

function opal_do_score($decoded_scan){
    $score =100;
    $wp_score = 0;
    $server_score = 0;

    # plugin score
    $inactive_plugin_total = $decoded_scan['plugin_amount'] - $decoded_scan['plugin_active_amount'];
    $plugin_score = $decoded_scan['plugin_outdated'] + $decoded_scan['plugin_noupdates'] + ($decoded_scan['plugin_amount']/2) + ($inactive_plugin_total/2) ;

    # TOTAL UP THE SCORE AND DESCRIBE IT -----------------------
    $score -= ($wp_score + $plugin_score +  $server_score);
    $score = round($score);
    return $score;
  }


function calculate_wp_score($scan_results){
    $score = 0;
    $wp_version = $scan_results['wp_version'];
    $wp_version_available = $scan_results['wp_version_available'];
    $score = op_version_difference($wp_version_available,$wp_version);
    //$score = op_version_difference('7.0.1','5.2');
    $score *= 100;
    $score = $score>100 ? 100 : $score;
    $score = 100-$score;
  //  $score = version_compare($wp_version_available, $wp_version);
    //$score *=3;
    return $score;
}

function calculate_wpsecurity_score($ssl){
  $sslscore= $ssl*100;
  return   $sslscore;
}

function calculate_serverPHP_score($scan_results){

/*  $score = 0;
  $sql = $scan_results['sql_version']; ///////// DO SOMETHING WITH THIS

  $ssl = $scan_results['ssl'];
  if ($ssl <1){ $score = 10;}*/
  $PHPscore =  100;
  if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    $PHPscore = 10;
  }
  if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    $PHPscore =  30;
  }
  if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    $PHPscore =  80;
  }
  if (version_compare(PHP_VERSION, '7.3.0', '<')) {
    $PHPscore =   90;
  }

  $scan_results['scores']['serverPHP'] = $PHPscore;
  return $scan_results;
}



  function calculate_plugin_score($scan_results){
    //$score = $scan_results['plugin_amount'];
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
        $pa_score=70;
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
    $scan_results['scores']['plugins_active'] = $pa_score;

    switch(true){ // score the outdated of plugins
      case ($p_outdated <1):
        $po_score=100;
        break;
      case ($p_outdated <3):
        $po_score=90;
        break;
      case ($p_outdated <6):
        $po_score=70;
        break;
      case ($p_outdated <9):
        $po_score=35;
        break;
      case ($p_outdated <11):
        $po_score=10;
        break;
      default:
        $po_score = 0;
    }
      $scan_results['scores']['plugins_outdated'] = $pa_score;

    switch(true){ // score the outdated of plugins
      case ($p_noupdate <1):
        $pn_score=100;
        break;
      case ($p_noupdate <3):
        $pn_score=90;
        break;
      case ($p_noupdate <6):
        $pn_score=70;
        break;
      case ($p_noupdate <9):
        $pn_score=35;
        break;
      case ($p_noupdate <11):
        $pn_score=10;
        break;
      default:
        $pn_score = 0;
    }
      $scan_results['scores']['plugins_abandoned'] = $pn_score;

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
      $scan_results['scores']['plugins_inactive'] = $pi_score;
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

function detect_plugin_security($slug, $current=''){
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
    'wordfence'
  );

  if (in_array($slug, $haystack)) {
      return $slug;
  }

}

function op_version_difference($available, $current){
  // a cheap version compare. uses string subtraction for first two numbers. array only if revision exists.
  $availableA = explode(".",$available);
  $currentA = explode(".",$current);

  $diff=$available-$current;
  if (array_key_exists('2',$availableA) && $diff ==0){
    # if theres a Revision then calculate it.

    return ($availableA[2] - $currentA[2])*0.01;
  }
  return   $diff;
}
