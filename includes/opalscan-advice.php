<?php
namespace opalscan;
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
# just a basic assessment of the overal score. the score total is worked out in the function opal_do_score() \includes\opalscan-calculate-score.php
function opal_summary($score){
  $score_words= Array('Extremely bad','Extremely bad', 'Very bad','Bad','Bad','Needs Urgent Attention','Needs Attention','Needs Attention','Good','Excellent');
  $attentionphrases=Array(
  'is extremely vulnerable to attack and data loss and needs urgent attention in all areas.',
  'is very vulnerable to attack and data loss  and needs attention in many areas urgently',
  'is very vulnerable to attack and data loss and needs attention in several areas',
  'has many security and core code update problems and needs attention in several areas',
  'has security and update problems and needs attention in several crucial areas',
  'has security and update problems and needs attention in several areas',
  'has some problems and needs attention',
  ' is doing well in most areas but needs attention to prevent issues. Check below for why.',
  'is OK but some areas need attention',
  'is doing great, well done!',
  );

  $out='<div class="rated_summary"><h2><span class="opal_dimmed">Rated:</span> '.esc_html($score_words[floor($score/10)]).'</h2>';
  $out.='<p>Your site scored '.$score.' out of a possible 100. This means your site '.esc_html($attentionphrases[floor($score/10)]).'</p>';
  $out.='</div>';
  return $out;
}

function opal_advice($decoded_scan, $score){
  $severity_words=Array('very extreme','extreme', 'very bad','very many','quite a lot of ','quite a few','several','some','a few','no');
  $issues_words=Array(
'you have many very serious issues and are extremely vulnerable to all common hack attacks, additionally due to the many problems your website is likely to fail imminently. We ugently advise you to ask a web specialist secure and repair your site.',
'you have very serious issues and are extremely vulnerable to common hack attacks, additionally due to the many problems your website may fail soon. We ugently advise you to ask a web specialist to look at securing and reapairing your site.',
'you have many very serious issues and are extremely vulnerable to common hack attacks, additionally due to the many problems your website may fail unexpectedly. We strongly advise you to ask a web specialist or competent IT team member to look at securing and maintaining your site.',
'you have serious issues and are vulnerable to common hack attacks, additionally due to the many problems your website may fail unexpectedly. We advise you to ask a web specialist or competent IT team member to look at securing and maintaining your site.',
'you have many issues and are vulnerable to common hack attacks, additionally due to the problems you may experience stablility and speed issues or your website may fail unexpectedly. We advise you to ask a web specialist or competent IT team member to look at securing and maintaining your site.',
'you have some issues and may be vulnerable to common hack attacks, additionally due to the problems you may experience stablility and speed issues. We advise you to ask a web specialist or competent IT team member secure, update and optimise your site.',
'you have a few issues and might be vulnerable to common hack attacks, you can ask a specialist to look at how you can update, optimise and repair the issues. ',
'you have one or two issues and should address them',
'you have few urgent issues and everything looks good, though you may wish to update the noted items',
'you have no issues and everything looks good',
);
$what_to_do_words =Array(
'which must be repaired very urgently, your site is in grave peril and we strongly advise you contact an experienced web technician to recover it, and a plan is put in place to secure your site',
'which must be repaired very urgently, your site is in grave peril and we strongly advise you contact an experienced web technician to recover it, and a plan is put in place to secure your site',
'which must be analysed and repaired by a competent web technician very urgently, with a plan put in place to secure your site in future',
'which must be analysed and repaired by a competent web technician very urgently, with a plan put in place to secure your site in future',
'which must be analysed and repaired, with a plan put in place to secure and maintain your site',
'which must be analysed and repaired, with a plan put in place to secure your site in future',
'that means you have some issues to address in order to maintain your site, take a look at the report for details',
'you need to address the issues in the report to keep your site speedy and secure',
'that means you are on top of most issues right now',
'a safe a secure web site with no detected issues. Well done. Please make sure you back up regularly and run your virus software.'
);

  $advice = '<div class="opaladvice_wrap">';
  $advice .= '<h2>Security Advice</h2>';
  $advice .='<p>Your site has '.esc_html($severity_words[floor($score/10)]).' security and maintenance problems '.esc_html($what_to_do_words[floor($score/10)]).'.<br> Your scan score is rated as '.esc_html($score).' out of 100 and this means ';

  $advice .=  $issues_words[floor($score/10)];

if ($score <70){
  $advice .='</p><h3>Self-Repair</h3><p>If you feel confident you may update all components detailed in the <a class="opal_tabber_link" data-tab="opalreport">Full Report</a> yourself but we caution that updating multiple components synchronously without testing often reveals code incompatibilities which may render your site inoperable.<br> If you decide to proceed yourself please back up your site and data before proceeding.
  </p><p>If you would like advice or assistance restoring your site speed and security you may <a>send the report to us for analysis</a>.';
}
  $advice .= '</p></div>';

  $advice .= '<div class="opaladvice_wrap"><h2>Summary Report</h2><p>Here is a brief summary of the key issues we found</p></div>';

  # add the summary table to this advice, this is used a couple of places so it's found in opalscan-render.php
  $advice .= opalscan_render_summarytable($decoded_scan);

  $advice.=('<div class="hideinmail"><p><br>Send your report to Opal Support and we will give you a free analysis.<br>A copy of the full report and our security analysis will be sent to <span class="thissite_admin_email">'.sanitize_email(get_option('admin_email')).'</span></p>');
  $advice.=('<a class="opalbigbutton opalsend opalsendGDPR logpresent">Send Report</a></div>');
 return $advice ;
}
