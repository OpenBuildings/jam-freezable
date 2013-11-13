<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Unit tests for Jam_Behavior_Freezable
 *
 * @group jam
 * @group jam.behavior
 * @group jam.behavior.freezable
 * @package openbuildings/jam-freezable
 * @author Ivan Kerin <ikerin@gmail.com>
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Jam_Behavior_FreezableTest extends PHPUnit_Framework_TestCase {

	public function setUp()
	{
		parent::setUp();
		Database::instance()->begin();
	}

	public function tearDown()
	{
		Database::instance()->rollback();
		parent::tearDown();
	}

	/**
	 * @covers Jam_Behavior_Freezable::call_associations_method
	 */
	public function test_call_associations_method()
	{
		$purchase = Jam::find('test_purchase', 1);

		$store_purchase1 = $this->getMock('Model_Test_Store_Purchase', array('test_method'), array('test_store_purchase'));
		$store_purchase1
			->expects($this->once())
			->method('test_method');

		$store_purchase2 = $this->getMock('Model_Test_Store_Purchase', array('test_method'), array('test_store_purchase'));
		$store_purchase2
			->expects($this->once())
			->method('test_method');

		$purchase->test_store_purchases = array(
			$store_purchase1,
			$store_purchase2,
		);

		$behaviors = $purchase->meta()->behaviors();

		$behaviors['freezable']->call_associations_method($purchase, 'test_method');
	}

	/**
	 * @covers Jam_Behavior_Freezable::model_call_freeze
	 */
	public function test_model_call_freeze()
	{
		$purchase = $this->getMock('Model_Test_Purchase', array('monetary'), array('test_purchase'));
		$monetary = new stdClass(array(
			'abc' => 'xyz'
		));

		$purchase
			->expects($this->once())
			->method('monetary')
				->will($this->returnValue($monetary));

		$store_purchase1 = $this->getMock('Model_Test_Store_Purchase', array('freeze'), array('test_store_purchase'));
		$store_purchase1
			->expects($this->once())
			->method('freeze');

		$store_purchase2 = $this->getMock('Model_Test_Store_Purchase', array('freeze'), array('test_store_purchase'));
		$store_purchase2
			->expects($this->once())
			->method('freeze');

		$purchase->test_store_purchases = array(
			$store_purchase1,
			$store_purchase2,
		);

		$result = $purchase->freeze();
		$this->assertSame($purchase, $result);

		$this->assertSame($monetary, $purchase->monetary);
		$this->assertTrue($purchase->is_frozen());
	}

	/**
	 * @covers Jam_Behavior_Freezable::model_after_check
	 * @covers Jam_Behavior_Freezable::model_after_save
	 */
	public function test_model_after_check()
	{
		$purchase = Jam::find('test_purchase', 2);

		$this->assertTrue($purchase->check());

		$purchase
			->freeze()
			->save();

		$purchase->test_store_purchases->build(array(
			'items' => array(
				array(
					'price' => 10,
				)
			)
		));

		$purchase->test_store_purchases[0]->test_items[0]->price = 122;

		$this->assertFalse($purchase->check());
		
		$purchase->unfreeze();

		$this->assertTrue($purchase->check());
	}

	/**
	 * @covers Jam_Behavior_Freezable::model_call_unfreeze
	 */
	public function test_model_call_unfreeze()
	{
		$purchase = Jam::build('test_purchase');

		$store_purchase1 = $this->getMock('Model_Test_Store_Purchase', array('unfreeze'), array('test_store_purchase'));
		$store_purchase1
			->expects($this->once())
			->method('unfreeze');

		$store_purchase2 = $this->getMock('Model_Test_Store_Purchase', array('unfreeze'), array('test_store_purchase'));
		$store_purchase2
			->expects($this->once())
			->method('unfreeze');

		$purchase->test_store_purchases = array(
			$store_purchase1,
			$store_purchase2,
		);

		$result = $purchase->unfreeze();
		$this->assertSame($purchase, $result);

		$this->assertNull($purchase->monetary);
		$this->assertFalse($purchase->is_frozen());
	}

	/**
	 * @covers Jam_Behavior_Freezable::model_call_is_frozen
	 */
	public function test_model_call_is_frozen()
	{
		$purchase = Jam::find('test_purchase', 2);

		$purchase->freeze();

		$this->assertTrue($purchase->is_frozen());
		$this->assertTrue($purchase->test_store_purchases[0]->is_frozen());
		$this->assertTrue($purchase->test_store_purchases[0]->test_items[0]->is_frozen());

		$purchase->unfreeze();

		$this->assertFalse($purchase->is_frozen());
		$this->assertFalse($purchase->test_store_purchases[0]->is_frozen());
		$this->assertFalse($purchase->test_store_purchases[0]->test_items[0]->is_frozen());
	}

	/**
	 * @covers Jam_Behavior_Freezable::model_call_is_just_frozen
	 */
	public function test_model_call_is_just_frozen()
	{
		$purchase = Jam::find('test_purchase', 2);

		$this->assertFalse($purchase->is_just_frozen());
		$this->assertFalse($purchase->test_store_purchases[0]->is_just_frozen());
		$this->assertFalse($purchase->test_store_purchases[0]->test_items[0]->is_just_frozen());

		$purchase->freeze();

		$this->assertTrue($purchase->is_just_frozen());
		$this->assertTrue($purchase->test_store_purchases[0]->is_just_frozen());
		$this->assertTrue($purchase->test_store_purchases[0]->test_items[0]->is_just_frozen());
	
		$purchase->save();

		$this->assertFalse($purchase->is_just_frozen());
		$this->assertFalse($purchase->test_store_purchases[0]->is_just_frozen());
		$this->assertFalse($purchase->test_store_purchases[0]->test_items[0]->is_just_frozen());
	}
}