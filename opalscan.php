<?php
/*
  Plugin Name: Opal Site Scanner
  Plugin URI: OpalSphere.com
  Author: Opalsphere
  Version: 0.1
  Author URI:http://opalsupport.com
 */
include_once('includes/opalscan-dashboard-widget.php' );
include_once('includes/opalscan-scanner.php' );
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
function opalscan_enqueue_scripts( ) {
	//wp_enqueue_script('jquery-ui-datepicker');
//	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	//wp_enqueue_script( 'datepicker_script', plugin_dir_url( __FILE__ ) . 'includes/datepicker.js' );

	wp_enqueue_script( 'opalscan_ajax_display', plugin_dir_url( __FILE__ ) . 'includes/js/opal-scan.js', array( 'jquery' ),false,true );
	wp_localize_script( 'opalscan_ajax_display', 'thescanobj', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),  ));

	wp_register_style( 'opalscan_admin_css', plugin_dir_url( __FILE__ ) . '/includes/css/opalscanner_admin.css', false, '1.0.0' );
	wp_enqueue_style( 'opalscan_admin_css' );
}

add_action('admin_enqueue_scripts', 'opalscan_enqueue_scripts');


if(is_admin()) { // make sure, the following code runs only in the back end

	if (!function_exists('plugins_api')) {
		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	}

		$vuln_score_plugin_outdated =
		$vuln_score_plugin_noupdates =
		$vuln_score_plugin_amount =
		$vuln_score_php_version =
		$vuln_score_sql_version =
		$vuln_score_wp_version =
		$vuln_score_ssl =
		 0;

    // returns version of the plugin represented by $slug, from repository
    function getPluginVersionFromRepository($slug) {
				$call_api = plugins_api( 'plugin_information', array( 'slug' => $slug , 'version' => true,) );
			  return $call_api;
    }

    // dashboard widget's callback
    function displayVulnScan() {
        $allPlugins = get_plugins(); // associative array of all installed plugins
        $activePlugins = get_option('active_plugins'); // simple array of active plugins

				$today= new DateTime();
				$datetime2 = new DateTime('2009-10-13');

$thead = <<<THEAD
	<table width="100%">
	<thead>
	<tr>
	<th width="20%" style="text-align:left">Plugin</th>
	<th width="20%" style="text-align:left">Installed Version</th>
	<th width="20%" style="text-align:left">Available</th>
	<th width="20%" style="text-align:left">WARN</th>
	<th width="20%" style="text-align:left">Outdated</th>
	</tr>
	</thead>
	<tbody>
THEAD;
echo $thead;

        // traversing $allPlugins array
        foreach($allPlugins as $key => $value) {
          //  if(in_array($key, $activePlugins)) { // display active only
                echo '<tr>';
                echo "<td>{$value['Name']}</td>";
                echo "<td>{$value['Version']}</td>";
                $slug = explode('/',$key)[0]; // get active plugin's slug

                // get newest version of active plugin from repository
                $call_api = getPluginVersionFromRepository($slug);
								$repoversion = $call_api->version;
								$last_updated = $call_api->last_updated;

								$last_updated_date = new DateTime($last_updated );
                echo "<td>{$repoversion}</td>";

								if($repoversion>$value['Version']){
									$issueflag = 'ISSUE';
									$vuln_score_plugin_outdated +=1;
								}else{$issueflag ='';}

								 echo "<td>$issueflag</td>";
								 $interval = $today->diff($last_updated_date);
								 $intervalstring= $interval->format('%R%a');
								 $intervalINT=intval($intervalstring);

								 if ($intervalINT <= -365){
									 $datewarn="WARN";
									 $vuln_score_plugin_noupdates +=1;
								 }else{$datewarn="";}

								 echo '<td>'.$intervalstring.' '.$datewarn.'</td>';
                echo '</tr>';
          //  }
        }
        echo '</tbody>';
        echo '</table>';
				echo '<h3>Plugin Vulnerability Score = '.$vuln_score_plugin_outdated .'</h3>';
    }



		add_action('admin_menu', 'register_opalscan_menu');
		function register_opalscan_menu() {
			add_menu_page( 'Opal Site Scan', 'Opal Site Scan', 'manage_options', 'opal-site-scan', 'phua_admin_page_output' );
			#add_action('admin_init', 'save_log_page_items', 10); // if you need to save something weird this might be useful.
		}


		//add_action( 'wp_ajax_do_some_ajax', 'phua_log_ajax_output');


		function phua_admin_page_output(){
			echo '<div class="wrap opalsitescannerpage"><h1>Opal Site Scanner</h1>';

			?>
<p>Site Scanner will check the status of your site, plugins, and platform to produce a report which you can analyse to help you keep your site safe, speedy and secure.<br>Our customers use this plugin to send us the details reports so we can advise and repair problems.
</p>
			<?php
			//echo('<div class="opalscanbarholder">Scanning <div class="opalscanbar"></div></div>');
			$scanurl=add_query_arg( 'scannow', 'true');
	//		echo '<p><a class="button bigwhitebutton" href="'.$scanurl.'">SCAN</a></p>';
			echo('<hr>');	echo '<p><a class="button bigbutton opalscannow">SCAN YOUR SITE</a><a class="button bigbutton opalsend">SEND IT</a></p><hr>';
			echo('<div id="opalscanbarholder"></div>');
			echo '<div id="opalscan_displayarea"> </div>'; // the scan gets written to here by AJAX.
			echo opalscan_show_scan(); // show the previous scan on load.


			echo('<hr>');


				echo('</div>');// close the main edit page pane
		}





}
