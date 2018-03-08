<?php

namespace Drupal\aai_videos\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;

/**
 * This controller class contains the callback method used by the AddVideoForm,
 * which builds the submit form and opens the SubmitVideoForm in a modal
 */
class SubmitVideoController extends ControllerBase {

	/**
	 * The form builder.
	 *
	 * @var \Drupal\Core\Form\FormBuilder
	 */
	protected $form_builder;

	/**
	 * The SubmitVideoController constructor.
	 *
	 * @param FormBuilder $form_builder The form builder.
	 */
	public function __construct(FormBuilder $form_builder) {
		$this->form_builder = $form_builder;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param ContainerInterface $container The Drupal service container.
	 *
	 * @return static
	 */
	public static function create(ContainerInterface $container) {
		return new static(
			$container->get( 'form_builder' )
		);
	}

	/**
	 * Callback for opening the Submit Video modal form.
	 */
	public function openSubmitVideoForm() {
		$response = new AjaxResponse();

		//Get the modal form using the form builder.
		$modal_form = $this->form_builder->getForm( 'Drupal\aai_videos\Form\SubmitVideoForm' );

		//Add an AJAX command to open a modal dialog with the form as the content.
		$response->addCommand( new OpenModalDialogCommand( 'Submit Video Form', $modal_form, ['width' => '800'] ) );

		return $response;
	}

}