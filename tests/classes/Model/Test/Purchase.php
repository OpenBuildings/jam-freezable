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
				'monetary'        => Jam::field('serialized'),
			));
	}

	/**
	 * Freezable field. Return Monetary::instance() if not frozen
	 * @return OpenBuildings\Monetary\Monetary
	 */
	public function monetary()
	{
		return $this->monetary ? $this->monetary : new stdClass;
	}
}
