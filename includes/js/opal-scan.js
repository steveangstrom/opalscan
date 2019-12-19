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
    $( "#opalscanner_results" ).remove();
    doScan();
  });

    // We'll pass this variable to the PHP function
    var scan = 'startscan';

/**************** AJAX FUNCTION ******************************************************************/

  function doScan(){
    //  console.log('dis be path = '+thescanobj.pluginpath);
      $.ajax({
        url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
        data: {
            'action': 'opalscan_ajax_request',
            'scan' : scan,
          //  'allPlugins' : allPlugins
        },
        success:function(data) {
          $('#opalalertaudio')[0].play();
        //  console.log(data);
          $('#opalscanbarholder').removeClass("lds-hourglass");
          $( "#opalscan_displayarea" ).html(data);
          /*
            var structureddata = jQuery.parseJSON(data);
            if (structureddata.scansuccess ==true){
              $('#opalalertaudio')[0].play();
              console.log(data);
              $('#opalscanbarholder').removeClass("lds-hourglass");
              $( "#opalscan_displayarea" ).html(structureddata.html);
            }*/
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
  } /* END DO SCAN */

});
