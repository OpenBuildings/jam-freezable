<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * @package    openbuildings/jam-freezable
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  2013 OpenBuildings, Inc.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Model_Test_Store_Purchase extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'freezable' => Jam::behavior('freezable', array(
					'associations' => 'test_items',
					'parent' => 'test_purchase'
				)),
			))
			->associations(array(
				'test_purchase' => Jam::association('belongsto', array(
					'inverse_of' => 'test_store_purchases'
				)),
				'test_items' => Jam::association('hasmany', array(
					'inverse_of' => 'test_store_purchase',
					'foreign_model' => 'test_purchase_item',
				)),
			))
			->fields(array(
				'id' => Jam::field('primary'),
			))
			->validator('test_purchase', array('present' => TRUE));
	}

	public function monetary()
	{
		return $this->get_insist('purchase')->monetary();
	}
}
