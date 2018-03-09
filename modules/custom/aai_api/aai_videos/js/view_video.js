( function ($) {
  
  function voteup() {
    let video = document.getElementById( "video" );
    var video_id = video.dataset.videoId;

    if ( isVoteAllowed( video_id ) ) {
      //They've already voted and got around the hidden buttons, don't let them vote
      return;
    }

    var ajax_success = voteVideoAjax( "1", video_id );

    if ( ajax_success ) {
      console.log("if success, before cookie")
      setVoteCookie( video_id );
      let votes = document.getElementById( "votes" );
      let count = parseInt( votes.innerHTML );
      count++;
      count = pluralizeVotes( count );
      votes.innerHTML = count;
    }

  }

  function votedown() {
    let video = document.getElementById( "video" );
    var video_id = video.dataset.videoId;

    if ( isVoteAllowed( video_id ) ) {
      //They've already voted and got around the hidden buttons, don't let them vote
      return;
    }

    let ajax_success = voteVideoAjax( "-1", video_id );

    if ( ajax_success ) {
      setVoteCookie( video_id );
      let votes = document.getElementById( "votes" );
      let count = parseInt( votes.innerHTML );
      count--;
      count = pluralizeVotes( count );
      votes.innerHTML = count;
    }
  }

  function voteVideoAjax( vote, video_id ) {
    var ajax_url = "/votes/" + vote + "/" + video_id;

    $.ajax({
      type: "GET",
      url: ajax_url,
      cache: false,
      success: function( response ) {
        
      }
    });

    return true;
  }

  function pluralizeVotes( count ) {
    if ( count == 1 || count == -1 ) {
      return count + ' votes'   }
    else {
      return count + ' votes';
    }
  }

  function isVoteAllowed( video_id ) {
    if( today.getDay() == 6 || today.getDay() == 0 ) {
      //It's the weekend, so users are not allowed to vote
      return false;
    }
    if ( voteCookieExists( video_id ) ) {
      //They've got a cookie from voting in the past 24 hours, so not allowed to vote
      return false;
    }

    return true;
  }

  function setVoteCookie( video_id ) {
    let cookie_video_id = video_id.replace(/-/g, '_');

    let date = new Date();
    date.setDate( date.getDate() + 1 );
    let expires = "expires=" + date.toGMTString();

    let cookie_value = cookie_video_id + "=video" + "; " + expires + "; path=/";

    console.log("setting cookie to " + cookie_value );

    document.cookie = cookie_value;
  }

  function voteCookieExists( video_id ) {
    let cookie_video_id = video_id.replace(/-/g, '_');
    let cookie_name = cookie_video_id + "=video";

    if ( document.cookie.indexOf(cookie_name) == -1 ) {
      return false;
    }

    return true;
  }

  function convertMedia( html ) {
    // based on: http://jsfiddle.net/oriadam/v7b5edo8/   http://jsfiddle.net/88Ms2/378/   https://stackoverflow.com/a/22667308/3356679
    var cls = 'class="embedded-media"';
    var frm = '<iframe '+cls+' src="//_URL_" frameborder="0" allowfullscreen></iframe>';
    var converts = [
      {
        rx: /^(?:https?:)?\/\/(?:www\.)?vimeo\.com\/([^\?&"]+).*$/g,
        tmpl: frm.replace('_URL_',"player.vimeo.com/video/$1")
      },
      {
        rx: /^.*(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|user\/.+\/)?([^\?&"]+).*$/g,
        tmpl: frm.replace('_URL_',"www.youtube.com/embed/$1")
      },
      {
        rx: /^.*(?:https?:\/\/)?(?:www\.)?(?:youtube-nocookie\.com)\/(?:watch\?v=|embed\/|v\/|user\/.+\/)?([^\?&"]+).*$/g,
        tmpl: frm.replace('_URL_',"www.youtube-nocookie.com/embed/$1")
      },
      {
        rx: /(^[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?\.(?:jpe?g|gif|png|svg)\b.*$)/gi,
        tmpl: '<a '+cls+' href="$1" target="_blank"><img src="$1" /></a>'
      },
    ];
    for (var i in converts)
      if (converts[i].rx.test(html.trim())) {

        return html.trim().replace(converts[i].rx, converts[i].tmpl);
      }
    return false;
  };

  $( document ).ready( function() {
    let video = document.getElementById( "video" );
    let video_id = video.dataset.videoId;
    let video_url = video.dataset.videoUrl;

    $( "#upvote" ).click( voteup );
    $( "#downvote" ).click( votedown );

    if ( isVoteAllowed( video_id ) ) {
      $( "#upvote" ).css('visibility', 'hidden');
      $( "#downvote" ).css('visibility', 'hidden');
    }

    $( "#video_window" ).html( convertMedia( video_url ) );
  });
}(jQuery));