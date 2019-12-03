jQuery(document).ready(function($) {
  $('.opalscannow').click(function() {
    alert( "Handler for .click() called." );
    doScan();
  });
    // We'll pass this variable to the PHP function example_ajax_request
    var fruit = 'Banana';

    // This does the ajax request

    function doScan(){
    $.ajax({
        url: ajaxurl, // or example_ajax_obj.ajaxurl if using on frontend
        data: {
            'action': 'opalscan_ajax_request',
            'fruit' : fruit
        },
        success:function(data) {
            // This outputs the result of the ajax request
            console.log(data);
            $( "#opalscan_displayarea" ).html('SCAN PRETEND COMPLETED');

        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
  }
});
