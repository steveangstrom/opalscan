<?php
if(is_admin()) {
  include_once('opalscan-render.php' ); # get the admin display methods
  include_once('opalscan-advice.php' ); # textualised advice in human form

  function opalscan_get_scan(){ // the main scan and data populating function
  //  global $allPlugins;


		if (!function_exists('plugins_api')) {
			require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		}

	    /** set the baselines for the scores **/
		$scan_results = array(
			'plugin_outdated'=>0,// is the installed plugin outdated?
			'plugin_noupdates'=>0, // are there no recent updates?
			'plugin_amount' =>0, // are there too many plugins?
      'plugin_active_amount' =>0, // are there too many plugins?
			'php_version' =>0,
			'sql_version' =>0,
      'sql_size'=>0,
      'wp_URL' =>0,
			'wp_version' =>0,
      'wp_version_available' =>0,
			'ssl' =>0,
      'allPlugins'=>'',
      'wp_plugin_security'=>'',
      'scores'=>array(),
		);

    $scan_results["wp_URL"] = get_site_url();
		/** ----------------- Get some information about the site --------------------------**/
    $scan_results["opalscanner_version"] = '0.1';

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
    $dbSize = calculate_database_size();
    $dbsizestring = $dbSize['size'];
    //$dbsizestring.=$dbSize['size'].$dbSize['type'];
    $scan_results["sql_size"] = $dbsizestring;

    /*********/
    $allPlugins =  get_plugins();// associative array of all installed plugins
    $how_many_plugins = count($allPlugins);
    $progress=1;

    // populate the plugin updatedness status array.
    foreach($allPlugins as $key => $value) {
      $scan_results['plugin_amount']+=1;
      // scan each plugin for status.
      $slug = explode('/',$key)[0]; // get active plugin's slug

      // write the status to a file.
    //  $scan_percent= ' | Completed '.$p.' of '.$how_many_plugins;
      opal_update_status($slug, $progress, $how_many_plugins);
      $progress++;

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


      if ($intervalINT <= -950){
        //also add a += to the overall score table perhaps.
        $allPlugins[$key]['plugin_noupdates']=36;
        $scan_results['plugin_noupdates']+=1; // update the main tally  of no updates.
      }elseif ($intervalINT <= -730){
        $allPlugins[$key]['plugin_noupdates']=24;
        $scan_results['plugin_noupdates']+=1; // update the main tally  of no updates.
      }
      elseif ($intervalINT <= -560){
        $allPlugins[$key]['plugin_noupdates']=18;
        $scan_results['plugin_noupdates']+=1; // update the main tally  of no updates.
      }
      elseif ($intervalINT <= -360){
        $allPlugins[$key]['plugin_noupdates']=12;
        $scan_results['plugin_noupdates']+=1; // update the main tally  of no updates.
      }

      // is this a security plugin?
        $scan_results['wp_plugin_security'] =  detect_plugin_security($slug, $scan_results['wp_plugin_security'] ); // check, if already populated keep it.

      //  $allPlugins[$key]['plugin_outated']='status test for '.$slug;
    }// end foreach


    $scan_results["allPlugins"] =  $allPlugins; // add all the changes and additions to the plugin array.
    $scan_results["scanDate"] =  $today;

    /* ----- populate the log with the calculated and weighted scores as a cache ----- */
    $scan_results['scores']['wpcore'] = calculate_wp_score($scan_results);
    $scan_results['scores']['plugins'] = calculate_plugin_score($scan_results);
    $scan_results['scores']['serverPHP'] = calculate_serverPHP_score($scan_results);

    opal_save_to_log($scan_results);//saves the log to a file for cache, and distribution to opalsupport
    unlink(plugin_dir_path( __DIR__ ) . 'reports/scanstatus.txt'); // empty the scan status file.
		return $scan_results;
	}  //  ----------end opalscan_get_scan() ---------------------


  // utility function which returns the available version of the plugin represented by $slug, from repository
  function getPluginVersionFromRepo($slug) {
    $call_api = plugins_api( 'plugin_information', array( 'slug' => $slug , 'tested' => true,) );
    return $call_api;
  }

  function opal_update_status($slug,$progress, $total){ // writes the current scan status to a file.
  //  $status = $status .' '.$progress. ' of '.$total;
  $status_array['slug']= $slug;
  $status_array['progress']= $progress;
  $status_array['total']= $total;
    $JSON_status = json_encode($status_array);
    file_put_contents(plugin_dir_path( __DIR__ ) . "reports/scanstatus.txt", $JSON_status);
  }

  function opal_save_to_log($scan_results){
    //  SAVE RESULTS TO A LOG FILE WHICH CAN BE PARSED, RENDERED  OR POSTED **/
    $scanlog = fopen(plugin_dir_path( __DIR__ ) . "reports/opalscan.log", "w"); // store a raw copy.
    fwrite($scanlog, json_encode($scan_results));
    fclose($scanlog);
  }




}
