<?php
/*
  Plugin Name: Opal Site Scanner
  Plugin URI: OpalSphere.com
  Author: Opalsphere
  Version: 0.1
  Author URI:http://pheriche.com
 */
 /*
GET active plugins.
http://phasionistasa.co.za/kiwix/wordpress.stackexchange.com_en_all_2019-02/A/question/298251.html


include_once('includes/phua_add_wp_signon.php' );
include_once('includes/phua_add_user_register.php' );
include_once('includes/phua_add_org_expired.php' );
include_once('includes/phua_add_user_download_resource.php' );
include_once('includes/phua_add_wp_head.php' );
*/
/*************************************************/

add_action('admin_menu', 'register_opalscan_menu');
function register_opalscan_menu() {
	add_menu_page( 'Opal Site Scan', 'Opal Site Scan', 'manage_options', 'opal-site-scan', 'phua_admin_page_output' );
	#add_action('admin_init', 'save_log_page_items', 10); // if you need to save something weird this might be useful.
}
/*
function phua_enqueue_scripts( ) {
wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	wp_enqueue_script( 'datepicker_script', plugin_dir_url( __FILE__ ) . 'includes/datepicker.js' );

	wp_enqueue_script( 'log_search', plugin_dir_url( __FILE__ ) . 'includes/pher-logsearch-admin.js', array( 'jquery' ),false,true );
	wp_localize_script( 'log_search', 'ph_log_search', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),  ));
	wp_register_style( 'phua_admin_css', plugin_dir_url( __FILE__ ) . '/includes/phua_logging_admin.css', false, '1.0.0' );
	wp_enqueue_style( 'phua_admin_css' );
}

add_action('admin_enqueue_scripts', 'phua_enqueue_scripts');
*/

add_action( 'wp_ajax_do_some_ajax', 'phua_log_ajax_output');


function phua_admin_page_output(){
		echo '<div class="wrap"><div id="icon-edit-pages" class="icon32"></div><h2>Opal Site Scanner</h2>';

			echo('<div class="opalscanbarholder">Scanning <div class="opalscanbar"></div></div>');

		echo('<form method="post" id="log_export_form" >');
		 echo('<hr>');
		echo('<h3 id="loggingdateheader" class="loggingsectionheaditem">Date Range</h3>');
		 $time = strtotime("-1 year", time());
 		 $a_year_ago = date("Y-m-d", $time);
		 $today = date("Y-m-d");

		/*
		$screen = get_current_screen();
		echo('<pre>');print_r($screen );echo('</pre>');
		*/
		 $atts = array('title'=>'Authorities','formslug'=>'organisation');
		//display_logging_row($atts);

		 $atts = array('title'=>'Resources','formslug'=>'resources');
		//display_logging_row($atts);

		echo '<input type="hidden" name="download-login-log" value="true" >';
		echo (wp_nonce_field('ualog_download'));

		echo '<p><input type="submit" name="submit" id="submit" class="button bigwhitebutton" value="Export to Excel"></p>';
		echo(' </form');
		echo('</div>');


		displayPluginsVersions();
}



#
if(is_admin()) { // make sure, the following code runs in back end

	if (!function_exists('plugins_api')) {
		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	}

		$vuln_score=array();
    // returns version of the plugin represented by $slug, from repository

    function getPluginVersionFromRepository($slug) {
			/*
			$url = "https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slugs][]={$slug}";
			$response = wp_remote_get($url); // WPOrg API call
			$plugins = json_decode($response['body']);
*/

				$call_api = plugins_api( 'plugin_information', array( 'slug' => $slug , 'version' => true,) );
				$version = $call_api->version;
			/*echo('<pre>');
			print_r($plugins);
			echo('</pre>');*/
			        // traverse $response object
			        /*foreach($plugins as $key => $plugin) {
			            $version = $plugin->version;
									//$versions = $plugin->versions;
								//	$last_updated =$plugin->last_updated;
			        }*/

			    return $version;
    }

    // dashboard widget's callback
    function displayPluginsVersions() {
        $allPlugins = get_plugins(); // associative array of all installed plugins
        $activePlugins = get_option('active_plugins'); // simple array of active plugins

        // building active plugins table
        echo '<table width="100%">';
        echo '<thead>';
        echo '<tr>';
        echo '<th width="20%" style="text-align:left">Plugin</th>';
        echo '<th width="20%" style="text-align:left">currVer</th>';
        echo '<th width="20%" style="text-align:left">repoVer</th>';
				echo '<th width="20%" style="text-align:left">WARN</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // traversing $allPlugins array
        foreach($allPlugins as $key => $value) {
          //  if(in_array($key, $activePlugins)) { // display active only
                echo '<tr>';
                echo "<td>{$value['Name']}</td>";
                echo "<td>{$value['Version']}</td>";
                $slug = explode('/',$key)[0]; // get active plugin's slug
                // get newest version of active plugin from repository
                $repoVersion = getPluginVersionFromRepository($slug);
                echo "<td>{$repoVersion}</td>";
								if($repoVersion>$value['Version']){$issueflag = 'ISSUE';}else{$issueflag ='';}
								 echo "<td>$issueflag</td>";
                echo '</tr>';
          //  }
        }
        echo '</tbody>';
        echo '</table>';
    }

    // widget's registration
    function fpwAddDashboardWidget() {
        wp_add_dashboard_widget(
            'active_plugins_versions', // widget's ID
            'Active Plugins Versions', // widget's title
            'activePluginsVersions'    // widget's callback (content)
        );
    }
    add_action('wp_dashboard_setup', 'fpwAddDashboardWidget');
}
