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
function opal_advice(){

  $advice = '';
  // $advice .= "<h2>Security Advice</h2><p>Your site has security and maintenance problems which must be addressed. Your scan score is rated as $score_rating and this means you are vulnerable to attacks, or your website may fail.</p> ";
  $advice .= "<h2>Security Advice</h2><p>Your site has security and maintenance problems which must be addressed. Your scan score is rated as SCORE GOES HERE and this means you are vulnerable to attacks, or your website may fail.</p> ";
  $advice .= "<h3>Wordpress Needs Attention</h3><p>your website may fail.</p> ";
  $advice .= "<h3>Plugins</h3><p>your website may fail.</p> ";
  $advice .= "<h3>Web Server</h3><p>your website may fail.</p> ";
 return $advice ;

}
