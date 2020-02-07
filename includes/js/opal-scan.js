
jQuery(document).ready(function($) {

var path = thescanobj.pluginpath+'includes/media/';

var score = $('#opalreportgraph').attr('data-score');
if(typeof score !="undefined"){
  drawscorearc(score);
  op_dobars();
}

/****** TABS ************/

$(document).on('click','.opal_tab, .opal_tabber_link', function(e) {
  var tab_id = $(this).attr('data-tab');
  $('.opal_tab').removeClass('active');
  $('.opal_pane').removeClass('active');

  $(this).addClass('active');
  $("#"+tab_id).addClass('active');

  drawfullreportbars();
})

/*-----------------Send mail---------------------------------*/

$(document).on('click','.opalsendGDPR', function(e) {
  // Shows the pop-over requesting acceptance of agreeement, informing the user of their rights regarding this mail, and the privacy of the contents
  var yourmail = $('.thissite_admin_email').text();
  $('#wpwrap').prepend('<div id="op_dim_everything"></div>');
  out='<div class="op_alertbox info"><h2>Send a Report to us</h2>';
  out+='<div class="op_alertbox_close">X</div>';
  out+='<p>The report will be sent to us, and to you at <b class="op_youremail">'+yourmail+'</b> <p>We delete all reports and emails after 7 days and we don\'t retain your details, nor offer them to anyone for any purpose.<div>';
  out+='<p>If you agree to send us the report and wish us to reply with solutions then please check Agree<p><div>';
  out+='<hr><input type="checkbox" name="agree" id="agreetosend" value="agree"><label for="agreetosend"  class="noselect agreetosendlabel">Agree</label> &nbsp;';
  out+='<a class="opalbigbutton opalsend logpresent notagreed noselect">Send Report</a>';
  out+='<div>';
  $('#opalscan_displayarea').prepend(out);
})

$(document).on('click','#agreetosend', function(e) {
  // if they agree to the terms unlock th esend botton ready for action, and move the window
  if ($('#agreetosend').is(':checked')) {
    $('.op_alertbox .opalsend').removeClass('notagreed');
    $('.op_alertbox .opalsend').addClass('agreed');
    window.scrollTo(0,0);
  }else{
    $('.op_alertbox .opalsend').addClass('notagreed');
    $('.op_alertbox .opalsend').removeClass('agreed');
  }
})
$(document).on('click','.op_alertbox_close', function(e) {
  $( ".op_alertbox" ).fadeOut( 500, function() {
    $( ".op_alertbox" ).remove();
  });
  $( "#op_dim_everything" ).fadeOut( 500, function() {
    $( "#op_dim_everything" ).remove();
  });
})



$(document).on('click','.op_alertbox .opalsend.agreed', function(e) {
  // if they have agreed, and the buttons are made available. and they have clicked - then send.
  console.log ('sending a report');
  $( ".op_alertbox" ).fadeOut( 100, function() {
    $( ".op_alertbox" ).remove();
  });
  $( "#op_dim_everything" ).fadeOut( 100, function() {
    $( "#op_dim_everything" ).remove();
  });
  doReportMail(); // call the function which will call PHP
    $('.opalspinnerlocation').addClass("lds-hourglass");
    $('.bigbutton.opalsend').addClass("sendingmail");

})

function doReportMail(){
  $.ajax({
      url: ajaxurl,
      data: {
          'action': 'opalreportmail',
          'security': thescanobj.security,
      },
      success:function(data) {
        //  console.log(data);
        $('.opalspinnerlocation').removeClass("lds-hourglass");
        $('.bigbutton.opalsend').removeClass("sendingmail");
        $('#opalmailsendaudio')[0].play();
        $('#opalscan_displayarea').prepend('<div class="op_alertbox success"><h2>Mail Sent</h2>The mail containing your scan results has been sent to our team and we\'ll get in touch with you to try to help out.<div>');
        $( ".op_alertbox" ).delay(2000).fadeOut( 1000, function() {
            $( ".op_alertbox" ).remove();
          });
      },
      error: function(errorThrown){
          console.log(errorThrown);
          $('.opal_status ').remove();
          $('.bigbutton.opalsend').removeClass("sendingmail");
          $('.opalspinnerlocation').removeClass("lds-hourglass");
          $('#opalscan_displayarea').html('<div class="op_alertbox fail"><h2>Sorry, there\'s something preventing the sending of the email</h2>The error we got was : '+ errorThrown.statusText + ' |  Status Code : ' +errorThrown.status+'<br>Contact us and we\'ll try to help out<div>');
          $('#opalerroraudio')[0].play();
      }
  });

} /* end of mail and GDPR functions */

/*****************/
/*some audio for alerts, success fail, etc.*/

$('<audio id="opalalertaudio"><source src="'+path+'scan-complete.mp3" type="audio/mpeg"><source src="'+path+'scan-complete.wav" type="audio/wav"></audio>').appendTo('body');
$('<audio id="opalerroraudio"><source src="'+path+'scan-error.mp3" type="audio/mpeg"></audio>').appendTo('body');
$('<audio id="opalmailsendaudio"><source src="'+path+'scan-mailsend.mp3" type="audio/mpeg"></audio>').appendTo('body');


/***** SCAN BUTTON FUNCTION *******/
  $(document).on('click','.opalscannow, .opaldoscan', function(e) {
    if ($( this ).hasClass('deactivated')){
      return;
    }
    $('.opalspinnerlocation').addClass('lds-hourglass');
    $('.opalbigbutton.opalscannow').addClass('deactivated');// prevent double scanning.
    $('.opalsend').addClass('opalhide'); // hide the send buttons while in action.
    $(".opalspinnerlocation").after('<div class="opal_status"><div class="statusbar"></div><div class="statusmessage">Waiting for status ...</div></div>');///  ADD THIS status display zone.
    $( "#opalscanner_results" ).fadeOut(900, function() { $("#opalscanner_results").remove(); });
    doScan();
  });

/***** AJAX FUNCTION *******/
  function doScan(){
    var statustimer = window.setInterval(function(){  check_status();}, 250); // got check to see whats happening on the server.
      $.ajax({
        url: ajaxurl,
        data: {
            'action': 'opalscan_ajax_request',
            'security': thescanobj.security,
        },
        success:function(data) {
          clearTimeout(statustimer); // stop looking for status.
          $('.opalsend').removeClass('opalhide');
          // if success then there's a log avaialbe to send so make the mail button GUI active again.
          $('.opalsend').addClass('logpresent');
          $('#opalalertaudio')[0].play();
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
            clearTimeout(statustimer); // stop looking for status.
            console.log(errorThrown);
            $('.opal_status ').remove();
            $('.opalspinnerlocation').removeClass("lds-hourglass");
            $('#opalscan_displayarea').html('<div class="opbox"><h2>Sorry, there\'s something preventing the scanning of your site</h2>The error we got was : '+ errorThrown.statusText + ' |  Status Code : ' +errorThrown.status+'<br>Contact us and we\'ll try to help out<div>');
            $('#opalerroraudio')[0].play();
        },
        complete: function(){
          clearTimeout(statustimer); // stop looking for status.
          $('.opalscannow ').removeClass('deactivated');

        }
    });
  } /* END DO SCAN */

function check_status(){
  $.ajax({
    url: ajaxurl,
    data: {
        'action': 'opalscan_scanstatus_request',
        'security': thescanobj.security,
    },
    success:function(data) {
    //  console.log(data);
      var structureddata = jQuery.parseJSON(data);

      var total = structureddata.total;
      var progress = structureddata.progress;
      var percent = progress * (100/total);
      $('.opal_status .statusbar').width(percent+'%');
      $('.opal_status .statusmessage').html('[ '+progress+' of '+total+' ] '+structureddata.slug );
    }
  });
}


/*   Drawing functions for the GUI   */
/* this draws the roundel or target score board */

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
    var ctx = c.getContext("2d");
    ctx.beginPath();
    ctx.arc(c.width/2, c.height/2, 75, 0, 2 * Math.PI);
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
    ctx.arc(c.width/2, c.height/2, 75, start, end);
    ctx.stroke();

    /****/
    ctx.font = '45px -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif';
    ctx.fillStyle = ratingcolors[ratecol];
    ctx.textAlign = 'center';
    ctx.fillText(score, c.width/2, (c.height/2)+15);
  }

  /* This set of functions draw the bar grpah scores */
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

  function drawfullreportbars(){
    $('.opfullscanbar').each(function( index ) {
      var newwidth = $( this ).text()
      var bar = $( '<div class="scanbar"></div>' );
      $( bar ).css( "background-color", "hsl("+newwidth+",20%,87%)" ).width(newwidth+'%');
      $( this ).append( bar );
      if(newwidth <30){
        $( this ).addClass('op_badbar');
      }
    });
  }
});
