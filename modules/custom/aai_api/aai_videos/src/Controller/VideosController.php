<?php

namespace Drupal\aai_videos\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * This class handles the routes for the videos lists
 * and for viewing a single video
 */
class VideosController extends ControllerBase {

	private $api_videos_url = 'https://proofapi.herokuapp.com/videos';
	
	/**
	 * This method makes the API call to get an individual video
	 * and builds the data for the page
	 * @param  string $video_id The id of the video to retrieve from the API
	 * @return array            Build data for the video
	 */
	public function getVideo( $video_id ) {
		$response = null;
		$result = null;
		$build = [];

		if ( function_exists( 'aai_api_response' ) ) {
			$video_url = $this->api_videos_url . '/' . $video_id;
			$response = aai_api_response( $video_url, 'GET' );
		}

		if ( $response ) {

			$result = json_decode($response, true);
			$formatted_video = $this->formatVideo( $result['data'] );

			$data = [];
			$data['video'] = $formatted_video;

			$build = [
				'#theme' => 'view_video',
				'#title' => 'View Video',
				'#pagehtml' => 'The video you asked for',
				'#data' => $data
			];
		}
		return $build;
	}

	/**
	 * This method trims out the unnecessary data from the video array returned by the API call
	 * @param  array $video The raw API video data
	 * @return array        The trimmed, formatted video data
	 */
	public function formatVideo( array $video ) {
		$formatted_video = [];
		$formatted_video['id'] = $video['id'];
		$formatted_video['title'] = $video['attributes']['title'];
		$formatted_video['url'] = $video['attributes']['url'];
		$formatted_video['view_tally'] = $this->pluralize( $video['attributes']['view_tally'], 'view' );
		$formatted_video['vote_tally'] = $this->pluralize( $video['attributes']['vote_tally'], 'vote' );
		$formatted_video['created_at'] = $video['attributes']['created_at'];
		return $formatted_video;
	}

	/**
	 * This method makes the API call to get all of the videos, sorts them by most recent,
	 * and builds the data for the page.
	 * @return array Build data for the Most Recent Videos page
	 */
	public function getRecentList() {
		$response = null;
		$result = null;
		$build = [];

		if ( function_exists( 'aai_api_response' ) ) {
			$response = aai_api_response( $this->api_videos_url, 'GET' );
		}

		if ( $response ) {

			$result = json_decode($response, true);
			$formatted_videos = $this->sortRecentVideos( $result['data'] );

			$data = [];
			$data['videos'] = $formatted_videos;

			$build = [
				'#theme' => 'videos_recent',
				'#title' => 'Most Recent Videos',
				'#pagehtml' => 'All the greatest new videos',
				'#data' => $data
			];
		}
		return $build;
	}

	/**
	 * This method makes the API call to get all of the videos,
	 * sorts and trims them by the top 10 highest voted videos,
	 * and builds the data for the page.
	 * @return array Build data for the Most Recent Videos page
	 */
	public function getRankedVotesList() {
		$response = null;
		$result = null;
		$build = [];

		if ( function_exists( 'aai_api_response' ) ) {
			$response = aai_api_response( $this->api_videos_url, 'GET' );
		}

		if ( $response ) {

			$result = json_decode($response, true);
			$formatted_videos = $this->sortRankedVideos( $result['data'], 'votes' );

			$data = [];
			$data['videos'] = $formatted_videos;

			$build = [
				'#theme' => 'videos_ranked_votes',
				'#title' => 'Top 10 Voted Videos',
				'#pagehtml' => "The videos you can't stop upvoting",
				'#cache' => [
  					'max-age' => 300,
  				],
				'#data' => $data
			];
		}
		return $build;
	}

	/**
	 * This method makes the API call to get all of the videos,
	 * sorts and trims them by the top 10 highest viewed videos,
	 * and builds the data for the page.
	 * @return array Build data for the Most Recent Videos page
	 */
	public function getRankedViewsList() {
		$response = null;
		$result = null;
		$build = [];

		if ( function_exists( 'aai_api_response' ) ) {
			$response = aai_api_response( $this->api_videos_url, 'GET' );
		}

		if ( $response ) {

			$result = json_decode($response, true);
			$formatted_videos = $this->sortRankedVideos( $result['data'], 'views' );

			$data = [];
			$data['videos'] = $formatted_videos;

			$build = [
				'#theme' => 'videos_ranked_views',
				'#title' => 'Top 10 Most Viewed Videos',
				'#pagehtml' => "The videos you can't stop watching",
				'#cache' => [
  					'max-age' => 300,
  				],
				'#data' => $data
			];
		}
		return $build;
	}

