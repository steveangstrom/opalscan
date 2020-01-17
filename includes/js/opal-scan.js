jQuery(document).ready(function($) {
var path = thescanobj.pluginpath+'includes/media/';

var score = $('#opalreportgraph').attr('data-score');
drawscorearc(score);
op_dobars();
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
    $('.opalspinnerlocation').addClass("lds-hourglass");
    $('.bigbutton.opalsend').addClass("sendingmail");
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
        $('.opalspinnerlocation').removeClass("lds-hourglass");
        $('.bigbutton.opalsend').removeClass("sendingmail");
      },
      error: function(errorThrown){
          console.log(errorThrown);
      }
  });

} /* mail */

/*****************/

$('<audio id="opalalertaudio"><source src="'+path+'scan-complete.mp3" type="audio/mpeg"><source src="'+path+'scan-complete.wav" type="audio/wav"></audio>').appendTo('body');
  $(document).on('click','.opalscannow, .opaldoscan', function(e) {
    $('.opalspinnerlocation').addClass("lds-hourglass");
    $('.opalsend').addClass('opalhide'); // hide the send buttons while in action.
    $(".opalspinnerlocation").after('<div class="opal_status"><div class="statusbar"></div><div class="statusmessage">Waiting for status ...</div></div>');///  ADD THIS status display zone.
    $( "#opalscanner_results" ).fadeOut(900, function() { $("#opalscanner_results").remove(); });
    doScan();
  });

    // We'll pass this variable to the PHP function
    var scan = 'startscan';

/***** AJAX FUNCTION *******/

  function doScan(){
    var statustimer = window.setInterval(function(){  check_status();}, 250); // got check to see whats happening on the server.
      $.ajax({
        url: ajaxurl,
        data: {
            'action': 'opalscan_ajax_request',
            'scan' : scan,
        },
        success:function(data) {
        clearTimeout(statustimer); // stop looking for status.
        $('.opalsend').removeClass('opalhide');
        $('.opalsend').addClass('logpresent');// there should be a log available to send if success.
        $('#opalalertaudio')[0].play();
        //  console.log(data);
        $('.opal_status ').remove();
        $('.opalspinnerlocation').removeClass("lds-hourglass");

          var structureddata = jQuery.parseJSON(data);
          if (structureddata.scansuccess ==true){
            $( "#opalscan_displayarea" ).html(structureddata.html);
          }
          score = $('#opalreportgraph').attr('data-score');
          drawscorearc(score); // draw the speedo graph
          op_dobars();
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


  function drawscorearc(score){
    var c = document.getElementById("opalreportgraph");
    if (score===''){  return;}
    var rating = score/100;
    var ratingcolors=["rgb(223,62,62)","rgb(223,144,42)","rgb(73,164,44)" ];
    switch(true){
      case score <50:
        var ratecol =0;
        break;
      case score <80:
        var ratecol =1;
        break;
      case score <100:
        var ratecol =2;
        break;
      default:
        var ratecol =2;
    }
    var c = document.getElementById("opalreportgraph");
  //  console.log(c);
    var ctx = c.getContext("2d");
    ctx.beginPath();
    ctx.arc(75, 75, 50, 0, 2 * Math.PI);
    ctx.lineWidth = 15;
    ctx.strokeStyle = '#dfdfdf';// grey bg/
    ctx.stroke();
    /*****/
    ctx.lineWidth = 6;
    ctx.strokeStyle = ratingcolors[ratecol];
    ctx.beginPath();
    var start = 2;
    var rating = rating * 1.72;
    end= start + (rating * Math.PI);
    ctx.arc(75, 75, 50, start, end);
    ctx.stroke();

    /****/
    ctx.font = '45px -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif';
    ctx.fillStyle = ratingcolors[ratecol];
    ctx.textAlign = 'center';
    ctx.fillText(score, c.width/2, (c.height/2)+15);

  }
  function op_dobars(){
    var sec_score = $('#score-secure').attr('data-score');
    $('#score-secure .opbar').css({"width": sec_score+'%'});
    $('#score-secure .opbar').addClass(op_barcol(sec_score));

    var maint_score = $('#score-maintain').attr('data-score');
    $('#score-maintain .opbar').css({"width": maint_score+'%'});
    $('#score-maintain .opbar').addClass(op_barcol(maint_score));

    var other_score = $('#score-other').attr('data-score');
    $('#score-other .opbar').css({"width": other_score+'%'});
    $('#score-other .opbar').addClass(op_barcol(other_score));
  }

  function op_barcol($score){
    if ($score <50){return 'bad' }
    if ($score >=50 && $score<80){return 'ok' }
    if ($score >=80){return 'good' }
  }

});
