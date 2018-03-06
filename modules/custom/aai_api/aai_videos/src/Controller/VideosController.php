<?php

	namespace Drupal\aai_videos\Controller;

	use Drupal\Core\Controller\ControllerBase;

	class VideosController extends ControllerBase {

		/**
		 * Callback function to get the data from REST API
		 */
		public function getRecentList() {

			$response = null;
			$result = null;
			$build = array();

			if ( function_exists( 'aai_api_response' ) ) {
				$response = aai_api_response( 'https://proofapi.herokuapp.com/videos', 'GET' );
			}

			if ( $response ) {

				$result = json_decode($response, true);
				$data = array();

				$data['title'] = 'Most Recent Videos';
				$data['videos'] = $result['data'];

				$build = array(
					//'#theme' => 'videos_recent_list',
					'#title' => 'Most Recent Videos',
					'#pagehtml' => 'All the greatest new videos',
					'#data' => $data
				);
			}
			return $build;
		}
	}
?>