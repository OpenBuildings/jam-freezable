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

	public function test_initialize()
	{
		$meta = Jam::meta('test');

		$this->assertInstanceOf(
			'Jam_Field_Boolean',
			$meta->field('is_frozen')
		);
		$this->assertInstanceOf(
			'Jam_Field_Boolean',
			$meta->field('is_just_frozen')
		);

		$behaviors = $meta->behaviors();
		$freezable = $behaviors['freezable'];

		$this->assertSame(array('child'), $freezable->_associations);
		$this->assertSame(array(), $freezable->_fields);
		$this->assertNull($freezable->_parent);
		$this->assertFalse($freezable->_skippable);
		$this->assertSame(array(), $freezable->_skippable_field_options);
	}

	public function test_initialize_with_parent()
	{
		$meta = Jam::meta('test_child');

		$this->assertNull($meta->field('is_frozen'));
		$this->assertNull($meta->field('is_just_frozen'));

		$behaviors = $meta->behaviors();
		$freezable = $behaviors['freezable'];

		$this->assertSame(array(), $freezable->_associations);
		$this->assertSame(array('value'), $freezable->_fields);
		$this->assertSame('test', $freezable->_parent);
		$this->assertFalse($freezable->_skippable);
		$this->assertSame(array(), $freezable->_skippable_field_options);
	}

	public function test_initialize_skippable()
	{
		$meta = Jam::meta('test_purchase_item');

		$this->assertInstanceOf(
			'Jam_Field_Boolean',
			$meta->field(Jam_Behavior_Freezable::DEFAULT_SKIPPABLE_FIELD)
		);

		$behaviors = $meta->behaviors();
		$freezable = $behaviors['freezable'];
		$this->assertSame(
			Jam_Behavior_Freezable::DEFAULT_SKIPPABLE_FIELD,
			$freezable->_skippable
		);
		$this->assertSame(array(), $freezable->_skippable_field_options);

		$meta = Jam::meta('test_skippable');
		$this->assertInstanceOf(
			'Jam_Field_Boolean',
			$meta->field('is_meldable')
		);

		$this->assertTrue($meta->field('is_meldable')->default);

		$behaviors = $meta->behaviors();
		$freezable = $behaviors['freezable'];

		$this->assertSame('is_meldable', $freezable->_skippable);
		$this->assertSame(array(
			'default' => TRUE
		), $freezable->_skippable_field_options);
	}

	/**
	 * @covers Jam_Behavior_Freezable::call_associations_method
	 */
	public function test_call_associations_method()
	{
		$purchase = Jam::find('test_purchase', 1);

		$store_purchase1 = $this->getMock('Model_Test_Store_Purchase', array(
			'test_method'
		), array(
			'test_store_purchase'
		));

		$store_purchase1
			->expects($this->once())
			->method('test_method');

		$store_purchase2 = $this->getMock('Model_Test_Store_Purchase', array(
			'test_method'
		), array(
			'test_store_purchase'
		));

		$store_purchase2
			->expects($this->once())
			->method('test_method');

		$purchase->test_store_purchases = array(
			$store_purchase1,
			$store_purchase2,
		);

		$payment = $this->getMock('Model_Test_Payment', array(
			'test_method'
		), array(
			'test_payment'
		));

		$payment
			->expects($this->once())
			->method('test_method');

		$purchase->test_payment = $payment;

		$behaviors = $purchase->meta()->behaviors();

		$behaviors['freezable']
			->call_associations_method($purchase, 'test_method');
	}

	/**
	 * @covers Jam_Behavior_Freezable::model_call_freeze
	 */
	public function test_model_call_freeze()
	{
		$purchase = $this->getMock('Model_Test_Purchase', array(
			'monetary'
		), array(
			'test_purchase'
		));

		$monetary = new stdClass(array(
			'abc' => 'xyz'
		));

		$purchase
			->expects($this->once())
			->method('monetary')
				->will($this->returnValue($monetary));

		$store_purchase1 = $this->getMock('Model_Test_Store_Purchase', array(
			'freeze'
		), array(
			'test_store_purchase'
		));

		$store_purchase1
			->expects($this->once())
			->method('freeze');

		$store_purchase2 = $this->getMock('Model_Test_Store_Purchase', array(
			'freeze'
		), array(
			'test_store_purchase'
		));

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
	 * @covers Jam_Behavior_Freezable::model_call_freeze
	 */
	public function test_freeze_freezes_only_null_fields()
	{
		$purchase_item1 = $this->getMock('Model_Test_Purchase_Item', array(
			'price'
		), array(
			'test_purchase_item'
		));

		$purchase_item1
			->expects($this->once())
			->method('price')
			->will($this->returnValue(10.00));

		$purchase_item2 = $this->getMock('Model_Test_Purchase_Item', array(
			'price'
		), array(
			'test_purchase_item'
		));

		$purchase_item2
			->expects($this->never())
			->method('price')
			->will($this->returnValue(10.00));

		$purchase_item2->price = 7.00;

		$store_purchase = Jam::build('test_store_purchase', array(
			'test_items' => array(
				$purchase_item1,
				$purchase_item2,
			)
		));

		$store_purchase->freeze();

		$this->assertSame(10.00, $store_purchase->test_items[0]->price);
		$this->assertSame(7.00, $store_purchase->test_items[1]->price);
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

		$store_purchase1 = $this->getMock('Model_Test_Store_Purchase', array(
			'unfreeze'
		), array(
			'test_store_purchase'
		));

		$store_purchase1
			->expects($this->once())
			->method('unfreeze');

		$store_purchase2 = $this->getMock('Model_Test_Store_Purchase', array(
			'unfreeze'
		), array(
			'test_store_purchase'
		));

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
		$this->assertTrue($purchase
			->test_store_purchases[0]
			->test_items[0]
			->is_frozen());

		$purchase->unfreeze();

		$this->assertFalse($purchase->is_frozen());
		$this->assertFalse($purchase->test_store_purchases[0]->is_frozen());
		$this->assertFalse($purchase
			->test_store_purchases[0]
			->test_items[0]
			->is_frozen());
	}

	/**
	 * @covers Jam_Behavior_Freezable::model_call_is_just_frozen
	 */
	public function test_model_call_is_just_frozen()
	{
		$purchase = Jam::find('test_purchase', 2);

		$this->assertFalse($purchase->is_just_frozen());
		$this->assertFalse($purchase
			->test_store_purchases[0]
			->is_just_frozen());
		$this->assertFalse($purchase
			->test_store_purchases[0]
			->test_items[0]
			->is_just_frozen());

		$purchase->freeze();

		$this->assertTrue($purchase->is_just_frozen());
		$this->assertTrue($purchase
			->test_store_purchases[0]
			->is_just_frozen());
		$this->assertTrue($purchase
			->test_store_purchases[0]
			->test_items[0]->is_just_frozen());

		$purchase->save();

		$this->assertFalse($purchase->is_just_frozen());
		$this->assertFalse($purchase
			->test_store_purchases[0]
			->is_just_frozen());
		$this->assertFalse($purchase
			->test_store_purchases[0]
			->test_items[0]->is_just_frozen());
	}

	public function test_skippable_field()
	{
		$store_purchase = Jam::find('test_store_purchase', 3);

		$store_purchase->freeze();
		$this->assertNull($store_purchase->test_items[0]->price);
		$this->assertNotNull($store_purchase->test_items[1]->price);

		$store_purchase->test_items[0]->is_not_freezable = FALSE;

		$store_purchase->freeze();
		$this->assertNotNull($store_purchase->test_items[0]->price);
		$this->assertNotNull($store_purchase->test_items[1]->price);

		$store_purchase->unfreeze();
		$this->assertNull($store_purchase->test_items[0]->price);
		$this->assertNull($store_purchase->test_items[1]->price);

		$store_purchase->freeze();
		$this->assertNotNull($store_purchase->test_items[0]->price);
		$this->assertNotNull($store_purchase->test_items[1]->price);
	}
}