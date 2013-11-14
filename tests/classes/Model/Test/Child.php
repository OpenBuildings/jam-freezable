<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Test_Child extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'freezable' => Jam::behavior('freezable', array(
					'parent' => 'test',
					'fields' => 'value'
				))
			))
			->associations(array(
				'parent' => Jam::association('belongsto')
			))
			->fields(array(
				'value' => Jam::field('string')
			));
	}
}