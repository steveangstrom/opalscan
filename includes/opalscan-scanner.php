<?php
/** POPULATE the variables, arrays and data from a scane of the site **/
if(is_admin()) { // make sure, the following code runs only in the back end


  function opalscan_get_scan(){ // the main scan and data populating function

		if (!function_exists('plugins_api')) {
			require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		}

	    /** set the baselines for the scores **/
		$scan_results = array(
			"plugin_outated"=>0,// is the installed plugin outdated?
			"plugin_noupdates"=>0, // are there no recent updates?
			"plugin_amount" =>0, // are there too many plugins?
			"php_version" =>0,
			"sql_version" =>0,
			"wp_version" =>0,
			"ssl" =>0,
      "allPlugins"=>'',
		);


		/** get some information **/
    $allPlugins = get_plugins(); // associative array of all installed plugins
    $activePlugins = get_option('active_plugins'); // simple array of active plugins

		$scan_results["php_version"] =  phpversion();
		$scan_results["sql_version"] =  mysqli_get_server_info(mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME));
		$scan_results["wp_version"] =   get_bloginfo( 'version' );
		$scan_results["ssl"] =   is_ssl();

		/* get SQL version */
		$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$SQLversion = mysqli_get_server_info($connection);
		/***********/
    $allPlugins =  get_plugins();// associative array of all installed plugins



    // populate the update status array.
    foreach($allPlugins as $key => $value) {
      // scan each plugin for status.
      $slug = explode('/',$key)[0]; // get active plugin's slug
      $call_api = getPluginVersionFromRepository($slug); // go check this particular plugin. // takes time, so comment out for debug.
      $repoversion = $call_api->version;

      if($repoversion>$value['Version']){ // newer repo version available
        $allPlugins[$key]['plugin_outdated']='Outdated '.$slug;
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
        $allPlugins[$key]['plugin_noupdates']='No recent updates for '.$slug;
      }

      //  $allPlugins[$key]['plugin_outated']='status test for '.$slug;
    }// end foreach

    $scan_results["allPlugins"] =  $allPlugins; // add all the changes and additions to the plugin array.
    $scan_results["scanDate"] =  $today;


    // NOW SAVE IT TO A LOG FILE WHICH CAN BE PARSED, RENDERED  OR POSTED **/
    $scanlog = fopen(plugin_dir_path( __DIR__ ) . "reports/scanlog.txt", "w"); // store a raw copy.
    //fwrite($scanlog, $scan_results);
    //$logparse = print_r($scan_results, true);
    //$logparse = var_export($scan_results, true);
    fwrite($scanlog, json_encode($scan_results));
    fclose($scanlog);

		return $scan_results;

	}  //  ----------end opalscan_get_scan() ---------------------

  // returns version of the plugin represented by $slug, from repository
  function getPluginVersionFromRepo($slug) {
    $call_api = plugins_api( 'plugin_information', array( 'slug' => $slug , 'version' => true,) );
    return $call_api;
  }



  function opalscan_show_scan(){ // show previous scan. including summary
    echo('<h2>Scan Results</h2>');
    echo('<h4>this scan is from the past, scan again to update</h4>');

    $raw_scan = file_get_contents(plugin_dir_path( __DIR__ ) . "reports/scanlog.txt");
    opalscan_render_html($raw_scan);
//	$scan_results = opalscan_get_scan();
	/*	echo('<pre>');
		print_r($raw_scan);
		echo('</pre>');
*/
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

function opalscan_render_html($raw_scan){
	// this can be called from the AJAX , or it can be used to create the HTML file which is sent to the receipients.
  $decoded_scan = json_decode($raw_scan,true);

  $log_date = strtotime($decoded_scan['scanDate']['date']);

  echo '<h3>Scan Date '.date('l dS \o\f F Y h:i:s A', $log_date).'</h3>';
//  echo '<h3>Scan Date '.$decoded_scan['scanDate']['date'].'</h3>';

  echo('<pre>');
  //print_r($raw_scan);
  print_r(  $decoded_scan);
  echo('</pre>');


}
