<?php
function opal_send_report($location, $address){
  
  $attachments = array(WP_CONTENT_DIR . '/uploads/file_to_attach.zip');
   $headers = 'From: My Name <myname@mydomain.com>' . "\r\n";
   wp_mail('test@test.com', 'subject', 'message', $headers, $attachments);
}
