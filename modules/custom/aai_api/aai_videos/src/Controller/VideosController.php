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
	public function getRankdedVotesList() {
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
				'#pagehtml' => 'The greatest new videos, as voted by you',
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
	public function getRankdedViewsList() {
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

		foreach ( $videos as $video ) {
			$formatted = [];
			$formatted['title'] = $video['attributes']['title'];
			$formatted['url'] = $video['attributes']['url'];
			$formatted['view_tally'] = $video['attributes']['view_tally'];
			$formatted['vote_tally'] = $video['attributes']['vote_tally'];
			$formatted['created_at'] = $video['attributes']['created_at'];
			$formatted_videos[] = $formatted;
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
			$formatted['title'] = $videos[$i]['attributes']['title'];
			$formatted['url'] = $videos[$i]['attributes']['url'];
			$formatted['view_tally'] = $videos[$i]['attributes']['view_tally'];
			$formatted['vote_tally'] = $videos[$i]['attributes']['vote_tally'];
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
}
?>