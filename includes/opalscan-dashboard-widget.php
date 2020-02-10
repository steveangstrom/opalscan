<?php
// widget's registration
namespace opalscan;
if(is_admin()) {
  function opalAddDashboardWidget() {
    # adds the dashboard widget for this plugin, the widget includes a summary table

      wp_add_dashboard_widget(
          'active_plugins_versions', // widget's ID
          'OpalSupport Site Scan', // widget's title
          'opalscan\opalscanDashDisplay'    // widget's callback (content)
      );
  }
  add_action('wp_dashboard_setup', 'opalscan\opalAddDashboardWidget');

  function opalscanDashDisplay(){
    # displays a summary table. note that the log location is hashed.
    $randomised_filename = get_option( 'opalsupport_log_location' );
    $out = '';
    $logfile=plugin_dir_path( __DIR__ ) . "reports/opalscan-$randomised_filename.log";

    if (file_exists($logfile)) {
      $JSON_scan = file_get_contents($logfile);
      $decoded_scan = json_decode($JSON_scan,true);
      $log_date = strtotime($decoded_scan['scanDate']['date']);
      $scandate = date('dS \o\f F Y g:i A', $log_date);

      $out .= '<p>Most recent scan results ('.$scandate.')</p>';
      $out .=  opalscan_render_summarytable($decoded_scan);
    }else{
        $out .= '<p>Your site needs to be scanned</p>';
    }
    $url=admin_url('admin.php?page=opal-site-scan');
    $out .= '<a href="'.esc_url($url).'" class="button button-primary">Perform a Scan</a>';
    echo $out;
  }
}
