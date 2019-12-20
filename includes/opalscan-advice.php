<?php
//emits textual advice and analysis of the scores for the layperson.

/*
THE SERVER
if PHP is less than 7 give a hard warning that they can't update to the latest wp
If PHP is less than 5.6 then their server is obviously very neglected. Advise to get a host pro on the case
If SSL is not active then advise

PLUGINS ON THE SERVER
if plugins are > 10 and if there are >2 inactive plugins then advice
if Plugins installed are > 15 start really admonishing them
if there are more than 20 plugins, something strange is happing with one of their over eager interns!
If >1 installed plugins are outdated compared to the repo versions then Advise
If >3 installed plugins are outdated compared to the repo versions then strongly Advise

PLUGINS ON THE REPO
If plugins on the repo are older than 1 year then Advise
If plugins on the repo have not been updated in >2 years then strongly Advise

*/


function opal_summary($score){
  $scorewords=['Extremely bad','Extremely bad', 'Very bad','Bad','Adequate','Needs Attention','Needs Attention','Good','Very Good','Excellent'];
  $attentionphrases=[
  'is extremely vulnerable and needs urgent attention in all areas urgently',
  'is very vulnerable and needs attention in many areas urgently',
  'is very vulnerable and needs attention in several areas',
  'has many problems and needs attention in several areas',
  'has problems and needs attention in several areas',
  'has problems and needs attention in several areas',
  'has some problems and needs attention',
  'needs attention to prevent issues',
  'is OK but some areas need attention',
  'is doing great, well done!',
  ];

  $out='<div class="rated_summary"><h2><span class="opal_dimmed">Rated:</span> '.$scorewords[round($score/10)-1].'</h2>';
  $out.='<p>Your site scored '.$score.' out of a possible 100. This means your site '.$attentionphrases[round($score/10)-1].'</p>';
  $out.='</div>';
  return $out;
}

function opal_advice($decoded_scan){
  $advice = '<div class="opaladvice_wrap">';



  $advice .= '<h2>Security Advice</h2>';
  $advice .='<p>Your site has severe security and maintenance problems which must be addressed. Your scan score is rated as 32 out of 100  and this means you have several issues and are vulnerable to common hack attacks, additionally due to the problems your website may fail unexpectedly.
  We advise you to ask a specialist to look at securing and maintaining your site.</p><p>If you feel confident you may update all components detailed in the Report yourself but we caution that updating multiple components synchronously without testing often reveals code incompatibilities which may render your site inoperable. If you decide to proceed yourself please back up your site and data before proceeding.
  If you would like additional help, or advice, you may wish to send the report to us for analysis.</p>';
  #  <p>Your site has security and maintenance problems which must be addressed. Your scan score is rated as $score_rating and this means you are vulnerable to attacks, or your website may fail.</p> ";




  $advice .= "<h2>Wordpress</h2><p>your website may fail.</p> ";
  $advice .= "<h2>Plugins</h2><p>your website may fail.</p> ";
  $advice .= "<h2>Web Server</h2><p>your website may fail.</p> ";
  $advice .= '</div>';
 return $advice ;
}
