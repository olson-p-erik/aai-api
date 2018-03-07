<?php

	namespace Drupal\aai_videos\Controller;

	use Drupal\Core\Controller\ControllerBase;

	class VideosController extends ControllerBase {

		private $api_videos_url = 'https://proofapi.herokuapp.com/videos';

		public function getRecentList() {
			$response = null;
			$result = null;
			$build = [];

			if ( function_exists( 'aai_api_response' ) ) {
				$response = aai_api_response( $this->api_videos_url, 'GET' );
			}

			if ( $response ) {

				$result = json_decode($response, true);

				//ERIK REMOVE
				//\Drupal::logger('aai_videos')->notice('Results from videos API call: ' . var_dump($result['data']));
				$formatted_videos = $this->sortVideos( $result['data'] );

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

		public function getRankdedVotesList() {
			$response = null;
			$result = null;
			$build = [];

			if ( function_exists( 'aai_api_response' ) ) {
				$response = aai_api_response( $this->api_videos_url, 'GET' );
			}

			if ( $response ) {

				$result = json_decode($response, true);

				//ERIK REMOVE
				//\Drupal::logger('aai_videos')->notice('Results from videos API call: ' . var_dump($result['data']));
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

		public function getRankdedViewsList() {
			$response = null;
			$result = null;
			$build = [];

			if ( function_exists( 'aai_api_response' ) ) {
				$response = aai_api_response( $this->api_videos_url, 'GET' );
			}

			if ( $response ) {

				$result = json_decode($response, true);

				//ERIK REMOVE
				//\Drupal::logger('aai_videos')->notice('Results from videos API call: ' . var_dump($result['data']));
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

		public function sortVideos( $videos ) {
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

			//ERIK REMOVE
			//\Drupal::logger('aai_videos')->notice('Sorted Videos: <pre><code>' . print_r($videos, true) . '</code></pre>');

			return $formatted_videos;
		}

		public function sortRankedVideos( $videos, $rank_type ) {
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

			//ERIK REMOVE
			//\Drupal::logger('aai_videos')->notice('Sorted Videos: <pre><code>' . print_r($videos, true) . '</code></pre>');

			return $formatted_videos;
		}

		public function sortByRecent( $a, $b ) {
			if ($a['attributes']["created_at"] == $b['attributes']["created_at"]) {
				return 0;
			}
			return ($a['attributes']["created_at"] > $b['attributes']["created_at"]) ? -1 : 1;
		}

		public function sortByVotes( $a, $b ) {
			if ($a['attributes']["vote_tally"] == $b['attributes']["vote_tally"]) {
				return 0;
			}
			return ($a['attributes']["vote_tally"] > $b['attributes']["vote_tally"]) ? -1 : 1;
		}

		public function sortByViews( $a, $b ) {
			if ($a['attributes']["view_tally"] == $b['attributes']["view_tally"]) {
				return 0;
			}
			return ($a['attributes']["view_tally"] > $b['attributes']["view_tally"]) ? -1 : 1;
		}
	}
?>