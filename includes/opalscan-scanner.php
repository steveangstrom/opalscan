<?php
/** SCAN AND SCORE THE ATTRIBUTES - THEN STORE IN A RAW LOG  **/
if(is_admin()) { // make sure, the following code runs only in the back end
include_once('opalscan-render.php' ); /* get the admin display methods */

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
      "wp_plugin_security"=>'',
      "calculated_scores"=>'',
		);


		/** ----------------- Get some information about the site --------------------------**/

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

    // populate the plugin updatedness status array.
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

    //$plugin_security = detect_plugin_security($allPlugins); // done this way for memmory, so we're not duplicating a massive array around..
    $scan_results['wp_plugin_security'] = detect_plugin_security($allPlugins);

    $scan_results["allPlugins"] =  $allPlugins; // add all the changes and additions to the plugin array.
    $scan_results["scanDate"] =  $today;

    opal_save_to_log($scan_results);//saves the log to a file for cache, and distribution to opalsupport

		return $scan_results;
	}  //  ----------end opalscan_get_scan() ---------------------


  // utility function which returns the available version of the plugin represented by $slug, from repository
  function getPluginVersionFromRepo($slug) {
    $call_api = plugins_api( 'plugin_information', array( 'slug' => $slug , 'version' => true,) );
    return $call_api;
  }

  function opal_save_to_log($scan_results){
    //  SAVE RESULTS TO A LOG FILE WHICH CAN BE PARSED, RENDERED  OR POSTED **/
    $scanlog = fopen(plugin_dir_path( __DIR__ ) . "reports/scanlog.txt", "w"); // store a raw copy.
    fwrite($scanlog, json_encode($scan_results));
    fclose($scanlog);
  }

/* ----- currently unused  ----- */
  function detect_plugin_security($allPlugins){
    $needle = array('all-in-one-wp-security-and-firewall','better-wp-security','wp-cerber','wordfence');
     if (in_array($needle, $allPlugins)) {
       foreach ($allPlugins as $item) {
     		if (is_array($item) && array_search($needle, $item)){
          return true;
        }
     	}
    }
    return false;
  }

/* ----- Calculate Scores  ----- */
  function calculate_plugin_score($in){
    

  }

  function calculate_server_score($in){


  }

}
