<?php

namespace Drupal\aai_videos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides the 'Add Video' block.
 *
 * @Block(
 *   id = "add_new_video_block",
 *   admin_label = @Translation("Add New Video block"),
 *   category = @Translation("Custom AAI API")
 * )
 */
class AddVideoBlock extends BlockBase {
	
	public function build() {
		$form = \Drupal::formBuilder()->getForm('Drupal\aai_videos\Form\AddVideoForm');
		return $form;
	}
}