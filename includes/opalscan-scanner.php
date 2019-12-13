<?php
/** POPULATE the variables, arrays and data from a scane of the site **/
if(is_admin()) { // make sure, the following code runs only in the back end


  function opalscan_get_scan(){ // the main scan and data populating function

		if (!function_exists('plugins_api')) {
			require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		}

	    /** set the baselines for the scores **/
		$scan_results = array(
			"plugin_outdated"=>0,// is the installed plugin outdated?
			"plugin_noupdates"=>0, // are there no recent updates?
			"plugin_amount" =>0, // are there too many plugins?
      "plugin_active_amount" =>0, // are there too many plugins?
			"php_version" =>0,
			"sql_version" =>0,
			"wp_version" =>0,
      "wp_version_available" =>0,
			"ssl" =>0,
      "allPlugins"=>'',
		);


		/** get some information **/
    $allPlugins = get_plugins(); // associative array of all installed plugins
    $activePlugins = get_option('active_plugins'); // simple array of active plugins
    $scan_results["plugin_active_amount"] = count($activePlugins);

		$scan_results["php_version"] =  phpversion();
		$scan_results["sql_version"] =  mysqli_get_server_info(mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME));
		$scan_results["wp_version"] =   get_bloginfo( 'version' );

    /* store the most recent available version of WP */
    $url = 'https://api.wordpress.org/core/version-check/1.7/';
    $response = wp_remote_get($url);
    $json = $response['body'];
    $obj = json_decode($json);
    $scan_results["wp_version_available"] = $obj->offers[0]->version;
    /*-----------*/

		$scan_results["ssl"] =   is_ssl();

		/* get SQL version */
		$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$SQLversion = mysqli_get_server_info($connection);


		/***********/
    $allPlugins =  get_plugins();// associative array of all installed plugins


    // populate the update status array.
    foreach($allPlugins as $key => $value) {

      $scan_results['plugin_amount']+=1;
      // scan each plugin for status.
      $slug = explode('/',$key)[0]; // get active plugin's slug
      $call_api = getPluginVersionFromRepository($slug); // go check this particular plugin. // takes time, so comment out for debug.
      $repoversion = $call_api->version;

      if($repoversion>$value['Version']){ // newer repo version available
        $allPlugins[$key]['plugin_outdated']= true;
        $scan_results['plugin_outdated']+=1; // update the main tally  of  plugin_outdated.
      }

      /* Date and update status of plugins compared to repo */
      $today= new DateTime();
      $datetime2 = new DateTime('2009-10-13');

      $last_updated = $call_api->last_updated;
      $last_updated_date = new DateTime($last_updated );
      $interval = $today->diff($last_updated_date);
      $intervalstring= $interval->format('%R%a');
      $intervalINT=intval($intervalstring);

      if ($intervalINT <= -365){
        //also add a += to the overall score table perhaps.
        $allPlugins[$key]['plugin_noupdates']=true;
        $scan_results['plugin_noupdates']+=1; // update the main tally  of no updates.
      }

      //  $allPlugins[$key]['plugin_outated']='status test for '.$slug;
    }// end foreach

    $scan_results["allPlugins"] =  $allPlugins; // add all the changes and additions to the plugin array.
    $scan_results["scanDate"] =  $today;

    opal_save_to_log($scan_results);//saves the log to a file for cache, and distribution to opalsupport

		return $scan_results;

	}  //  ----------end opalscan_get_scan() ---------------------

