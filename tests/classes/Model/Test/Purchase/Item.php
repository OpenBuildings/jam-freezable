<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * @package    Openbuildings/jam-freezable
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  2013 OpenBuildings, Inc.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Model_Test_Purchase_Item extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'freezable' => Jam::behavior('freezable', array(
					'fields' => 'price',
					'parent' => 'test_store_purchase',
					'skippable' => TRUE
				)),
			))
			->associations(array(
				'test_store_purchase' => Jam::association('belongsto', array(
					'inverse_of' => 'test_items'
				)),
			))
			->fields(array(
				'id' => Jam::field('primary'),
				'price' => Jam::field('float'),
			));
	}

	/**
	 * Return the monetary for this purchase item.
	 * Get it from parent store_purchase.
	 *
	 * @return stdClass
	 */
	public function monetary()
	{
		return $this->get_insist('test_store_purchase')->monetary();
	}

	/**
	 * Freezable implementation, return random price (dynamic value)
	 * or price field (frozen) if available
	 *
	 * @return Jam_Price
	 */
	public function price()
	{
		return ($this->price === NULL)
			? (round(mt_rand(1, 10000) / 100, 2))
			: $this->price;
	}
}
