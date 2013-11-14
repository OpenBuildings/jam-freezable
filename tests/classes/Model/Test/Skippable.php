<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Test_Skippable extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta->behaviors(array(
			'freezable' => Jam::behavior('freezable', array(
				'skippable' => 'is_meldable',
				'skippable_field_options' => array(
					'default' => TRUE
				)
			))
		));
	}
}