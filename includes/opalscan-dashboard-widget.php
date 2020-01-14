<?php
// widget's registration
if(is_admin()) {
  function opalAddDashboardWidget() {
      wp_add_dashboard_widget(
          'active_plugins_versions', // widget's ID
          'OpalSupport Site Scan', // widget's title
          'opalscanDashDisplay'    // widget's callback (content)
      );
  }
  add_action('wp_dashboard_setup', 'opalAddDashboardWidget');

  function opalscanDashDisplay(){
    $out = '';
    $logfile=plugin_dir_path( __DIR__ ) . 'reports/opalscan.log';

    if (file_exists($logfile)) {
      $JSON_scan = file_get_contents($logfile);
      $decoded_scan = json_decode($JSON_scan,true);
      $out .= '<p>Most recent scan results</p>';
      $out .=  opalscan_render_summarytable($decoded_scan);
    }else{
      $url=admin_url('admin.php?page=opal-site-scan');
      $out .= '<p><a href="'.$url.'" class="button">Perform a Scan</a></p>';
    }

    echo $out;
  }
}
