<?php

namespace Drupal\aai_videos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This class handles the voting for a video through AJAX
 */
class VotesController extends ControllerBase {

	private $api_videos_url = 'https://proofapi.herokuapp.com/videos';

	public function voteVideo( $vote, $video_id ) {
		$response = null;
		$result = null;

		$params = [
			'opinion' => $vote 
		];

		if ( ( date('N') >= 6 ) ) {
			//Users are not allowed to vote on weekends
			$ajax_response = ['voted' => false];
		}
		else {
			if ( function_exists( 'aai_api_response' ) ) {
				$video_url = $this->api_videos_url . '/' . $video_id . '/votes';
				$response = aai_api_response( $video_url, 'POST', $params );
			}

			if ( $response ) {
				$result = json_decode($response, true);
			}
			$ajax_response = ['voted' => true];
		}

		return new AjaxResponse($ajax_response);
	}
}