function opal_save_to_log($scan_results){
  // NOW SAVE IT TO A LOG FILE WHICH CAN BE PARSED, RENDERED  OR POSTED **/
  $scanlog = fopen(plugin_dir_path( __DIR__ ) . "reports/scanlog.txt", "w"); // store a raw copy.
  fwrite($scanlog, json_encode($scan_results));
  fclose($scanlog);

}


  // returns version of the plugin represented by $slug, from repository
  function getPluginVersionFromRepo($slug) {
    $call_api = plugins_api( 'plugin_information', array( 'slug' => $slug , 'version' => true,) );
    return $call_api;
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

/****** RENDER THE DATA AS HTML ********/
function opalscan_render_html($raw_scan, $livescan=true){
	// this can be called from the AJAX , or it can be used to create the HTML file which is sent to the receipients.

  $out='';
  $score =100;
  $scorewords=['Extremely bad','Extremely bad', 'Very bad','Bad','Adequate','Needs Attention','Needs Attention','Good','Very Good','Excellent'];

  $decoded_scan = json_decode($raw_scan,true);

  $log_date = strtotime($decoded_scan['scanDate']['date']);
  $out.='<h3>Scan Date '.date('l dS \o\f F Y h:i:s A', $log_date).'</h3>';

  if($livescan===false){
      $out.='<h4>this scan is from the past, scan again to update</h4>'; // a  conditional checks if this display is from an old log, or a live AJAX request.
  }

/* Debuggery */
  $out.= '[plugin_outdated]'.$decoded_scan['plugin_outdated'];
  $out.= '<br>[plugin_noupdates]'.$decoded_scan['plugin_noupdates'];
  $out.= '<br>[plugin_amount]'.$decoded_scan['plugin_amount'];
  $out.= '<br>[plugin_active_amount]'.$decoded_scan['plugin_active_amount'];

  $out.= '<br>[php_version]'.$decoded_scan['php_version'];
  $out.= '<br>[sql_version]'.$decoded_scan['sql_version'];
  $out.= '<br>[wp_version]'.$decoded_scan['wp_version'];
  $out.= '<br>[wp_version_available]'.$decoded_scan['wp_version_available'];
  $out.= '<br>[ssl]'.$decoded_scan['ssl'];

/* --- Do a Score ---*/
  #Wp score
  $wp_score = 10;

  #Server score
  $server_score = 10;

  # plugin score
  $inactive_plugin_total = $decoded_scan['plugin_amount'] - $decoded_scan['plugin_active_amount'];
  $plugin_score = $decoded_scan['plugin_outdated'] + $decoded_scan['plugin_noupdates'] + ($decoded_scan['plugin_amount']/2) + ($inactive_plugin_total/2) ;

  # main score
  $score -= ($wp_score + $plugin_score +  $server_score);
  $score = round($score);

  # Display Score
  $out.= '<div class = "opalscore score s'.round($score/10).'0"><span>'.$score.'</span></div>';
  $score_rating=$scorewords[round($score/10)-1];
  $out.= 'Your site scored '.$score.' out of a possible 100.   Your site safety is rated as '.$scorewords[round($score/10)-1].'   ... ' . round($score/10);

/* --- describe plugin state verbally -----*/
  $advice = "<h2>Security Advice</h2><p>Your site has security and maintenance problems which must be addressed. Your scan score is rated as $score_rating and this means you are vulnerable to attacks, or your website may fail.<p> ";
  $out.=  $advice ;
/* -----SHOW TABLES ---*/
  $out.=('<table class="opalscan_results_table">');
  $out.=('<thead><tr><th>Element</th> <th>Installed Version</th><th>Status</th></tr></thead>');
  $out.=('<tr><td>Wordpress Core</td><td>'.$decoded_scan['wp_version'].'</td><td>Outdated</td></tr>');
  $out.=('<tr><td>Plug-ins</td><td>'.$decoded_scan['plugin_amount'].'</td><td>Needs Attention</td></tr>');
  $out.=('<tr><td>Web Server</td><td>'.$decoded_scan['plugin_amount'].'</td><td>Needs Attention</td></tr>');
  $out.=('<tr><td>Security</td><td>'.$decoded_scan['plugin_amount'].'</td><td>Needs Attention</td></tr>');
  $out.=('</table>');


  $allPlugins = $decoded_scan['allPlugins'];
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
