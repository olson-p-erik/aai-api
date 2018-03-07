<?php

namespace Drupal\aai_videos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;


class SubmitVideoForm extends FormBase {

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

		// This is where we will display any form errors
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
	 * AJAX callback handler that displays any errors or a success message.
	 */
	public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
		$response = new AjaxResponse();

		//ERIK REMOVE
		\Drupal::logger('aai_videos')->notice('Submit Video Form State: <pre><code>' . print_r($form_state, true) . '</code></pre>');

		// If there are any form errors, re-display the form.
		if ($form_state->hasAnyErrors()) {
			$response->addCommand(new ReplaceCommand('#submit_video_form', $form));
		}
		else {
			$response->addCommand(new OpenModalDialogCommand("Success!", 'The modal form has been submitted.', ['width' => 800]));
		}

		return $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state) {}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {}

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