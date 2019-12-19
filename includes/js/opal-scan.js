jQuery(document).ready(function($) {
var path = thescanobj.pluginpath+'includes/media/';

/****** TABS ************/

$(document).on('click','.opal_tab', function(e) {
  var tab_id = $(this).attr('data-tab');
  $('.opal_tab').removeClass('active');
  $('.opal_pane').removeClass('active');

  $(this).addClass('active');
  $("#"+tab_id).addClass('active');
})

/****************/

$('<audio id="opalalertaudio"><source src="'+path+'notify.ogg" type="audio/ogg"><source src="'+path+'notify.mp3" type="audio/mpeg"><source src="'+path+'notify.wav" type="audio/wav"></audio>').appendTo('body');
//console.log('dis be path = '+thescanobj.pluginpath);

  $('.opalscannow').click(function() {
    $('#opalscanbarholder').addClass("lds-hourglass");
    $("#opalscanbarholder").after('<div class="opal_status">status goes here </div>');///  ADD THIS status display zone.
    $( "#opalscanner_results" ).fadeOut(900, function() { $("#opalscanner_results").remove(); });
    doScan();
  });

    // We'll pass this variable to the PHP function
    var scan = 'startscan';

/**************** AJAX FUNCTION ******************************************************************/

  function doScan(){
    //  console.log('dis be path = '+thescanobj.pluginpath);

    window.setInterval(function(){  check_status();}, 250);

      $.ajax({
        url: ajaxurl,
        data: {
            'action': 'opalscan_ajax_request',
            'scan' : scan,
          //  'allPlugins' : allPlugins
        },
        success:function(data) {
          $('#opalalertaudio')[0].play();
        //  console.log(data);
        $('.opal_status ').remove();
        $('#opalscanbarholder').removeClass("lds-hourglass");

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
  console.log('update of status');
  $.get( "https://testingzone.local/wp-content/plugins/opalscanner/reports/scanstatus.txt", function( data ) {
    console.log(data);
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
