
var pause=false;
function loadMore(obj)
{
 $obj = $(obj);
 var left = Math.max($obj.scrollLeft(), $obj.width());
 if(left-$obj.scrollLeft()==0)
 {
  $item = $obj.find('.loader').prev('.one_item');
  if(!$item.length)
   return;
  else
   var top = $item.attr('data-top');
  // getDailyDeals(top);
  var fn=$obj.attr('data-function');
  getMoreItems(top,fn);
 }
}

var pointer = 0;
var playtime = 0;
var gap = 0;
var audio = '';
$(function()
{
 audio = document.getElementById('controller');
 $(document).on('keyup',function(e)
 {
  if(e.keyCode==32)
  {
   pause?playAudio():pauseAudio();
   e.preventDefault();
  }
 });
 
 $('.play_all').click(function()
 {
  var list = '';
  $('.quick_play').each(function()
  {
   list += $(this).attr('data-id')+',';
  });
  list.trim(',');
  $('#playlist').val(list);
  $(this).addClass('active');
  $('.quick_play').first().find('.play_btn').trigger('click')
 });
 
 $('.progress_wrapper,#track_progress').click(function(e)
 {
  var parentOffset = $('.progress_wrapper').parent().offset();
  //or $(this).offset(); if you really just want the current element's offset
  var relX = e.pageX - parentOffset.left;
  playTo = relX - (e.pageX/9.8);
  audio.currentTime = Math.floor(playTo);
 });
 
 $('.fullbar,.activebar,.volume_icon').click(function(e)
 {
  if($(this).hasClass('volume_icon'))
  {
   changeVolume(0);
   return;
  }
  var parentOffset = $(this).parent().offset(); 
  //or $(this).offset(); if you really just want the current element's offset
  var relX = e.pageX - parentOffset.left;
  // var relY = e.pageY - parentOffset.top;
  var relY = parentOffset.top - e.pageY;
  var y = parseInt(relY);
  var hyt = parseInt($('.fullbar').height());
  var level = parseFloat(y/hyt);
  changeVolume(level);
 });
 
 $('.play_current').click(function()
 {
  var id=$(this).attr('item-id');
  id?$('.quick_play[data-id="'+id+'"]>.play_btn').click():alert('Please select a track from the list to play')
 });
 
 $('.quick_play>.play_btn').on('click',function()
 {
  var $obj = $(this);
  if($obj.parent().hasClass('playing'))
  {
   $obj.find('span.glyphicon-pause').length?pauseAudio():playAudio();
   return;
  }
  var id = $obj.parent().attr('data-id');
  $('.play_current').attr('item-id',id);
  $obj.parent().addClass('playing');
  var title = $obj.parent().siblings('.title').html();
  $('#current_track').html(title+': Loading...');
  $.post(jsHome+'/default/ajax/get/token',//'/default/ajax/get'
         {
          id:id
         },
         function(json)
         {
          if(json.success)
          {
           $('#current_track').html('Now playing: '+title);
           var info = json.data.info;
           playtime = parseInt(info.playing_time);
           gap = parseFloat(100/playtime);
           $('#current').val(id);
           loadTrack(id,json.data.token);
           // $('#controller').attr('src',json.data.path)
           // playAudio();
          }
          else
          {
           $('#current_track').html('Error in playing: '+title);
           $obj.find('span.glyphicon-pause').removeClass('glyphicon-pause').addClass('glyphicon-play').parent().removeClass('playing')
          }
         },'json')
 });
 
 audio.addEventListener('progress', function()//buffered progress
 {
  if(!audio.buffered.length)
   return;
  var bufferedEnd = audio.buffered.end(audio.buffered.length - 1);
  var duration =  audio.duration;
  if (duration > 0) 
  {
   $('#buffered').css('width',((bufferedEnd / duration)*100) + "%");
  }
 });

 audio.addEventListener('timeupdate', function()//played progress
 {
  var duration =  audio.duration;
  if (duration > 0) 
  {
   $('#pointer').css({left:((audio.currentTime / duration)*100) + "%"})
  }
 });
 
 audio.addEventListener('ended',function()
 {
  pauseAudio();
  if($('#playlist').length && $('#playlist').val())
  {
   var val = $('#playlist').val().trim(',').split(',');
   var current = $('#current').val();
   var next = parseInt(val.indexOf(current));
   next++;
   if(next>=val.length)
    return;
    
   next = val[next];
   $('.quick_play[data-id="'+next+'"]>.play_btn').trigger('click');
  }
  else
  {
  }
 })
});

function loadTrack(id,token)
{
 $.post(jsHome+'/default/ajax/get/nauha',//'/default/ajax/get'
        {
         id:id,
         token:token
        },
        function(html)
        {
         var src = html.match(/http/i)?(level+html):html;
         $('#controller').attr('src',src);
         playAudio();
        }
       )
}