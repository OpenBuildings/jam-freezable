<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * @package    Openbuildings/jam-freezable
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  2013 OpenBuildings, Inc.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Model_Test_Purchase extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'freezable' => Jam::behavior('freezable', array(
					'fields'       => 'monetary',
					'associations' => array(
						'test_store_purchases',
						'test_payment'
					)
				)),
			))
			->associations(array(
				'test_store_purchases' => Jam::association('hasmany', array(
					'inverse_of'    => 'test_purchase',
					'foreign_model' => 'test_store_purchase',
				)),
				'test_payment' => Jam::association('hasone')
			))
			->fields(array(
				'id'              => Jam::field('primary'),
				'monetary'        => Jam::field('serialized'),
			));
	}

	/**
	 * Freezable field. Return stdClass if not frozen
	 * @return stdClass
	 */
	public function monetary()
	{
		return $this->monetary ? $this->monetary : new stdClass;
	}
}
