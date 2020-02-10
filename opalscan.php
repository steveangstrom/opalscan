<?php
/*
  * Plugin Name: Opal Scan and Support
  * Plugin URI: https://opalsupport.com/wordpress-scan-and-support-plugin/
  * Author: OpalSupport
  * Version: 1.0.3
  * Description: Checks the health of your WordPress and allows WP experts to analyse the results
  * Author URI:http://opalsupport.com
  * License: GPL v2 or later
  * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
namespace opalscan;
 if ( !defined('ABSPATH') ) {
	header( 'HTTP/1.0 403 Forbidden' );
  exit;
}

if(is_admin()) {
  # Conditional ensures this plugin only runs in the admin area.

  include_once('includes/opalscan-calculate-score.php' );
  include_once('includes/opalscan-mailer.php' );
  include_once('includes/opalscan-dashboard-widget.php' );
  include_once('includes/opalscan-scanner.php' );

  function op_append_plugin_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
      if ( strpos( $plugin_file_name, basename(__FILE__) ) ) {
          $links_array[] ='<a class="op_do_a_scanbut" href="' . esc_url( admin_url( '/admin.php?page=opal-site-scan' ) ) . '">' . __( 'Perform a Scan', 'textdomain' ) . '</a>';
        #  $links_array[] = '<a href="#">Support</a>';
        #  $links_array[] = '<a href="#">FAQ</a>';
      }
      return $links_array;
  }
  add_filter( 'plugin_row_meta', 'opalscan\op_append_plugin_links', 10, 4 );

  function opalscan_enqueue_scripts( ) {
    # Adds and localizes the JS file, with a nonce for the two AJAX submissions (scan and mail)
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

  function register_opalscan_menu() {
    # Adds the admin menu for this plugin
  	add_menu_page( 'Opal Scan & Support', 'Opal Scan & Support', 'manage_options', 'opal-site-scan', 'opalscan\opalscan_admin_page_output' );
  }
  add_action('admin_menu', 'opalscan\register_opalscan_menu');

  function opalscan_admin_page_output(){
    # Renders the basic HTML for the admin page for this plugin,
  	echo '<div class="wrap opalsitescannerpage"><h1>Opal Scan &amp; Support</h1><p>This Site Scanner creates a quick analysis of the health status of your site but is not a comprehensive security scan, it is a tool helping our Opal Support Customers report issues with their sites.</p>';

    $randomised_filename = get_option( 'opalsupport_log_location' );
  	$logfile=plugin_dir_path( __FILE__ ) . "reports/opalscan-$randomised_filename.log";
  	$sendvisibility = '';
    
  	if (file_exists($logfile)) {$sendvisibility = 'logpresent';}
  	echo('<hr>');	echo '<div id="scanbarcontrols" class="noselect"><a class="opalbigbutton opalscannow">Scan your site</a><a class="opalbigbutton opalsend opalsendGDPR '.$sendvisibility.'">Send Report</a><div class="opalspinnerlocation"></div></div><hr>';
  	echo '<div id="opalscan_displayarea"> </div>'; // the scan gets written to here by AJAX.
  	opalscan_show_scan(); // show the previous scan on load.
  	echo('</div>');// close the main site scanner page pane
  }
}
