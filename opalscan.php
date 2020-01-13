<?php
/*
  Plugin Name: Opal Site Scanner
  Plugin URI: OpalSphere.com
  Author: Opalsphere
  Version: 0.1
  Author URI:http://opalsupport.com
 */
include_once('includes/opalscan-calculate-score.php' );
include_once('includes/opalscan-mailer.php' );
include_once('includes/opalscan-dashboard-widget.php' );
include_once('includes/opalscan-scanner.php' );

/*************************************************/
function opalscan_enqueue_scripts( ) {

	wp_enqueue_script( 'opalscan_ajax_display', plugin_dir_url( __FILE__ ) . 'includes/js/opal-scan.js', array( 'jquery' ),false,true );
	wp_localize_script( 'opalscan_ajax_display', 'thescanobj', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'pluginpath' => plugin_dir_url( __FILE__ ) ,'bar' => 'test' ));

	wp_register_style( 'opalscan_admin_css', plugin_dir_url( __FILE__ ) . '/includes/css/opalscanner_admin.css', false, '1.0.0' );
	wp_enqueue_style( 'opalscan_admin_css' );
}

add_action('admin_enqueue_scripts', 'opalscan_enqueue_scripts');


if(is_admin()) { // make sure, the following code runs only in the back end

	if (!function_exists('plugins_api')) {
		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	}

    // returns version of the plugin represented by $slug, from repository
    function getPluginVersionFromRepository($slug) {
				$call_api = plugins_api( 'plugin_information', array( 'slug' => $slug , 'version' => true,) );
			  return $call_api;
    }


		add_action('admin_menu', 'register_opalscan_menu');
		function register_opalscan_menu() {
			add_menu_page( 'Opal Site Scan', 'Opal Site Scan', 'manage_options', 'opal-site-scan', 'phua_admin_page_output' );
		}

		function phua_admin_page_output(){
			echo '<div class="wrap opalsitescannerpage"><h1>Opal Site Scanner</h1>';
			?>
<p>Site Scanner will check the status of your site, plugins, and platform to produce a report which you can analyse to help you keep your site safe, speedy and secure. Our customers use this plugin to send reports so that we can advise and repair problems.
</p>
			<?php
			//$scanurl=add_query_arg( 'scannow', 'true');
			echo('<hr>');	echo '<div id="scanbarcontrols"><a class="button bigbutton opalscannow">Scan your site</a><a class="button bigbutton opalsend">Send Report</a><div class="opalspinnerlocation"></div></div><hr>';
			//echo('<div id="opalscanbarholder"></div>');
			//echo '<div class="opal_status"><div class="statusbar"></div><div class="statusmessage">Waiting for status ...</div></div>' ; // temporary for styling
			echo '<div id="opalscan_displayarea"> </div>'; // the scan gets written to here by AJAX.
			 opalscan_show_scan(); // show the previous scan on load.
			echo('<hr>');
			echo('</div>');// close the main edit page pane
		}
}