	/**
	 * This method takes the videos array, sorts it by most recent,
	 * and formats the video data to remove unnecessary info
	 * @param  array  $videos The videos returned from the API call
	 * @return array          The sorted videos, trimmed of unnecessary data
	 */
	public function sortRecentVideos( array $videos ) {
		$formatted_videos = [];

		usort( $videos, array( $this, "sortByRecent" ) );

		$rank = 1;
		foreach ( $videos as $video ) {
			$formatted = [];
			$formatted['rank'] = $rank;
			$formatted['id'] = $video['id'];
			$formatted['title'] = $video['attributes']['title'];
			$formatted['url'] = $video['attributes']['url'];
			$formatted['view_tally'] = $this->pluralize( $video['attributes']['view_tally'], 'view' );
			$formatted['vote_tally'] = $this->pluralize( $video['attributes']['vote_tally'], 'vote' );
			$formatted['created_at'] = $video['attributes']['created_at'];
			$formatted_videos[] = $formatted;
			$rank++;
		}

		return $formatted_videos;
	}

	/**
	 * This method takes the videos array, sorts it by either the votes or views,
	 * and formats the video data to remove unnecessary info
	 * @param  array  $videos    The videos returned from the API call
	 * @param  string $rank_type The ranking to be sorted by (either 'votes' or 'views')
	 * @return array             The top 10 videos sorted by their rank, trimmed of unnecessary data
	 */
	public function sortRankedVideos( array $videos, $rank_type ) {
		$num_videos = 10;
		$formatted_videos = [];

		if ( $rank_type == 'votes' ) {
			usort( $videos, array( $this, "sortByVotes" ) );
		} else {
			usort( $videos, array( $this, "sortByViews" ) );
		}

		for ( $i=0; $i < $num_videos; $i++ ) { 
			$formatted = [];
			$formatted['rank'] = $i + 1;
			$formatted['id'] = $videos[$i]['id'];
			$formatted['title'] = $videos[$i]['attributes']['title'];
			$formatted['url'] = $videos[$i]['attributes']['url'];
			$formatted['view_tally'] = $this->pluralize( $videos[$i]['attributes']['view_tally'], 'view' );
			$formatted['vote_tally'] = $this->pluralize( $videos[$i]['attributes']['vote_tally'], 'vote' );
			$formatted['created_at'] = $videos[$i]['attributes']['created_at'];
			$formatted_videos[] = $formatted;
		}

		return $formatted_videos;
	}

	/**
	 * Compares two videos and sorts by the most recent,
	 * sorting them by the created_at date descending 
	 */
	public function sortByRecent( $a, $b ) {
		if ($a['attributes']["created_at"] == $b['attributes']["created_at"]) {
			return 0;
		}
		return ($a['attributes']["created_at"] > $b['attributes']["created_at"]) ? -1 : 1;
	}

	/**
	 * Compares two videos and sorts by their votes,
	 * sorting them by the vote_tally descending
	 */
	public function sortByVotes( $a, $b ) {
		if ($a['attributes']["vote_tally"] == $b['attributes']["vote_tally"]) {
			return 0;
		}
		return ($a['attributes']["vote_tally"] > $b['attributes']["vote_tally"]) ? -1 : 1;
	}

	/**
	 * Compares two videos and sorts by their votes,
	 * sorting them by the vote_tally descending
	 */
	public function sortByViews( $a, $b ) {
		if ($a['attributes']["view_tally"] == $b['attributes']["view_tally"]) {
			return 0;
		}
		return ($a['attributes']["view_tally"] > $b['attributes']["view_tally"]) ? -1 : 1;
	}

	/**
	 * Properly pluralize the votes/views for the page
	 * @param  int    $number The number we're checking
	 * @param  string $noun   The word we're pluralizing, either view(s) or vote(s)
	 * @return string         A pluralized string
	 */
	public function pluralize( $number, $noun ) {
		if ( $number == 1 || $number == -1 ) {
			return $number . ' ' . $noun;
		}
		else {
			return $number . ' ' . $noun . 's';
		}
	}
}
?>