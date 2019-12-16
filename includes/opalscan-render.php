
<?php
if(is_admin()) { // make sure, the following code runs only in the back end

  /****** RENDER THE DATA AS HTML ********/
  function opalscan_render_html($raw_scan, $livescan=true){
  	// this can be called from the AJAX , or it can be used to create the HTML file which is sent to the receipients.

    $out='';
    $score =100;
    $scorewords=['Extremely bad','Extremely bad', 'Very bad','Bad','Adequate','Needs Attention','Needs Attention','Good','Very Good','Excellent'];

    $decoded_scan = json_decode($raw_scan,true);

    $log_date = strtotime($decoded_scan['scanDate']['date']);


    if($livescan===false){
        $out.='<h4>this scan is from the past, scan again to update</h4>'; // a  conditional checks if this display is from an old log, or a live AJAX request.
    }


  /* --- Do a Score ---*/
    #Wp score
    $wp_score = 10;

    #Server score
    $server_score = 10;

    # plugin score
    $inactive_plugin_total = $decoded_scan['plugin_amount'] - $decoded_scan['plugin_active_amount'];
    $plugin_score = $decoded_scan['plugin_outdated'] + $decoded_scan['plugin_noupdates'] + ($decoded_scan['plugin_amount']/2) + ($inactive_plugin_total/2) ;

    # TOTAL UP THE SCORE AND DESCRIBE IT -----------------------
    $score -= ($wp_score + $plugin_score +  $server_score);
    $score = round($score);

    # Display Score
    $out.= '<div class="opalscanner_results">
    <div class="opal_tab_bar">
      <div class="opal_tab active">Summary</div><div class="opal_tab">Report</div>
    </div>';


    $out.='<div class = "opal_pane">';
    $out.='<h3>Scan Date '.date('l dS \o\f F Y h:i:s A', $log_date).'</h3>';
    $out.=' <div class="opalscore_wrap">
      <div class = "opalscore score s'.round($score/10).'0"><span>SCORE '.$score.' / 100</span></div>
      <div class="deco s10"></div><div class="deco s20"></div><div class="deco s30"></div><div class="deco s40"></div><div class="deco s50"></div><div class="deco s60"></div><div class="deco s70"></div><div class="deco s80"></div><div class="deco s90"></div><div class="deco s100"></div></div>';

    $score_rating=$scorewords[round($score/10)-1];
    $out.= 'Your site scored '.$score.' out of a possible 100.   Your site safety is rated as '.$scorewords[round($score/10)-1].'   ... ' . round($score/10);

  /* --- describe plugin state verbally -----*/
  $advice = '';
    $advice .= "<h2>Security Advice</h2><p>Your site has security and maintenance problems which must be addressed. Your scan score is rated as $score_rating and this means you are vulnerable to attacks, or your website may fail.</p> ";
    $advice .= "<h3>Wordpress Needs Attention</h3><p>your website may fail.</p> ";
    $advice .= "<h3>Plugins</h3><p>your website may fail.</p> ";
    $advice .= "<h3>Web Server</h3><p>your website may fail.</p> ";
    $out.=  $advice ;

    $out.= '</div>';// end summary tab


  /* -----RENDER THE SCORE RESULT TABLES ---*/
    $out.=('<h2>Wordpress and Server</h2>');
    $out.=('<table class="opalscan_results_table">');
    $out.=('<thead><tr><th>Element</th> <th>Installed</th><th>Status</th></tr></thead>');
    $out.=('<tr><td>Wordpress Core Version</td><td>'.$decoded_scan['wp_version'].' ( Avaliable '.$decoded_scan['wp_version_available'].' )</td><td> Status</td></tr>');
    $out.=('<tr><td>Wordpress Security</td><td>'.$decoded_scan['wp_plugin_security'].' </td><td> Status</td></tr>');
    $out.=('<tr><td>Plug-ins Installed</td><td>'.$decoded_scan['plugin_amount'].'</td><td>Status</td></tr>');
    $out.=('<tr><td>Plug-ins Inactive</td><td>'.($decoded_scan['plugin_amount'] - $decoded_scan['plugin_active_amount']).'</td><td>Status</td></tr>');

    $out.=('<tr><td>Plug-ins Outdated</td><td>'.$decoded_scan['plugin_outdated'].'</td><td>Status</td></tr>');
    $out.=('<tr><td>Plug-ins Abandoned</td><td>'.$decoded_scan['plugin_noupdates'].'</td><td>Status</td></tr>');
    $out.=('<tr><td>Web Server</td><td>PHP Version '.$decoded_scan['php_version'].'</td><td>Status</td></tr>');
    $out.=('<tr><td>SQL Server</td><td>SQL Version '.$decoded_scan['sql_version'].'</td><td>Status</td></tr>');
    $ssl = ($decoded_scan['ssl'] == 1) ? 'True' : 'False';
    $out.=('<tr><td>SSL Security</td><td>'.$ssl.'</td><td>Status</td></tr>');
    $out.=('</table>');


    $allPlugins = $decoded_scan['allPlugins'];
    $out.=('<h2>Plug-in Details</h2>');
    $out.=('<table class="opalscan_results_table">');
    $out.=('<thead><tr><th>Plugin</th> <th>Installed Version</th> <th>Status</th> <th>Availability</th></tr></thead>');

    foreach($allPlugins as $key => $value) {
        $out.='<tr><td>'.$value['Title'].'</td>';
        $out.= '<td>'.$value['Version'].'</td>';
        $out.= '<td>'.$value['plugin_outdated'].'</td>';
        $out.= '<td>'.$value['plugin_noupdates'].'</td></tr>';
    }
      $out.=('</table>');
      $out.=('<pre>');
    //print_r($raw_scan);
      $out.=(  print_r($decoded_scan, true));
      $out.=('</pre>');

      echo $out;
  }


  function opalscan_show_scan(){ // show previous scan. including summary
    echo('<h2>Scan Results</h2>');
    $raw_scan = file_get_contents(plugin_dir_path( __DIR__ ) . "reports/scanlog.txt");
    opalscan_render_html($raw_scan);// render the array as HTML table.
  }


  function opalscan_ajax_request() {
      // The $_REQUEST contains all the data sent via ajax
      if ( isset($_REQUEST) ) {
          $scan= $_REQUEST['scan'];

            $scan_results = opalscan_get_scan(); // go get the scan results for a basic check.

          // Let's take the data that was sent and do something with it
          if ( $scan== 'startscan' ) {
              $scan = '<h2>scan results</h2> <p>are here yes,</p> <p><b>big</b> list very sexy .. </p>'.$scan_results["php_version"];
          }else{$scan ='what up';}
          // Now we'll return it to the javascript function
          // Anything outputted will be returned in the response
          echo $scan;
          // If you're debugging, it might be useful to see what was sent in the $_REQUEST
          // print_r($_REQUEST);
      }
     die();
  }
  add_action( 'wp_ajax_opalscan_ajax_request', 'opalscan_ajax_request' );

}
