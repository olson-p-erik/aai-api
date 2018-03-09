( function ($) {
  
  /**
   * This function votes a video up
   * @return void
   */
  function voteup() {
    let video = document.getElementById( "video" );
    var video_id = video.dataset.videoId;

    if ( isVoteAllowed( video_id ) ) {
      //They've already voted or it's the weekend, don't let them vote
      return;
    }

    var ajax_success = voteVideoAjax( "1", video_id );

    if ( ajax_success ) {
      //Setting the cookie so they can't vote again for 24hours
      setVoteCookie( video_id );
      
      let votes = document.getElementById( "votes" );
      let count = parseInt( votes.innerHTML );
      count++;
      count = pluralizeVotes( count );
      votes.innerHTML = count;
    }

  }

  /**
   * This function votes a video down
   * @return void
   */
  function votedown() {
    let video = document.getElementById( "video" );
    var video_id = video.dataset.videoId;

    if ( isVoteAllowed( video_id ) ) {
      //They've already voted or it's the weekend, don't let them vote
      return;
    }

    let ajax_success = voteVideoAjax( "-1", video_id );

    if ( ajax_success ) {
      //Setting the cookie so they can't vote again for 24hours
      setVoteCookie( video_id );

      let votes = document.getElementById( "votes" );
      let count = parseInt( votes.innerHTML );
      count--;
      count = pluralizeVotes( count );
      votes.innerHTML = count;
    }
  }

  /**
   * This is the function that makes the AJAX call to vote the video up/down
   * @param  {string} vote     Whether or not the vote was +/- 1
   * @param  {string} video_id The id of the video we're voting on
   * @return {boolean}         Returns true if the ajax call succeeded
   */
  function voteVideoAjax( vote, video_id ) {
    var ajax_url = "/votes/" + vote + "/" + video_id;

    $.ajax({
      type: "GET",
      url: ajax_url,
      cache: false,
      success: function( response ) {}
    });

    return true;
  }

  /**
   * Properly pluralize the votes when up/down voting
   * @param  {int} count    The new number of votes
   * @return {string}       The pluralized number of votes
   */
  function pluralizeVotes( count ) {
    if ( count == 1 || count == -1 ) {
      return count + ' votes'   }
    else {
      return count + ' votes';
    }
  }

  /**
   * This function checks to see if the current user is allowed to vote on this video
   * by checking to see if it is a weekend day
   * or whether they have the 24hour cookie set
   * @param  {string}  video_id The id of the video
   * @return {boolean}          Whether or not their allowed to vote on this video
   */
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

  /**
   * This function sets the cookie to prevent the user from voting for 24hours
   * @param {string} video_id The id of the video
   */
  function setVoteCookie( video_id ) {
    let cookie_video_id = video_id.replace(/-/g, '_');

    let date = new Date();
    date.setDate( date.getDate() + 1 );
    let expires = "expires=" + date.toGMTString();

    let cookie_value = cookie_video_id + "=video" + "; " + expires + "; path=/";

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

  /**
   * An external function I found on http://jsfiddle.net/oriadam/v7b5edo8/
   * Does some magic to insert iframe HTML into a div 
   * @param  {string} html The HTML to replace with the iframe
   * @return {multi}       Either the new iframe HTML or false if the url type is not supported
   */
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

  /**
   * Attaching our upvote/downvote handling functions,
   * hiding the voting functions if the user isn't allowed to vote,
   * and adding our video iframe onto the page.
   */
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