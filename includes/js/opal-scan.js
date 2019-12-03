jQuery(document).ready(function($) {
  $('.opalscannow').click(function() {
    //alert( "Handler for .click() called." );
    $('#opalscanbarholder').addClass("lds-hourglass");
    doScan();
  });
    // We'll pass this variable to the PHP function example_ajax_request
    var scan = 'startscan';

    // This does the ajax request

    function doScan(){
    $.ajax({
        url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
        data: {
            'action': 'opalscan_ajax_request',
            'scan' : scan
        },
        success:function(data) {
            // This outputs the result of the ajax request
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
