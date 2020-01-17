<?php
if(is_admin()) { // make sure, the following code runs only in the back end

  /****** RENDER THE DATA AS HTML ********/
  function opalscan_render_html($JSON_scan, $livescan=false){
  	// this can be called from the AJAX , or it can be used to create the HTML file which is sent to the receipients.

    $out='';
    $score =100;
    //$scorewords=['Extremely bad','Extremely bad', 'Very bad','Bad','Adequate','Needs Attention','Needs Attention','Good','Very Good','Excellent'];

    $decoded_scan = json_decode($JSON_scan,true);
    $log_date = strtotime($decoded_scan['scanDate']['date']);
    $scores = opal_do_score($decoded_scan);
    $score_total=$scores['total'];
    # Display results
    $out.= '<div id = "opalscanner_results" class="opalscanner_results">
    <div class="opal_tab_bar noselect">
      <div class="opal_tab active" data-tab="opalsummary">Summary</div>
      <div class="opal_tab" data-tab="opalreport">Full Report</div>
    </div>';
    $out.='<div id = "opalsummary" class= "opal_pane active">';

    $scandate = date('dS \o\f F Y g:i A', $log_date);
    if($livescan===false){
        $out.='<div class="opal_infobox"><p>Displaying previous scan ('.$scandate.') <a class="opaldoscan">scan again</a> to update</p></div>'; // a  conditional checks if this display is from an old log, or a live AJAX request.
    }
    $out.='<canvas id ="opalreportgraph" data-score="'.$score_total.'" width="150px" height="150px"></canvas>';# the speedo display
    $out .='<div id="score-secure" class="scorebar" data-score="'.$scores['security'].'"><div class="opbar"></div></div>';
    $out .='<div id="score-maintain" class="scorebar" data-score="'.$scores['maintenance'].'"><div class="opbar"></div></div>';
    $out .='<div id="score-other" class="scorebar" data-score="'.$scores['other'].'"><div class="opbar"></div></div>';

/****** top score and summary block *****/
    $out.='<div class="summary_wrap">';
      $out.='<div class="opalscore_wrap">
        <div class = "opalscore score s'.round($score_total/10).'0"><span>SCORE '.$score_total.'%</span></div>
        <div class="deco s10"></div><div class="deco s20"></div><div class="deco s30"></div><div class="deco s40"></div><div class="deco s50"></div><div class="deco s60"></div><div class="deco s70"></div><div class="deco s80"></div><div class="deco s90"></div><div class="deco s100"></div></div>';

    $out .= opal_summary($score_total);

    $out.='</div>';#end summary wrapper

  /* --- describe plugin state verbally -----*/
  # this function is passed the entire decoded scan.
    $out .= opal_advice($decoded_scan, $score_total);
    $out.= '</div>';// end summary tab content


  /* -----RENDER THE SCORE RESULT TABLES ---*/
    $out.='<div id = "opalreport" class = "opal_pane">';
    $out.=('<h2>Wordpress and Server</h2>');
    $out.=('<table class="opalscan_results_table">');
    $out.=('<thead><tr><th>Element</th> <th>Installed</th><th>Status</th></tr></thead>');

   /*$wpstatus = 'OK';
    if ($decoded_scan['scores']['wpcore']>0){$wpstatus = 'Attention';}
    if ($decoded_scan['scores']['wpcore']>10){$wpstatus = 'Urgent';}
    $out.=('<tr><td>Wordpress Core Version</td><td>'.$decoded_scan['wp_version'].' ( Avaliable '.$decoded_scan['wp_version_available'].' )</td><td>'.$wpstatus.'</td></tr>');
*/
    $out.=opal_rendertablerow('Wordpress Core Version',$decoded_scan['wp_version'],$decoded_scan['scores']['wpcore'], 0, 10 );

    if (strlen($decoded_scan['wp_plugin_security'])>2){$secstatus = 'OK';}else{ $secstatus = 'Attention';}
    $out.=('<tr><td>Wordpress Security</td><td>'.$decoded_scan['wp_plugin_security'].' </td><td>'.$secstatus.'</td></tr>');

    /*$pstatus = 'OK';
    if ($decoded_scan['plugin_amount']>10){$pstatus = 'Attention';}
    if ($decoded_scan['plugin_amount']>15){$pstatus = 'Urgent';}
    $out.=('<tr><td>Plug-ins Installed</td><td>'.$decoded_scan['plugin_amount'].'</td><td>'.$pstatus.'</td></tr>');
    */
    $out.=opal_rendertablerow('Plug-ins Installed',$decoded_scan['plugin_amount'],$decoded_scan['plugin_amount'], 10, 15 );

    $pinstatus = 'OK';
    if ($decoded_scan['plugin_active_amount']>3){$pinstatus = 'Attention';}
    if ($decoded_scan['plugin_active_amount']>6){$pinstatus = 'Urgent';}
    $out.=('<tr><td>Plug-ins Inactive</td><td>'.($decoded_scan['plugin_amount'] - $decoded_scan['plugin_active_amount']).'</td><td>'.$pinstatus.'</td></tr>');

    $pupinstatus = 'OK';
    if ($decoded_scan['plugin_outdated']>3){$pupinstatus = 'Attention';}
    if ($decoded_scan['plugin_outdated']>6){$pupinstatus = 'Urgent';}
    $out.=('<tr><td>Plug-ins Outdated</td><td>'.$decoded_scan['plugin_outdated'].'</td><td>'.$pupinstatus.'</td></tr>');

    $pabinstatus = 'OK';
    if ($decoded_scan['plugin_noupdates']>3){$pabinstatus = 'Attention';}
    if ($decoded_scan['plugin_noupdates']>6){$pabinstatus = 'Urgent';}
    $out.=('<tr><td>Plug-ins Abandoned</td><td>'.$decoded_scan['plugin_noupdates'].'</td><td>'.$pabinstatus.'</td></tr>');

    $phpstatus = 'OK';
    if ($decoded_scan['scores']['serverPHP']>10){$phpstatus = 'Attention';}
    if ($decoded_scan['scores']['serverPHP']>20){$phpstatus = 'Urgent';}
    $out.=('<tr><td>Web Server PHP</td><td>PHP Version '.$decoded_scan['php_version'].'</td><td>'.$phpstatus.'</td></tr>');

    $sqlstatus = 'OK';
    //if ($decoded_scan['sql_version']==0){$sqlstatus = 'Attention';}
    $out.=('<tr><td>SQL Server</td><td>SQL Version '.$decoded_scan['sql_version'].'</td><td>'.$sqlstatus.'</td></tr>');

    $databasesize = 'OK';
    $dbSize= $decoded_scan['sql_size'];
    $status='OK';
    if ($dbSize > 30){$status = 'Attention';}
    if ($dbSize > 80){$status = 'Urgent';}
    $out.=('<tr><td>SQL Database Size</td><td> '.$dbSize.' MB</td><td> '.$status.' </td></tr>');

    $ssl = ($decoded_scan['ssl'] == 1) ? 'True' : 'False';
    $sslstatus = ($decoded_scan['ssl'] == 1) ? 'OK' : 'Attention';
    $out.=('<tr><td>SSL Security</td><td>'.$ssl.'</td><td>'.$sslstatus.'</td></tr>');

    $out.=('</table>');


    $allPlugins = $decoded_scan['allPlugins'];
    $out.=('<h2>Plug-in Details</h2>');
    $out.=('<table class="opalscan_results_table">');
    $out.=('<thead><tr><th>Plugin</th> <th>Installed Version</th> <th>Status</th> <th>Availability</th></tr></thead>');

    foreach($allPlugins as $key => $value) {
        $out.='<tr><td>'.$value['Title'].'</td>';
        $out.= '<td>'.$value['Version'].'</td>';

        $outstatus = $value['plugin_outdated']? 'Needs Update' : 'Most Recent';
        $out.= '<td>'.$outstatus.'</td>';

      //  $updstatus = $value['plugin_noupdates']? 'Abandoned' : 'Ok';
      $updstatus = 'OK';
      if ($value['plugin_noupdates'] >11){$updstatus = 'Outdated';}
      if ($value['plugin_noupdates'] >20){$updstatus = 'Abandoned!';}

        $out.= '<td>'.$updstatus.'</td></tr>';
    }
      $out.=('</table>');
      $out.=('<a class="button bigbutton opalsend logpresent">Send Report</a>');
      $out.=('<p><br>Send your report to Opal Support and we will give you a free analysis.<br>A copy of the full report and our security analysis will be sent to '.get_option('admin_email').'</p>');
      $out.=('<p><a data-tab="opalreport" class="opal_tabber_link">View the full detailed Report</a></p>');

      $out.='</div>'; //  END OF report pane


      # if this is a display of an old log then print it, otherwise we are in an AJAX situation, so return it.
      if ($livescan==false){
        $out.=('<pre>');
       $out.=( print_r($decoded_scan, true));
        $out.=('</pre>');
        echo $out;
      }else{
        return $out;
      }

  }
  function opalscan_noprevious_html(){
    $out = 'test';
    return $out;
  }

  function opalscan_show_scan(){ // show previous scan, from the log  including summary
    $logfile=plugin_dir_path( __DIR__ ) . 'reports/opalscan.log';
    if (file_exists($logfile)) {
      $JSON_scan = file_get_contents($logfile);
      opalscan_render_html($JSON_scan);// render the array as HTML table.
    } else {
      //  file_put_contents($filename, '');
    }
  }


/*
  NOTE ;
  to make the AJAX for the scan be more interactive, every loop of the ELSE scan of the repo, call this and
  increment a status bar, possibly even pass something about what is being scanned..

*/

  function opalscan_ajax_request() {
      // The $_REQUEST contains all the data sent via ajax


      if ( isset($_REQUEST['scan']) ) {
          $scan= $_REQUEST['scan'];

          $JSON_results = opalscan_get_scan(); // go get the scan results for a basic check.
          $decoded_scan = json_encode($JSON_results);
          $rendered_scan = opalscan_render_html($decoded_scan, true);

          // if we are down with that scan function, then display the results. it takes a while, so within that func we call more AJAX for status updates
          if ( $scan== 'startscan' ) {

          $out['html']= $rendered_scan;
          $out['scansuccess']= true;
	        echo json_encode($out);
          }
      }
     die();
  }
  add_action( 'wp_ajax_opalscan_ajax_request', 'opalscan_ajax_request' );

function opal_statusbar($status='test'){
  echo $status;
  die();
}
add_action( 'wp_ajax_opal_statusbar', 'opalstatus' );
}

