<?php
namespace opalscan;
if(is_admin()) {
  /****** RENDER THE DATA AS HTML ********/
  function opalscan_render_html($JSON_scan, $livescan=false,$mailmode=false){
  	# This function renders the html from the scan.
    # it takes the JSON passed to it and accepts a flag to switch between a HTML echo, or a return
    # it can be called from the AJAX from where it returns to be rendered to the page by JS
    # it is also used to create a HTML file which is sent to the receipients as an attachment via mail.

    $out='';
    $score =100;

    $decoded_scan = json_decode($JSON_scan,true);
    $log_date = strtotime($decoded_scan['scanDate']['date']);

    $decoded_scan = opal_do_score($decoded_scan);
    $scores = $decoded_scan['scores']['analysis'];
    $score_total=$scores['total'];

    if($mailmode===true){
      # the $mail_css appends some simple css to the saved html file version of the output of this parent function
      $mail_css = file_get_contents(plugin_dir_url( __FILE__ ) . 'css/mail_style.css');
      $out.='<!DOCTYPE html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Opal Scan &amp; Support WordPress Report</title>
        <style type="text/css">'.$mail_css.'
        </style>
        </head>
        <body>
        <h1><a href="https://opalsupport.com">OpalSupport</a> : Report For '.esc_html(get_bloginfo('name')).'</h1>
        <p>We will be in touch with you to advise on the next steps to take</p>';
      }


    # Display results in tabbed interface

    $out.= '<div id = "opalscanner_results" class="opalscanner_results">
    <div class="opal_tab_bar noselect">
      <div class="opal_tab active" data-tab="opalsummary">Summary</div>
      <div class="opal_tab" data-tab="opalreport">Full Report</div>
    </div>';
    $out.='<div id = "opalsummary" class= "opal_pane active">';

    $scandate = date('jS \o\f F Y g:i A', $log_date);
    if($livescan===false){
        $out.='<div class="opal_infobox"><p>Displaying previous scan ( '.esc_html( $scandate).' ) <a class="opaldoscan">scan again</a> to update</p></div>'; // a  conditional checks if this display is from an old log, or a live AJAX request.
    }
    $out.='<canvas id ="opalreportgraph" data-score="'.esc_html($score_total).'" width="250px" height="200px"></canvas>';# the speedo display
    $out .='<div id="op_bar_wrapper">';
    $out .='<div id="score-secure" class="scorebar" data-score="'.esc_html($scores['security']).'"><div class="opbar"></div></div>';
    $out .='<div id="score-maintain" class="scorebar" data-score="'.esc_html($scores['maintenance']).'"><div class="opbar"></div></div>';
    $out .='<div id="score-other" class="scorebar" data-score="'.esc_html($scores['other']).'"><div class="opbar"></div></div>';
    #  print a textual summary -  function is in Opalscan-advice.php
    $out .= opal_summary($score_total);
    $out .='</div>';

    /****** top score and summary block *****/

    # opal_advice function is passed the entire decoded scan so it can render advice in natural language. function is in Opalscan-advice.php
    $out .= opal_advice($decoded_scan, $score_total);
    $out.= '</div>';// end summary tab content

    /* -----RENDER THE SCORE RESULT TABLES ---*/
    $out.='<div id = "opalreport" class = "opal_pane">';

    # explains the three human readable scores.
    $out.=('<h2>Security, Maintenance and Stability</h2>');
    $out.=('<p>The first section shows how we determine the overal scores for the arbitrary categorisations Security, Maintenance and (Site) Stability. The scores are weighted. Each score itself is the product of a calculation and those figures are shown <a href="#scoring">below</a>. A good score is 100, a bad score is 0 </p>');

    $security_score = $decoded_scan['scores']['analysis']['security'];
    $maint_score= $decoded_scan['scores']['analysis']['maintenance'];
    $other_score= $decoded_scan['scores']['analysis']['other'];

    $out.=('<table class="opalscan_results_table">');
    $out.=('<thead><tr><th><h3>Security Scanned Item</h3></th><th>Score</th></tr></thead>');
    $out.='<tr><td>Wp Core '.esc_html($decoded_scan['wp_version']).' (Available '.esc_html($decoded_scan['wp_version_available']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['wpcore']).'</td></tr>';
    if (isset($decoded_scan['ssl'])){
      $ssl_string = esc_html(' - '.round($decoded_scan['ssl']['days']). ' days until expiry, ('.esc_html($decoded_scan['ssl']['issuer']['O']).')');
    }else{
      $ssl_string ='';
    }
    $out.='<tr><td>Server SSL '.esc_html($ssl_string).'</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['serverSSL']).'</td></tr>';
    $out.='<tr><td>Abandoned Plugins ('.esc_html($decoded_scan['plugin_noupdates']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['plugins_abandoned']).'</td></tr>';
    $out.='<tr><td>Outdated Plugins ('.esc_html($decoded_scan['plugin_outdated']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['plugins_outdated']).'</td></tr>';
    $out.='<tr><td>Outdated Themes ('.esc_html($decoded_scan['theme_outdated']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['themes_outdated']).'</td></tr>';
    $activestatus = ' - <b>inactive</b>';
    if($decoded_scan['wp_plugin_security'][1] =='1'){$activestatus = '';}
    $out.='<tr><td>Wp Security Plugin ('.esc_html($decoded_scan['wp_plugin_security'][0]).''.wp_kses($activestatus,$allowed_html).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['wp_plugin_security']).'</td></tr>';
    $out.='<tr class="scoretotal"><td>Score</td><td >'. esc_html(round($security_score)) .'</td></tr>';
    $out.=('</table>');

    $out.=('<table class="opalscan_results_table">');
    $out.=('<thead><tr><th><h3>Maintenance Scanned Item</h3></th><th>Score</th></tr></thead>');
    $out.='<tr><td>Wp Core '.esc_html($decoded_scan['wp_version']).' (Available '.esc_html($decoded_scan['wp_version_available']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['wpcore']).'</td></tr>';
    $out.='<tr><td>Plugins active ('.esc_html($decoded_scan['plugin_amount']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['plugins_active']).'</td></tr>';
    $out.='<tr><td>Abandoned Plugins ('.esc_html($decoded_scan['plugin_noupdates']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['plugins_abandoned']).'</td></tr>';
    $out.='<tr><td>Outdated Plugins ('.esc_html($decoded_scan['plugin_outdated']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['plugins_outdated']).'</td></tr>';
    $out.='<tr><td>Installed Themes ('.esc_html($decoded_scan['theme_amount']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['themes_active']).'</td></tr>';
    $out.='<tr><td>Outdated Themes ('.esc_html($decoded_scan['theme_outdated']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['themes_outdated']).'</td></tr>';
    $out.='<tr class="scoretotal"><td>Score</td><td>'. round($maint_score) .'</td></tr>';
    $out.=('</table>');

    $out.='<h3>Server Stability & Speed</h3>';
    $out.=('<table class="opalscan_results_table">');
    $out.=('<thead><tr><th>Scanned Item</th><th>Score</th></tr></thead>');
    $out.='<tr><td>Wp Core '.esc_html($decoded_scan['wp_version']).' (Available '.esc_html($decoded_scan['wp_version_available']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['wpcore']).'</td></tr>';
    $out.='<tr><td>Server PHP ('.esc_html($decoded_scan['php_version']).')</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['serverPHP']).'</td></tr>';
    $out.='<tr><td>Server Database size ('.esc_html($decoded_scan['sql_size']).' Mb)</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['serverDBsize']).'</td></tr>';
    $out.='<tr><td>Server SSL '.esc_html($ssl_string).'</td><td class="opfullscanbar">'.esc_html($decoded_scan['scores']['serverSSL']).'</td></tr>';
    $out.='<tr class="scoretotal"><td>Score</td><td>'. esc_html(round($other_score)) .'</td></tr>';
    $out.=('</table>');

    $out.=('<h2><a name="scoring">Wordpress and Server</a></h2>');
    $out.=('<table class="opalscan_results_table opalscan_details_table">');
    $out.=('<thead><tr><th>Element</th> <th>Installed</th><th>Status</th></tr></thead>');

    $wp_update_needed = "OK";
    if ($decoded_scan['scores']['wpcore'] <90){
      $wp_update_needed = "Attention";
    }elseif($decoded_scan['scores']['wpcore'] <75){
      $wp_update_needed = "Urgent";
    }
    $out.=('<tr><td>Wordpress Core Version</td><td>'.esc_html($decoded_scan['wp_version']).' Available ('.esc_html($decoded_scan['wp_version_available']).')</td><td>'.$wp_update_needed.'</td></tr>');

    if (strlen($decoded_scan['wp_plugin_security'][0])>2){$secstatus = 'OK';}else{ $secstatus = 'Attention';}
    $out.= ('<tr><td>Wordpress Security</td><td>'.esc_html($decoded_scan['wp_plugin_security']).' </td><td>'.$secstatus.'</td></tr>');
    $out.= opal_rendertablerow('Plug-ins Installed',$decoded_scan['plugin_amount'],$decoded_scan['plugin_amount'], 10, 15 ); # go get a row render, escaping is done there.

    $pinstatus = 'OK';
    if ($decoded_scan['plugin_amount'] - $decoded_scan['plugin_active_amount']>3){$pinstatus = 'Attention';}
    if ($decoded_scan['plugin_amount'] - $decoded_scan['plugin_active_amount']>6){$pinstatus = 'Urgent';}
    $out.=('<tr><td>Plug-ins Inactive</td><td>'.esc_html(($decoded_scan['plugin_amount'] - $decoded_scan['plugin_active_amount'])).'</td><td>'.$pinstatus.'</td></tr>');

    $pupinstatus = 'OK';
    if ($decoded_scan['plugin_outdated']>3){$pupinstatus = 'Attention';}
    if ($decoded_scan['plugin_outdated']>6){$pupinstatus = 'Urgent';}
    $out.=('<tr><td>Plug-ins Outdated</td><td>'.esc_html($decoded_scan['plugin_outdated']).'</td><td>'.$pupinstatus.'</td></tr>');

    $pabinstatus = 'OK';
    if ($decoded_scan['plugin_noupdates']>3){$pabinstatus = 'Attention';}
    if ($decoded_scan['plugin_noupdates']>6){$pabinstatus = 'Urgent';}
    $out.=('<tr><td>Plug-ins Abandoned</td><td>'.esc_html($decoded_scan['plugin_noupdates']).'</td><td>'.$pabinstatus.'</td></tr>');

    /********** DISPLAY THEMES INFO ************/
   $themc_status =$thmout_status = 'OK';
   if ($decoded_scan['theme_amount'] > 6){$themc_status = 'Attention';}
   if($decoded_scan['theme_amount'] > 15){ $themc_status = 'Urgent'; }
   $out.=('<tr><td>Themes Installed</td><td>'.esc_html($decoded_scan['theme_amount']).'</td><td>'.$themc_status.'</td></tr>');

   if ($decoded_scan['theme_outdated'] > 2){$thmout_status = 'Attention';}
   if($decoded_scan['theme_outdated'] > 4){ $thmout_status = 'Urgent'; }
   $out.=('<tr><td>Themes Outdated</td><td>'.esc_html($decoded_scan['theme_outdated']).'</td><td>'.$thmout_status.'</td></tr>');

    $phpstatus = 'OK';
    if ($decoded_scan['scores']['serverPHP']<95){$phpstatus = 'Attention';}
    if ($decoded_scan['scores']['serverPHP']<70){$phpstatus = 'Urgent';}
    $out.=('<tr><td>Web Server PHP</td><td>PHP Version '.esc_html($decoded_scan['php_version']).'</td><td>'.$phpstatus.'</td></tr>');

    $sqlstatus = 'OK';
    $out.=('<tr><td>SQL Server</td><td>SQL Version '.esc_html($decoded_scan['sql_version']).'</td><td>'.$sqlstatus.'</td></tr>');

    $databasesize = 'OK';
    $dbSize= $decoded_scan['sql_size'];
    $status='OK';
    if ($dbSize > 30){$status = 'Attention';}
    if ($dbSize > 80){$status = 'Urgent';}
    $out.=('<tr><td>SQL Database Size</td><td> '.esc_html($dbSize).' MB</td><td> '.$status.' </td></tr>');

    $ssldays = round($decoded_scan['ssl']['days']);
    $sslstatus = ($ssldays>30) ? 'OK' : 'Attention';
    $out.=('<tr><td>SSL Security</td><td>'.esc_html($ssldays).' Days ('.esc_html($decoded_scan['ssl']['issuer']['O']).')</td><td>'.$sslstatus.'</td></tr>');
    $out.=('</table>');

    $allPlugins = $decoded_scan['allPlugins'];
    $out.=('<h2>Plug-in Details</h2>');
    $out.=('<table class="opalscan_results_table opalscan_details_table">');
    $out.=('<thead><tr><th>Plugin</th> <th>Installed Version</th> <th>Status</th> <th>Availability</th></tr></thead>');

    foreach($allPlugins as $key => $value) {
      $out.='<tr><td>'.esc_html($value['Title']).'</td>';
      $out.= '<td>'.esc_html($value['Version']).'</td>';

      $outstatus = $value['plugin_outdated']? 'Needs Update' : 'Most Recent';
      $out.= '<td>'.esc_html($outstatus).'</td>';

      $updstatus = 'OK';
      if ($value['plugin_noupdates'] >11){$updstatus = 'Outdated';}
      if ($value['plugin_noupdates'] >20){$updstatus = 'Abandoned';}
        $out.= '<td>'.$updstatus.'</td></tr>';
      }
      $out.=('</table>');

      $out.=('<div class="hideinmail"><p><br>Send your report to Opal Support and we will give you a free analysis.<br>A copy of the full report and our security analysis will be sent to '.esc_url(get_option('admin_email')).'</p>');
      $out.=('<a class="opalbigbutton opalsend logpresent opalsendGDPR">Send Report</a></div>');

      $out.='</div>'; //  END OF report pane

      # if this is a display of an archived  log then print it, otherwise we are in an AJAX situation, so return it.
      if ($livescan==false){
        echo $out; // HTML echo to page, for PHP
      }else{
        return $out; // AJAX return, for JS
      }
  }

  function opalscan_show_scan(){
    # Go and get a previous scan from the log
    # the reason we do this is to cache a scan, and prevent site slowdown.
    # We could cron-job the scans but I want users to feel in control of this

    # the filename of the JSON log is randomised and deleted then generated on each scan, to prevent bad bot crawling behaviour
    $randomised_filename = get_option( 'opalsupport_log_location' );
    $logfile=plugin_dir_path( __DIR__ ) . "reports/opalscan-$randomised_filename.log";

    if (file_exists($logfile)) {
      $JSON_scan = file_get_contents($logfile);
      opalscan_render_html($JSON_scan);// render the array as HTML table.
    }
  }


  function opalscan_ajax_request() {
    #Security: check for a nonce, and also for user capabilities.
    if ( !check_ajax_referer( 'opalscan-security-nonce', 'security' ) ) {
      wp_send_json_error( 'Invalid security token sent.' );
      wp_die();
    }
    if ( !current_user_can( 'edit_posts' )) {
      wp_send_json_error( 'Invalid user.' );
      wp_die();
    }

    # Query the environment and populate an array and write it to a log to be parsed by other functions.
    $JSON_results = opalscan_get_scan();
    $decoded_scan = json_encode($JSON_results);

    # take the array and render it as HTML but put it into a variable to send it to AJAX for JS "live" render
    $rendered_scan = opalscan_render_html($decoded_scan, true);

    // if we are do with that scan function, then display the results. it takes a while, so within that func we call more AJAX for status updates
    $out['html']= $rendered_scan;
    $out['scansuccess']= true;
    $return = json_encode($out);
    wp_send_json($return);
    die();
  }
  add_action( 'wp_ajax_opalscan_ajax_request', 'opalscan\opalscan_ajax_request' );
}

