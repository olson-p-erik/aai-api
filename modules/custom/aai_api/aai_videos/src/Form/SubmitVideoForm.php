<?php

namespace Drupal\aai_videos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Component\Utility\UrlHelper;

/**
 * The form class for submitting a new video
 */
class SubmitVideoForm extends FormBase {
	
	private $api_videos_url = 'https://proofapi.herokuapp.com/videos';

	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {
		return 'submit_video_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
		$form['#prefix'] = '<div id="submit_video_form">';
		$form['#suffix'] = '</div>';

		//This is where we will display any form errors
		$form['status_messages'] = [
			'#type' => 'status_messages',
			'#weight' => -10,
		];

		$form['video_title'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Video Title'),
			'#maxlength' => 255,
			'#required' => TRUE,
		];

		$form['video_url'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Video URL'),
			'#maxlength' => 255,
			'#required' => TRUE,
		];

		
		$form['actions'] = ['#type' => 'actions'];

		$form['actions']['send'] = [
			'#type' => 'submit',
			'#value' => $this->t('Submit Video'),
			'#attributes' => [
				'class' => [
					'use-ajax',
				],
			],
			'#ajax' => [
				'callback' => [$this, 'submitModalFormAjax'],
				'event' => 'click',
			],
		];

		$form['#attached']['library'][] = 'core/drupal.dialog.ajax';

		return $form;
	}

	/**
	 * This validation method checks for a valid URL and if that succeeds
	 * calls the submitVideo method, adding any errors to be displayed
	 */
	public function validateForm( array &$form, FormStateInterface $form_state ) {
		$video_url = $form_state->getValue( 'video_url' );

		if ( !UrlHelper::isValid( $video_url, true ) ) {
			$form_state->setErrorByName( 'video_url', $this->t( 'Please provide a valid url for your video' ) );

			//Jump out of the validation before sending an API request, since this isn't even a valid URL
			return;
		}
		elseif ( ( date('N') >= 6 ) ) {
			$form_state->setErrorByName( 'video_title', $this->t( 'Sorry, you cannot submit videos on the weekend. You know that.' ) );

			//Jump out of the validation before sending an API request, since this isn't even a valid URL
			return;
		}

		//Ideally we would submit the video in the submitModalFormAjax method, but this way we can display errors on the form inputs
		$this->submitVideo( $form_state );
	}

	/**
	 * AJAX callback handler that displays any errors or a success message.
	 * @param  array              $form       form data
	 * @param  FormStateInterface $form_state The form state object
	 * @return AjaxResponse                   The ajax response to be sent back to the submit video form
	 */
	public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
		$response = new AjaxResponse();

		// If there are any form errors, re-display the form.
		if ( $form_state->hasAnyErrors() ) {
			$response->addCommand( new ReplaceCommand( '#submit_video_form', $form ) );
		}
		else {
			$response->addCommand( new OpenModalDialogCommand( "Nice!", 'Your video has been submitted, go tell your friends.', ['width' => 800]) );
		}

		return $response;
	}

	/**
	 * This method makes the actual API call to submit a video and if there are any errors,
	 * calls the formatFormErrors method to add any errors to the $form_state object
	 * @param  FormStateInterface &$form_state The form state object
	 * @return void
	 */
	public function submitVideo( FormStateInterface &$form_state ) {
		$response = null;
		$result = null;

		$video_title = $form_state->getValue( 'video_title' );
		$video_url = $form_state->getValue( 'video_url' );
		$params = [
			'title' => $video_title, 
			'url' => $video_url, 
		];

		if ( function_exists( 'aai_api_response' ) ) {
			$response = aai_api_response( $this->api_videos_url, 'POST', $params );
		}

		if ( $response ) {
			$result = json_decode( $response, true );

			if ( !empty( $result['errors'] ) ) {
				$this->formatFormErrors( $result['errors'], $form_state );
			}
		}

		return;
	}

	/**
	 * This method looks at the errors returned in the JSON response
	 * and adds them to the form_state object so they can be properly displayed on the form
	 * @param  array              $result_errors [description]
	 * @param  FormStateInterface &$form_state   [description]
	 * @return void
	 */
	public function formatFormErrors( array $result_errors, FormStateInterface &$form_state ) {
		foreach ( $result_errors as $error ) {

			if ( $error['status'] == 401 ) {
				//Somehow our token has been invalidated, uh oh
				$form_state->setErrorByName( 'video_title', $this->t( 'Unauthorized request - The API token was invalidated.' ) );
			}
			elseif ( $error['source']['pointer'] == '/data/attributes/url' ) {
				//The only url error we could get here is due to the url already being taken
				$form_state->setErrorByName( 'video_url', $this->t( 'This URL has already been taken, submit a new video.' ) );
			}
			elseif ( $error['source']['pointer'] == '/data/attributes/slug' ) {
				//Someone's already used this title, since the slug is generated from the title
				$form_state->setErrorByName( 'video_title', $this->t( 'This title has already been taken, please be more original.' ) );
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm( array &$form, FormStateInterface $form_state ) {}

	/**
	 * Gets the configuration names that will be editable.
	 *
	 * @return array
	 *   An array of configuration object names that are editable if called in
	 *   conjunction with the trait's config() method.
	 */
	protected function getEditableConfigNames() {
		return ['config.submit_video_form'];
	}

}