function opal_rendertablerow($label='',$installed='',$match='',$bp1=0,$bp2=10){
  // labels with labels.
  # the current is Installed
  #thing to check is  $match
  #acceptable values are $bp1 and $bp2
  $status = 'OK';
 if ($match>$bp1){
    $status = 'Attention';
  }
  if ($match>$bp2){
    $status = 'Urgent';
  }

    $out=("<tr><td>$label</td><td>$installed</td><td>$status</td></tr>");
    return $out;
}

function opalscan_render_summarytable($decoded_scan){
    $out=('<table class="opalscan_results_table opalbigtable">');

    $targ = $decoded_scan['scores']['wpcore'];
    if ($targ >80 && $targ <99){
      $out.='<tr><td class="inform wpcore">Your Wordpress core is out of date</td></tr>';
    }elseif($targ <79){
      $out.='<tr><td class="warn wpcore">Your Wordpress core is very out of date</td></tr>';
    }

    if (strlen($decoded_scan['wp_plugin_security'])<99){
      $out.='<tr><td class="inform wpcore">Your Wordpress does not seem to have a security plugin</td></tr>';
    }
    # SSL summary
    $ssl = $decoded_scan['scores']['serverSSL'];
    if($ssl>70 && $ssl<90){
      $out.='<tr><td class="inform server">Your security certificate has problems</td></tr>';
    }elseif($ssl<70){
      $out.='<tr><td class="warn server">Your security certificate has major problems</td></tr>';
    }

    $plugins_active = $decoded_scan['scores']['plugins_active'];
      if($plugins_active>50 && $plugins_active<80){
      $out.='<tr><td class="inform plugin">There are '.$decoded_scan['plugin_amount'].'  plugins active</td></tr>';
    }elseif($plugins_active<50){
      $out.='<tr><td class="warn plugin">There are '.$decoded_scan['plugin_amount'].'  plugins active, that is too many</td></tr>';
    }

    $plugins_outdated= $decoded_scan['scores']['plugins_outdated'];
      if($plugins_outdated>50 && $plugins_outdated<80){
      $out.='<tr><td class="inform plugin">There are '.$decoded_scan['plugin_outdated'].' plugins needing aaaa updates</td></tr>';
    }elseif($plugins_outdated<50){
      $out.='<tr><td class="warn plugin">There are '.$decoded_scan['plugin_outdated'].' plugins needing updates, that is too many</td></tr>';
    }

    $plugins_abandoned= $decoded_scan['scores']['plugins_abandoned'];
    if($plugins_abandoned>50 && $plugins_abandoned<90){
    $out.='<tr><td class="inform plugin">There are '.$decoded_scan['plugin_noupdates'].' plugins which may have been abandoned by their authors</td></tr>';
    }elseif($plugins_abandoned<50){
      $out.='<tr><td class="warn plugin">There are '.$decoded_scan['plugin_noupdates'].' plugins which may have been abandoned by their authors</td></tr>';
    }


    $serverPHP= $decoded_scan['scores']['serverPHP'];
    if($serverPHP>50 && $serverPHP<100){
      $out.='<tr><td class="inform server">Your server core components are outdated</td></tr>';
    }elseif($serverPHP<50){
      $out.='<tr><td class="warn server">Your server core components are very outdated</td></tr>';
    }


    $out.=('</table>');

    return $out;
}
