
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

    $score = opal_do_score($decoded_scan);

    # Display results
    $out.= '<div id = "opalscanner_results" class="opalscanner_results">
    <div class="opal_tab_bar">
      <div class="opal_tab active" data-tab="opalsummary">Summary</div>
      <div class="opal_tab" data-tab="opalreport">Report</div>
    </div>';

    $out.='<div id = "opalsummary" class= "opal_pane active">';

    $scandate = date('dS \o\f F Y g:i A', $log_date);
        if($livescan===false){
            $out.='<div class="opal_infobox"><p>Displaying previous scan ('.$scandate.') <a class="opaldoscan">scan again</a> to update</p></div>'; // a  conditional checks if this display is from an old log, or a live AJAX request.
        }


/****** top score and summary block *****/
    $out.='<div class="summary_wrap">';

      $out.='<div class="opalscore_wrap">
        <div class = "opalscore score s'.round($score/10).'0"><span>SCORE '.$score.'%</span></div>
        <div class="deco s10"></div><div class="deco s20"></div><div class="deco s30"></div><div class="deco s40"></div><div class="deco s50"></div><div class="deco s60"></div><div class="deco s70"></div><div class="deco s80"></div><div class="deco s90"></div><div class="deco s100"></div></div>';

      //$score_rating=$scorewords[round($score/10)-1];
    //  $out.= 'Your site scored '.$score.' out of a possible 100.   Your site safety is rated as '.$scorewords[round($score/10)-1].'   ... ' . round($score/10);

    //$out.='<div class="rated_summary"><h2><span class="opal_dimmed">Rated:</span> '.$scorewords[round($score/10)-1].'</h2><p>test test</p></div>';
    $out .= opal_summary($score);
    $out.='</div>';#end summary wrapper

  /* --- describe plugin state verbally -----*/
  # this function is passed the entire decoded scan. 
    $out .= opal_advice($decoded_scan, $score);
    $out.= '</div>';// end summary tab content


  /* -----RENDER THE SCORE RESULT TABLES ---*/
    $out.='<div id = "opalreport" class = "opal_pane">';
    $out.=('<h2>Wordpress and Server</h2>');
    $out.=('<table class="opalscan_results_table">');
    $out.=('<thead><tr><th>Element</th> <th>Installed</th><th>Status</th></tr></thead>');
    $wpstatus = 'OK';
    if ($decoded_scan['scores']['wp']>0){$wpstatus = 'Attention';}
    if ($decoded_scan['scores']['wp']>10){$wpstatus = 'Urgent';}
    $out.=('<tr><td>Wordpress Core Version</td><td>'.$decoded_scan['wp_version'].' ( Avaliable '.$decoded_scan['wp_version_available'].' )</td><td>'.$wpstatus.'</td></tr>');

    if (strlen($decoded_scan['wp_plugin_security'])>2){$secstatus = 'OK';}else{ $secstatus = 'Attention';}
    $out.=('<tr><td>Wordpress Security</td><td>'.$decoded_scan['wp_plugin_security'].' </td><td>'.$secstatus.'</td></tr>');

    $pstatus = 'OK';
    if ($decoded_scan['plugin_amount']>10){$pstatus = 'Attention';}
    if ($decoded_scan['plugin_amount']>15){$pstatus = 'Urgent';}
    $out.=('<tr><td>Plug-ins Installed</td><td>'.$decoded_scan['plugin_amount'].'</td><td>'.$pstatus.'</td></tr>');

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
    if ($decoded_scan['scores']['server']>10){$phpstatus = 'Attention';}
    if ($decoded_scan['scores']['server']>20){$phpstatus = 'Urgent';}
    $out.=('<tr><td>Web Server</td><td>PHP Version '.$decoded_scan['php_version'].'</td><td>'.$phpstatus.'</td></tr>');

    $sqlstatus = 'OK';
    //if ($decoded_scan['sql_version']==0){$phpstatus = 'Attention';}
    $out.=('<tr><td>SQL Server</td><td>SQL Version '.$decoded_scan['sql_version'].'</td><td>'.$sqlstatus.'</td></tr>');


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

        $outstatus = $value['plugin_outdated']? 'Needs Update' : 'Current';
        $out.= '<td>'.$outstatus.'</td>';

      //  $updstatus = $value['plugin_noupdates']? 'Abandoned' : 'Ok';
      $updstatus = 'OK';
      if ($value['plugin_noupdates'] >11){$updstatus = 'Outdated';}
      if ($value['plugin_noupdates'] >20){$updstatus = 'Abandoned!';}

        $out.= '<td>'.$updstatus.'</td></tr>';
    }
      $out.=('</table>');
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


  function opalscan_show_scan(){ // show previous scan, from the log  including summary
    $JSON_scan = file_get_contents(plugin_dir_path( __DIR__ ) . "reports/scanlog.txt");
    opalscan_render_html($JSON_scan);// render the array as HTML table.
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

/********** this is the function that uipdates the status display *********/
/*  function opalscan_statusupdater($status='') {
      $out ='this is a test from the place beyond';
      echo $out;
      die();
  }
  add_action( 'wp_ajax_scanstatusupdate', 'opalscan_statusupdater' );
*/



/**

https://stackoverflow.com/questions/14918462/get-response-from-php-file-using-ajax

https://wisdmlabs.com/blog/create-real-time-progress-bar-using-jquery-ui-ajax/

****/
}
