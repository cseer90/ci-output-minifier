mouse_over_slider = false;
over_logout = 0;

function setCookie(cname, cvalue, exdays) 
{
    exdays = exdays?exdays:1;
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname)
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) 
    {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
    }
    return "";
}

var count = 0;

function changeVolume(level)
{
 var audio = $('#controller');
 controller.volume = level;
 var per = level?Math.ceil(level*100)+'px':'0px';
 level = level?Math.ceil(level*100):0;
 if(level==0)
 {
  $('.volume_wrapper>.glyphicon').addClass('glyphicon-volume-off').removeClass('glyphicon-volume-up glyphicon-volume-down');
 }
 else if(level<50)
  $('.volume_wrapper>.glyphicon').addClass('glyphicon-volume-down').removeClass('glyphicon-volume-up glyphicon-volume-off');
 else if(level>80)
  $('.volume_wrapper>.glyphicon').addClass('glyphicon-volume-up').removeClass('glyphicon-volume-down glyphicon-volume-off');
 setCookie('last_vol',level/100,30);
 $('#volume_level').find('.pointer').animate({bottom:per},'fast');
 $('#volume_level').find('.activebar').animate({height:per},'fast');
}


function playAudio(start)
{
  pause = false;
  // start?audio.buffered.start(start):audio.play();
  audio.play();
  $('[item-id="'+$('#current').val()+'"]').toggleClass('glyphicon-play glyphicon-pause')
}

function pauseAudio()
{
  pause = true;
  $('[item-id="'+$('#current').val()+'"]').toggleClass('glyphicon-play glyphicon-pause');
  //audio is set in ajax.js
  audio.pause();
}