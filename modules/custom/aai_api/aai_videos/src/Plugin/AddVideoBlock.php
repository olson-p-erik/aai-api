<?php

namespace Drupal\aai_videos\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;


class AddVideoBlock extends BlockBase {
	
	public function build() {
		$form = \Drupal::formBuilder()->getForm('Drupal\aai_videos\Form\AddVideoForm');
		return $form;
	}
}