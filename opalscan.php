<?php
/*
  Plugin Name: Opal SiteScanner
  Plugin URI: OpalSupport.com
  Author: OpalSphere
  Version: 1
  Author URI:http://opalsupport.com
 */
namespace opalscan;
 if ( !defined('ABSPATH') ) {
	header( 'HTTP/1.0 403 Forbidden' );
  exit;
}

include_once('includes/opalscan-calculate-score.php' );
include_once('includes/opalscan-mailer.php' );
include_once('includes/opalscan-dashboard-widget.php' );
include_once('includes/opalscan-scanner.php' );

function op_plugin_action_links( $links ) {
	$links = array_merge( array(
		'<a class="op_do_a_scanbut" href="' . esc_url( admin_url( '/admin.php?page=opal-site-scan' ) ) . '">' . __( 'Perform a Scan', 'textdomain' ) . '</a>'
	), $links );
	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'opalscan\op_plugin_action_links' );


/*************************************************/
function opalscan_enqueue_scripts( ) {

	wp_enqueue_script( 'opalscan_ajax_display', plugin_dir_url( __FILE__ ) . 'includes/js/opal-scan.js', array( 'jquery' ),false,true );
	wp_localize_script( 'opalscan_ajax_display', 'thescanobj', array(
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
    'pluginpath' => plugin_dir_url( __FILE__ ) ,
    'security'  => wp_create_nonce( 'opalscan-security-nonce' )
  ));

	wp_register_style( 'opalscan_admin_css', plugin_dir_url( __FILE__ ) . '/includes/css/opalscanner_admin.css', false, '1.0.0' );
	wp_enqueue_style( 'opalscan_admin_css' );
}

add_action('admin_enqueue_scripts', 'opalscan\opalscan_enqueue_scripts');


if(is_admin()) { // make sure, the following code runs only in the back end


		add_action('admin_menu', 'opalscan\register_opalscan_menu');
		function register_opalscan_menu() {
			add_menu_page( 'Opal Site Scan', 'Opal Site Scan', 'manage_options', 'opal-site-scan', 'opalscan\opalscan_admin_page_output' );
		}

		function opalscan_admin_page_output(){
			echo '<div class="wrap opalsitescannerpage"><h1>Opal Site Scanner</h1>';
			?>
<p>This Site Scanner creates a quick analysis of the health status of your site but is not a comprehensive security scan, it is a tool helping our Opal Support Customers report issues with their sites.</p>
		<?php
      $randomised_filename = get_option( 'opalsupport_log_location' );
			$logfile=plugin_dir_path( __FILE__ ) . "reports/opalscan-$randomised_filename.log";
			$sendvisibility = '';
			if (file_exists($logfile)) {$sendvisibility = 'logpresent';}
			echo('<hr>');	echo '<div id="scanbarcontrols" class="noselect"><a class="opalbigbutton opalscannow">Scan your site</a><a class="opalbigbutton opalsend opalsendGDPR '.$sendvisibility.'">Send Report</a><div class="opalspinnerlocation"></div></div><hr>';
			echo '<div id="opalscan_displayarea"> </div>'; // the scan gets written to here by AJAX.
			opalscan_show_scan(); // show the previous scan on load.
			echo('<hr>');
			echo('</div>');// close the main edit page pane
		}
}
