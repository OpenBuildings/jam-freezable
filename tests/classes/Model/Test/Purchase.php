<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * @package    Openbuildings\Purchases
 * @author     Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2013 OpenBuildings Ltd.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Model_Test_Purchase extends Jam_Model {

	protected $_monetary;

	/**
	 * @codeCoverageIgnore
	 */
	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'freezable' => Jam::behavior('freezable', array('fields' => 'monetary', 'associations' => 'test_store_purchases')),
			))
			->associations(array(
				'test_store_purchases' => Jam::association('hasmany', array(
					'inverse_of' => 'test_purchase',
					'foreign_model' => 'test_store_purchase',
				)),
			))
			->fields(array(
				'id'              => Jam::field('primary'),
				'currency'        => Jam::field('string'),
				'monetary'        => Jam::field('serialized'),
			));
	}

	/**
	 * Iterate through the existing store_purchases and return the one that is linked to this store.
	 * If none exist build one and return it
	 * @param  Model_Store $store
	 * @return Model_Store_Purchase
	 */
	public function find_or_build_store_purchase($store)
	{
		$store_purchases = $this->store_purchases->as_array('store_id');

		if (isset($store_purchases[$store->id()]))
		{
			$store_purchase = $store_purchases[$store->id()];
		}
		else
		{
			$store_purchase = $this->store_purchases->build(array('store' => $store));
		}

		return $store_purchase;
	}

	/**
	 * Add item to the store_purchase that matches the store given, if it exists, update the quantity, if the store_purchase does not exist, build it.
	 * @trigger model.add_item event, pass $new_item
	 * @param Model_Store              $store
	 * @param Model_Test_Purchase_Item $new_item
	 */
	public function add_item($store, Model_Test_Purchase_Item $new_item)
	{
		$this
			->find_or_build_store_purchase($store)
				->add_or_update_item($new_item);

		$this->meta()->events()->trigger('model.add_item', $this, array($new_item));

		return $this;
	}

	/**
	 * Freezable field. Return Monetary::instance() if not frozen
	 * @return OpenBuildings\Monetary\Monetary
	 */
	public function monetary()
	{
		return $this->monetary ? $this->monetary : new stdClass;
	}

	/**
	 * Return the currency for all the field in the purchase
	 * @return string
	 */
	public function currency()
	{
		return $this->currency;
	}

	/**
	 * The currency used in "humanizing" any of the price fields in the purchase
	 * @return string
	 */
	public function display_currency()
	{
		return $this->currency;
	}

	/**
	 * Return purchase_items, aggregated from all the store_purchases. Can pass filters.
	 * @param  array $types filters
	 * @return array        Model_Test_Purchase_Items
	 */
	public function items($types = NULL)
	{
		$items = array();

		foreach ($this->store_purchases->as_array() as $store_purchase)
		{
			$items = array_merge($items, $store_purchase->items($types));
		}

		return $items;
	}

	/**
	 * Return the sum purchase itmes count from all store_purchases
	 * @param  array $types filters
	 * @return integer
	 */
	public function items_count($types = NULL)
	{
		return count($this->items($types));
	}

	/**
	 * Return the sum of the quantities of all the purchase_items
	 * @param  array $types filters
	 * @return integer
	 */
	public function items_quantity($types = NULL)
	{
		$quantities = array_map(function($item) {
			return $item->quantity;
		}, $this->items($types));

		return $quantities ? array_sum($quantities) : 0;
	}

	/**
	 * Run update items on all the store_purchases
	 * @return Model_Purchase self
	 */
	public function update_items()
	{
		foreach ($this->store_purchases->as_array() as $store_purchase)
		{
			$store_purchase->update_items();
		}

		return $this;
	}

	/**
	 * Replace the purchase items from a given type, removing old items
	 * @param  array $items array of new items
	 * @param  array $types filters
	 * @return Model_Purchase        self
	 */
	public function replace_items($items, $types = NULL)
	{
		$grouped = Model_Test_Purchase_Item::group_by_store_purchase($items);
		$current = $this->store_purchases->as_array('id');

		$replaced = array_intersect_key($grouped, $current);
		$removed = array_diff_key($current, $grouped);

		foreach ($replaced as $index => $items)
		{
			$current[$index]->replace_items($items, $types);
		}

		$this->store_purchases->remove(array_values($removed));

		return $this;
	}

	// /**
	//  * Return the sum of all the prices from the purchase items
	//  * @param  array $types filters
	//  * @return Jam_Price
	//  */
	// public function total_price($types = NULL)
	// {
	// 	$prices = array_map(function($item) { return $item->total_price(); }, $this->items($types));
		
	// 	return Jam_Price::sum($prices, $this->currency(), $this->monetary(), $this->display_currency());
	// }

	public function recheck()
	{
		$this->store_purchases = array_map(function($item){
			return $item->set('items', $item->items);
		}, $this->store_purchases->as_array());

		return $this->check();
	}
}