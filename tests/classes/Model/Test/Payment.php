<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Test_Payment extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'freezable' => Jam::behavior('freezable', array(
					'parent' => 'test_purchase'
				))
			))
			->associations(array(
				'test_purchase' => Jam::association('belongsto')
			));
	}
}