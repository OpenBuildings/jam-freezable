<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * @package    Openbuildings/jam-freezable
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  2013 OpenBuildings, Inc.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Model_Test_Purchase_Item extends Jam_Model {

	const PRODUCT = 'product';

	const FILTER_PREFIX = 'matches_filter_';
	
	/**
	 * @codeCoverageIgnore
	 */
	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'freezable' => Jam::behavior('freezable', array(
					'fields' => 'price',
					'parent' => 'test_store_purchase'
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
	 * Check if the purchase item is the same as another purchase item,
	 * creterias are reference id, model and purchase item type.
	 *
	 * @param  Model_Test_Purchase_Item $item
	 * @return boolean
	 */
	public function is_same(Model_Test_Purchase_Item $item)
	{
		return ($item->reference_id
			AND $this->reference_id == $item->reference_id
			AND $this->reference_model == $item->reference_model
			AND $this->type == $item->type);
	}

	/**
	 * Return the monetary for this purchase item, get it from parent store_purchase
	 * @return OpenBuildings\Monetary\Montary
	 */
	public function monetary()
	{
		return $this->get_insist('store_purchase')->monetary();
	}

	/**
	 * Return the currency for this purchase item, get it from parent store_purchase
	 * @return string
	 */
	public function currency()
	{
		return $this->get_insist('store_purchase')->currency();
	}

	/**
	 * Compute the price of the reference, converted to this purchase item currency and Monetary
	 * @return Jam_Price
	 */
	public function compute_price()
	{
		return round(mt_rand(1, 10000) / 100, 2);
	}

	/**
	 * Freezable implementation, return compute_price or price field
	 * @return Jam_Price
	 */
	public function price()
	{
		return ($this->price === NULL) ? $this->compute_price() : $this->price;
	}
}
