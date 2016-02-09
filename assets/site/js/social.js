(function(d, s, id) //for facebook
{
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=801538769886801&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));


(function()//for Gplus
{
   var po = document.createElement('script'); 
   po.type = 'text/javascript'; 
   po.async = true;
   // po.src = 'https://apis.google.com/js/client:plusone.js?onload=render';
   po.src = 'https://apis.google.com/js/client:plusone.js';
   var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();//for Gplus

window.fbAsyncInit = function()//for FB
{
 FB.init({
        appId      : '801538769886801',
        version    : 'v2.0',
        status     : true, // check login status
        cookie     : true, // enable cookies to allow the server to access the session
        frictionlessRequests: true,
        xfbml      : false  // on true,parse XFBML,when there are social plugins added 
        });
}

function connectFB()
{
 FB.login(function(response)
 {
  if(!response.authResponse)
  {
   alert('There was an error logging you in through Facebook.\nPlease try again.');
   return false;
  }
  else if(response.status=='connected')
  {
   FB.api('/me?fields=email,name',function(resp)
   {
    // console.log(resp);
    if(response.authResponse)
		connectSocial(resp,'facebook');
	else
	{
	 alert('An error occured while logging you in through Facebook.\nPlease try again.')
	 return false;
	}
   })
  }
  else
  {
   alert('There was an error signing you up through Facebook.\nPlease try again.');
   return false;
  }
 },{scope:'email,publish_actions'});
}

function connectGplus() 
{
  gapi.auth.signIn({'callback':loadPlusAPI,'scope':'email'});
}

function loadPlusAPI(authResult)
{
  if (authResult['status']['signed_in']) 
  {
   gapi.client.load('plus','v1',function()
   {
    var request = gapi.client.plus.people.get( {'userId' : 'me'} );
    request.execute(function(obj)//loadProfile
    {
     console.log(obj)
     response = new Object;
     response.id = obj.id;
     email = !obj['emails'].length? false :obj['emails'].filter(function(v) 
     {
        return v.type === 'account'; // Filter out the primary email
     })[0].value; // get the email from the filtered results, should always be defined.
     if(!email)
     {
      alert('Sorry, we were not able to fetch details from your Google account. Please make sure you have updated all your details (including email) on your Google account before you connect it with us.');
      return;
     }
     response.email = email;
     connectSocial(response,'gplus');
   });
   })
  }
  // else 
  // {
   // alert('An error occurred while connecting your gPlus account. Details:\n'+authResult['error'])
  // }
}

function onLinkedInLoad() 
{
 IN.Event.on(IN, "auth", function()
 {
  $('[name="linkedin"]').attr('disabled',false);
 });
}

function onLinkedInAuth() 
{
 IN.API.Profile("me")
    .fields('id', "email-address")
    .result(function(profiles)
     {
        member = profiles.values[0];
        response = new Object;
        response.id = member.id;
        response.email = member.emailAddress;
        connectSocial(response,'linkedin')
     });
}

gplus = false;
facebook = false;
linkedin = false;
function connectSocial(response,type)
{
 showLoader('Connecting your '+type.toUpperCase()+' account. Please wait..');
 if(type=='gplus')
  gplus = true;
 if(type=='facebook')
  facebook = true;
 if(type=='linkedin')
  linkedin = true;
 $.post
 (jsHome+'/user/ajax/',
  {
	task:'connect',
	action:'social',
	item:type,
	id:response.id,
	email:response.email
  },
  function(data)
  {
   hideLoader();
   if(window[type])
   {
    window[type] = false;
    alert(data.msg);
    if(data.success)
     window.location.reload();
   }
  },'json')
  .fail(function()
  {
   hideLoader();
   alert('There was an error in this request. Please try again.');
  })
}
