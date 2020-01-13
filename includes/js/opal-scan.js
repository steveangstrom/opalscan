jQuery(document).ready(function($) {
var path = thescanobj.pluginpath+'includes/media/';

/****** TABS ************/
$(document).on('click','.opal_tab, .opal_tabber_link', function(e) {
  var tab_id = $(this).attr('data-tab');
  $('.opal_tab').removeClass('active');
  $('.opal_pane').removeClass('active');

  $(this).addClass('active');
  $("#"+tab_id).addClass('active');
})


/*-----------------Send mail---------------------------------*/
$(document).on('click','.opalsend', function(e) {
  console.log ('send');
  doReportMail();
  //$('#opalscanbarholder').addClass("lds-hourglass");

})

function doReportMail(){
    var mailaction = 'sendmail';
  $.ajax({
      url: ajaxurl,
      data: {
          'action': 'opalreportmail',
          'mailaction' : mailaction
      },
      success:function(data) {
          console.log(data);
      },
      error: function(errorThrown){
          console.log(errorThrown);
      }
  });

} /* mail */

/*****************/

$('<audio id="opalalertaudio"><source src="'+path+'notify.ogg" type="audio/ogg"><source src="'+path+'notify.mp3" type="audio/mpeg"><source src="'+path+'notify.wav" type="audio/wav"></audio>').appendTo('body');


  $(document).on('click','.opalscannow, .opaldoscan', function(e) {
    $('.opalspinnerlocation').addClass("lds-hourglass");
    $('.opalsend').addClass('opalhide'); // hide the send buttons while in action.
    $(".opalspinnerlocation").after('<div class="opal_status"><div class="statusbar"></div><div class="statusmessage">Waiting for status ...</div></div>');///  ADD THIS status display zone.
    $( "#opalscanner_results" ).fadeOut(900, function() { $("#opalscanner_results").remove(); });
    doScan();
  });

    // We'll pass this variable to the PHP function
    var scan = 'startscan';

/**************** AJAX FUNCTION ******************************************************************/

  function doScan(){
    //  console.log('dis be path = '+thescanobj.pluginpath);
    var statustimer = window.setInterval(function(){  check_status();}, 250); // got check to see whats happening on the server.

      $.ajax({
        url: ajaxurl,
        data: {
            'action': 'opalscan_ajax_request',
            'scan' : scan,
          //  'allPlugins' : allPlugins
        },
        success:function(data) {
        clearTimeout(statustimer); // stop looking for status.
        $('.opalsend').removeClass('opalhide');
        $('.opalsend').addClass('logpresent');// there should be a log available to send if success. 
        $('#opalalertaudio')[0].play();
        //  console.log(data);
        $('.opal_status ').remove();
        $('.opalspinnerlocation').removeClass("lds-hourglass");

      //  $( "#opalscan_displayarea" ).html(data); // this works with raw HTML data.

          // removed for JSON reasons.
          //console.log(data);

            var structureddata = jQuery.parseJSON(data);
            if (structureddata.scansuccess ==true){
              $( "#opalscan_displayarea" ).html(structureddata.html);
            }
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
  } /* END DO SCAN */

function check_status(){
  $.get( "https://testingzone.local/wp-content/plugins/opalscanner/reports/scanstatus.txt", function( data ) {
    //console.log(pluginpath);
    var structureddata = jQuery.parseJSON(data);
    var total = structureddata.total;
    var progress = structureddata.progress;
    var percent = progress * (100/total);
    $('.opal_status .statusbar').width(percent+'%');
    $('.opal_status .statusmessage').html('[ '+progress+' of '+total+' ] '+structureddata.slug );
  });
}

/**************************/
function watchstatus(){
    console.log('status watching start');
  $.ajax({
    url: ajaxurl,
    data: {
        'action': 'opalstatus'
    },
    success:function(data) {
      //alert ('status is go ');
      console.log(data);
    },
    error: function(errorThrown){
        console.log(errorThrown);
    }
  });
} /* END DO status */


});
