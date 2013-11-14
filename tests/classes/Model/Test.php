<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Test extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'freezable' => Jam::behavior('freezable', array(
					'associations' => 'child'
				))
			))
			->associations(array(
				'child' => Jam::association('hasone')
			));
	}
}