function opal_rendertablerow($label='',$installed='',$match='',$bp1=0,$bp2=10){
  # renders an html table row, not used to its full extent.
  $status = 'OK';
 if ($match>$bp1){
    $status = 'Attention';
  }
  if ($match>$bp2){
    $status = 'Urgent';
  }
  $out=('<tr><td>'.esc_html($label).'</td><td>'.esc_html($installed).'</td><td>'.esc_html($status).'</td></tr>');
  return $out;
}

function opalscan_render_summarytable($decoded_scan){
    # renders an html table fora  summary in the dashboard and in the summary tab of the plugin.
    $out=('<table class="opalscan_results_table opalbigtable">');
    $targ = $decoded_scan['scores']['wpcore'];
    if ($targ >80 && $targ <99){
      $out.='<tr><td class="inform wpcore">Your Wordpress core is out of date</td></tr>';
    }elseif($targ <79){
      $out.='<tr><td class="warn wpcore">Your Wordpress core is very out of date</td></tr>';
    }

    if (strlen($decoded_scan['wp_plugin_security'][0])<2){
      $out.='<tr><td class="inform wpcore">Your Wordpress does not seem to have a security plugin</td></tr>';
    }
    # SSL summary
    $ssl = $decoded_scan['scores']['serverSSL'];
    if($ssl>70 && $ssl<90){
      $out.='<tr><td class="inform server">Your security certificate has problems</td></tr>';
    }elseif($ssl<70){
      $out.='<tr><td class="warn server">Your security certificate has major problems</td></tr>';
    }
    $serverPHP= $decoded_scan['scores']['serverPHP'];
    if($serverPHP>50 && $serverPHP<90){
      $out.='<tr><td class="inform server">Your server software would benefit from updating (PHP)</td></tr>';
    }elseif($serverPHP<50){
      $out.='<tr><td class="warn server">Your server core components are very outdated (PHP)</td></tr>';
    }

    $plugins_active = $decoded_scan['scores']['plugins_active'];
      if($plugins_active>50 && $plugins_active<80){
      $out.='<tr><td class="inform plugin">There are '.esc_html($decoded_scan['plugin_amount']).'  plugins active</td></tr>';
    }elseif($plugins_active<50){
      $out.='<tr><td class="warn plugin">There are '.esc_html($decoded_scan['plugin_amount']).'  plugins active, that is too many</td></tr>';
    }

    $plugins_outdated= $decoded_scan['scores']['plugins_outdated'];
      if($plugins_outdated>50 && $plugins_outdated<80){
      $out.='<tr><td class="inform plugin">There are '.esc_html($decoded_scan['plugin_outdated']).' plugins needing updates</td></tr>';
    }elseif($plugins_outdated<50){
      $out.='<tr><td class="warn plugin">There are '.esc_html($decoded_scan['plugin_outdated']).' plugins needing updates, that is too many</td></tr>';
    }

    $plugins_abandoned= $decoded_scan['scores']['plugins_abandoned'];
    if($plugins_abandoned>50 && $plugins_abandoned<90){
    $out.='<tr><td class="inform plugin">There are '.esc_html($decoded_scan['plugin_noupdates']).' plugins which may have been abandoned by their authors</td></tr>';
    }elseif($plugins_abandoned<50){
      $out.='<tr><td class="warn plugin">There are '.esc_html($decoded_scan['plugin_noupdates']).' plugins which may have been abandoned by their authors</td></tr>';
    }

    $themes_active= $decoded_scan['scores']['themes_active'];
    if($themes_active>50 && $themes_active<90){
    $out.='<tr><td class="inform theme">There are '.esc_html($decoded_scan['theme_amount']).' themes installed</td></tr>';
    }elseif($themes_active<50){
      $out.='<tr><td class="warn theme">There are '.esc_html($decoded_scan['theme_amount']).' themes installed</td></tr>';
    }

    $themes_outdated= $decoded_scan['scores']['themes_outdated'];
    if($themes_outdated>50 && $themes_outdated<90){
    $out.='<tr><td class="inform theme">'.esc_html($decoded_scan['theme_outdated']).' themes are outdated</td></tr>';
    }elseif($themes_outdated<50){
      $out.='<tr><td class="warn theme">'.esc_html($decoded_scan['theme_outdated']).' themes are outdated</td></tr>';
    }
    if($decoded_scan['scores']['analysis']['total']>90){
    $out.='<tr><td class="">Your site is healthy, remember to check often</td></tr>';
    }
    $out.=('</table>');
    return $out;
}
/* some escaping help */
function opal_allowed_html() {
	return array (
		'a' => array (
			'href' => array(),
			'class' => array(),
		),
    'br' => array(),
    'em' => array(),
    'strong' => array(),
    'i' => array(),
    'p' => array(),
	);
}
$allowed_html = opal_allowed_html();