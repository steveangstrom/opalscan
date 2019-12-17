jQuery(document).ready(function($) {

var path = thescanobj.pluginpath+'includes/media/';
$('<audio id="opalalertaudio"><source src="'+path+'notify.ogg" type="audio/ogg"><source src="'+path+'notify.mp3" type="audio/mpeg"><source src="'+path+'notify.wav" type="audio/wav"></audio>').appendTo('body');
//console.log('dis be path = '+thescanobj.pluginpath);

  $('.opalscannow').click(function() {
    //alert( "Handler for .click() called." );
    $('#opalscanbarholder').addClass("lds-hourglass");

    doScan();
  });
    // We'll pass this variable to the PHP function example_ajax_request
    var scan = 'startscan';

    // This does the ajax request

    function doScan(){
    //  console.log('dis be test = '+ajaxurl);
    //  console.log('dis be path = '+JSON.stringify(thescanobj));
    //  console.log('dis be path = '+thescanobj.pluginpath);
    $.ajax({
        url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
        data: {
            'action': 'opalscan_ajax_request',
            'scan' : scan
        },
        success:function(data) {
            // This outputs the result of the ajax request
              $('#opalalertaudio')[0].play();
            console.log(data);
            $('#opalscanbarholder').removeClass("lds-hourglass");
            $( "#opalscan_displayarea" ).html(data);

        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
  }
});
