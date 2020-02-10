<?php
namespace opalscan;
use \DateTime;
if(is_admin()) {
  include_once('opalscan-render.php' ); # get the admin display methods
  include_once('opalscan-advice.php' ); # textualised advice in human form

  function opalscan_get_scan(){
    # the main site and plugin scanner and data populating function
    # it scans the core, themes, plugins, SSL status and populates a log

		if (!function_exists('plugins_api')) {
      # we need some core functions for plugin scanning
			require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		}

	    /** set the baselines for the scores **/
		$scan_results = array(
			'plugin_outdated'=>0,// is the installed plugin outdated?
			'plugin_noupdates'=>0, // are there no recent updates?
			'plugin_amount' =>0, // are there too many plugins?
      'plugin_active_amount' =>0, // are there too many plugins?
      'theme_amount' =>0, // are there too many themes?
      'theme_outdated'=>0, // how many outdated themes
			'php_version' =>0,
			'sql_version' =>0,
      'sql_size'=>0,
      'wp_URL' =>0,
			'wp_version' =>0,
      'wp_version_available' =>0,
			'ssl' =>0,
      'allPlugins'=>'',
      'allThemes'=>'',
      'wp_plugin_security'=>'',// what wp security plugin are they using.
      'scores'=>array(),
		);


    $plugin_data = get_plugin_data( dirname(__DIR__).'/opalscan.php' );
    $scan_results['opalscanner_version']=$plugin_data['Version'];


		/** ----------------- Get some information about the site --------------------------**/
    $scan_results["wp_URL"] = get_site_url();
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
    /* populate the SSL sub-array with data about the cert, expiry, issuer, etc */
    $scan_results["ssl"] = SSLcheck();

		/* get SQL version */
		$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$SQLversion = mysqli_get_server_info($connection);

		/* get SQL WP database size just to see if there's size creep from a bad plugin, or options */
    $dbSize = calculate_database_size();
    $dbsizestring = $dbSize['size'];
    $scan_results["sql_size"] = $dbsizestring;

    /***** SCAN PLUGINS *****************************************************/
    $allPlugins =  get_plugins();// WPs own func getting an associative array of all installed plugins
    $how_many_plugins = count($allPlugins);
    $progress=1;

    // populate the plugin updatedness status array
    $today= new DateTime();
    $scan_results["scanDate"] =  $today;

    foreach($allPlugins as $key => $value) {
      $scan_results['plugin_amount']+=1;
      // scan each plugin for status.
      $sluga = explode('/',$key); // get active plugin's slug
  		$slug = $sluga[0]; // get active plugin's slug

      # As we iterate through this loop, go and update the status, this status can be checked by AJAX for UI
      opal_update_status($slug, $progress, $how_many_plugins);
      $progress++;

      $call_api = getPluginVersionFromRepository($slug); // go check this particular plugin. // takes time, so comment out for debug.
      $repoversion = $call_api->version;
      $allPlugins[$key]['plugin_repo_version']= $repoversion;
      $allPlugins[$key]['plugin_installed_version']= $value['Version'];

      $allPlugins[$key]['plugin_repo_version']= $repoversion;
      $allPlugins[$key]['plugin_installed_version']= $value['Version'];

      if($repoversion>$value['Version']){ // newer repo version available
        $allPlugins[$key]['plugin_outdated']= true;
        $scan_results['plugin_outdated']+=1; // update the main tally  of  plugin_outdated.
      }

      /* Date and update status of plugins compared to repo */

      $last_updated = $call_api->last_updated;
      $last_updated_date = new DateTime($last_updated );
      $interval = $today->diff($last_updated_date);
      $intervalstring= $interval->format('%R%a');
      $intervalINT=intval($intervalstring);

      if ($intervalINT <= -950){
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

      // is this a security plugin? If so is it actually active?
        $scan_results['wp_plugin_security'] =  detect_plugin_security($slug, $scan_results['wp_plugin_security'],$key ); // check, if already populated keep it.

    }// end foreach

    $scan_results["activePlugins"] =  $activePlugins;
    $scan_results["allPlugins"] =  $allPlugins; // add all the changes and additions to the plugin array.

    /***** SCAN THE THEMES   *****************************************************/
    $all_themes =  wp_get_themes();// associative array of all installed themes
    $how_many_themes = count($all_themes);
    $scan_results["theme_amount"] = $how_many_themes;

    $theme_info=array();
    foreach($all_themes as $key => $value){
      $sluga = explode('/',$key);
      $slug = $sluga[0]; // get theme's slug this compat for old php version
      $call_api = getThemeVersionFromRepository($slug); // go check this particular theme.
      $repoversion = $call_api->version;
      $theme_data = wp_get_theme($slug);// Iterate thru the themes
      $theme_version = $theme_data->get( 'Version' );

      $theme_info[$slug]['installed_version']=$theme_version;
      $theme_info[$slug]['repo_version']=$repoversion;

      if (isset($repoversion) && $repoversion>$theme_version){
        $theme_info[$slug]['theme_outdated']='1';
        $scan_results['theme_outdated']+=1; // update the count of outdated themes
      }// is the installed theme in need of updating from the repo.

      $theme_info[$slug]['theme_noupdates']='1'; // make use of the date elapsed since repo update code from plugin
      # if plugin_outdated then push plugin_outdated
    }
    $scan_results["allThemes"] =  $theme_info;

  /***** END THEMES   *****************************************************/

  /******  SCORING populate the log with the calculated and weighted scores as a cache ----- */
    $scan_results['scores']['wpcore'] = calculate_wp_score($scan_results);
    $scan_results = calculate_plugin_score($scan_results);
    $scan_results = calculate_theme_score($scan_results);
    $scan_results = calculate_server_score($scan_results);

    opal_save_to_log($scan_results);//saves the log to a file for cache, and distribution to opalsupport

    delete_option('opalsupport_scan_status',' ');# empty the scan status bar option.
		return $scan_results;
	}  //  ----------end opalscan_get_scan() ---------------------



  function opal_update_status($slug,$progress, $total){
    # Writes the current scan status to an ioption in the DB, which can be queried via AJAX to show a status bar UI
    $status_array['slug']= $slug;
    $status_array['progress']= $progress;
    $status_array['total']= $total;
    $JSON_status = json_encode($status_array);
    update_option('opalsupport_scan_status',$JSON_status);
  }

  function opal_get_scan_status(){
    # get the option for the status of this scan. then send it back to the AJAX for UI of status bar
    # this returns a JSON array, so we can see the name of the plugin being scanned.
    $out = get_option( 'opalsupport_scan_status' );
    wp_send_json($out);
    die();
  }
  add_action( 'wp_ajax_opalscan_scanstatus_request', 'opalscan\opal_get_scan_status' );


  function opal_save_to_log($scan_results){
    # Writes the current scan to a text log file and an HTML version.
    $old_filename = get_option( 'opalsupport_log_location' );

    # Check - has there ever been a scan previously? If so there's probably an Option saved and we should delete the files before proceeding.
    if (  isset($old_filename)   &&   file_exists(plugin_dir_path( __DIR__ ) . "reports/opalscan-$old_filename.log")){
      unlink(plugin_dir_path( __DIR__ ) . "reports/opal-scanner-report-$old_filename.html"); // delete the old HTML file
      unlink(plugin_dir_path( __DIR__ ) . "reports/opalscan-$old_filename.log"); // delete the old log file
    }

    //  SAVE RESULTS TO A LOG FILE WHICH CAN BE PARSED, RENDERED OR RETURNED **/
    $randomised_filename = wp_generate_password( 8, false );
    update_option('opalsupport_log_location',$randomised_filename, false);
    $JSON_scan = json_encode($scan_results);
    $scanlog = fopen(plugin_dir_path( __DIR__ ) . "reports/opalscan-$randomised_filename.log", "w"); // store a raw copy.
    fwrite($scanlog, $JSON_scan);
    fclose($scanlog);

    /** Write a HTML file **/
    $htmlfile = fopen(plugin_dir_path( __DIR__ ) . "reports/opal-scanner-report-$randomised_filename.html", "w"); // store a raw copy.
    $html_content =   opalscan_render_html($JSON_scan, true, true);

    fwrite($htmlfile, $html_content);
    fclose($htmlfile);
  }

  function getPluginVersionFromRepository($slug) {
      $call_api = plugins_api( 'plugin_information', array( 'slug' => $slug , 'version' => true,) );
      return $call_api;
  }

  function getThemeVersionFromRepository($slug) {
      $call_api = themes_api( 'theme_information', array( 'slug' => $slug , 'version' => true,'last_updated' => true) );
      return $call_api;
  }

}
