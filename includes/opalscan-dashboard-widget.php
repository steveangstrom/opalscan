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

  $output = '<h4>OpalScan</h4>';
  $output = '<p>Most recent scan results</p>';
  $output = '<p>Do a scan</p>';
    echo $output;
  }
}
