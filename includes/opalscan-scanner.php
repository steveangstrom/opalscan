<?php
/** POPULATE the variables, arrays and data from a scane of the site **/
if(is_admin()) { // make sure, the following code runs only in the back end


  function opalscan_get_scan(){

		if (!function_exists('plugins_api')) {
			require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		}

	    /** set the baselines for the scores **/
		$scan_results = array(
			"plugin_outated"=>0,
			"plugin_noupdates"=>0,
			"plugin_amount" =>0,
			"php_version" =>0,
			"sql_version" =>0,
			"wp_version" =>0,
			"ssl" =>0,
		);

		// returns version of the plugin represented by $slug, from repository
		function getPluginVersionFromRepo($slug) {
			$call_api = plugins_api( 'plugin_information', array( 'slug' => $slug , 'version' => true,) );
		  return $call_api;
		}
		/** get some information **/

		$scan_results["php_version"] =  phpversion();
		$scan_results["sql_version"] =  mysqli_get_server_info(mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME));
		$scan_results["wp_version"] =   get_bloginfo( 'version' );
		$scan_results["ssl"] =   is_ssl();

		/* get SQL version */
		$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$SQLversion = mysqli_get_server_info($connection);
		/***********/

		$allPlugins = get_plugins(); // associative array of all installed plugins
		$activePlugins = get_option('active_plugins'); // simple array of active plugins

		return $scan_results;
	}// end opalscan_get_scan()


  function opalscan_show_scan(){
    echo('<h2>Scan Results</h2>');
    echo('<h4>this scan is from the past, scan again to update</h4>');

		$scan_results = opalscan_get_scan();
		echo('<pre>');
		print_r($scan_results);
		echo('</pre>');
  }


}
