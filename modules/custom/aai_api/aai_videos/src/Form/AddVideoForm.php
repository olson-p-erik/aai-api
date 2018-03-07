<?php

namespace Drupal\aai_videos\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;


class AddVideoForm extends FormBase {

	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {
		return 'add_new_video_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
		$form['open_modal'] = [
			'#type' => 'link',
			'#title' => $this->t('Add New Video'),
			'#url' => Url::fromRoute('submit_video.form'),
			'#attributes' => [
				'class' => [
					'use-ajax',
					'button',
				],
			],
		];

		// Attach the library for pop-up dialogs/modals.
		$form['#attached']['library'][] = 'core/drupal.dialog.ajax';

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		
	}

	/**
	 * Gets the configuration names that will be editable.
	 *
	 * @return array
	 *   An array of configuration object names that are editable if called in
	 *   conjunction with the trait's config() method.
	 */
	protected function getEditableConfigNames() {
		return ['config.add_new_video_form'];
	}

}