<?php
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
