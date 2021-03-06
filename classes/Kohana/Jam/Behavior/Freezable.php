<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Jam behavior for freezing computed values into the database
 *
 * @package    openbuildings/jam-freezable
 * @author     Ivan Kerin <ivank@gmail.com>
 * @copyright  2013 OpenBuildings, Inc.
 * @license    http://spdx.org/licenses/BSD-3-Clause
 */
class Kohana_Jam_Behavior_Freezable extends Jam_Behavior {

	const DEFAULT_SKIPPABLE_FIELD = 'is_not_freezable';

	public $_associations;

	public $_fields;

	public $_parent;

	public $_skippable = FALSE;

	public $_skippable_field_options = array();

	public function initialize(Jam_Meta $meta, $name)
	{
		parent::initialize($meta, $name);

		$this->_associations = (array) $this->_associations;
		$this->_fields = (array) $this->_fields;

		if ( ! $this->_parent)
		{
			$meta->field('is_frozen', Jam::field('boolean'));
			$meta->field('is_just_frozen', Jam::field('boolean', array(
				'in_db' => FALSE
			)));
		}

		if ($this->_skippable)
		{
			$this->_skippable = is_string($this->_skippable)
				? $this->_skippable
				: static::DEFAULT_SKIPPABLE_FIELD;

			$meta->field(
				$this->_skippable,
				Jam::field('boolean', $this->_skippable_field_options)
			);
		}
	}

	/**
	 * After saving the model it is not considered "just_frozen"
	 *
	 * @param  Jam_Model      $model
	 * @param  Jam_Event_Data $data
	 */
	public function model_after_save(Jam_Model $model, Jam_Event_Data $data)
	{
		$model->is_just_frozen = FALSE;
	}

	/**
	 * Add validation so that if you change any fields that are considered frozen,
	 * it would add an error.
	 * In case of associations, if the count of the items has been changed,
	 * also add an error.
	 *
	 * @param  Jam_Model      $model
	 * @param  Jam_Event_Data $data
	 */
	public function model_after_check(Jam_Model $model, Jam_Event_Data $data)
	{
		if ($model->loaded() AND $model->is_frozen() AND ! $model->is_just_frozen())
		{
			foreach ($this->_associations as $name)
			{
				if ($model->meta()->association($name) instanceof Jam_Association_Collection
				 AND count($model->{$name}) !== count($model->{$name}->original()))
				{
					$model->errors()->add($name, 'frozen');
				}
			}

			foreach ($this->_fields as $name)
			{
				if ($model->changed($name))
				{
					$model->errors()->add($name, 'frozen');
				}
			}
		}
	}

	/**
	 * Call the given method an all the child associations.
	 *
	 * @param  Jam_Model $model
	 * @param  string    $method_name
	 */
	public function call_associations_method(Jam_Model $model, $method_name)
	{
		foreach ($this->_associations as $name)
		{
			if ($model->meta()->association($name) instanceof Jam_Association_Collection)
			{
				foreach ($model->{$name}->as_array() as $item)
				{
					$item->{$method_name}();
				}
			}
			elseif ($model->{$name})
			{
				$model->{$name}->{$method_name}();
			}

			$model->{$name} = $model->{$name};
		}
	}

	/**
	 * Freeze all the fields in this method, also call "freeze" on all children.
	 *
	 * @param  Jam_Model      $model
	 * @param  Jam_Event_Data $data
	 * @return Jam_Model                self
	 */
	public function model_call_freeze(Jam_Model $model, Jam_Event_Data $data)
	{
		$this->call_associations_method($model, 'freeze');

		foreach ($this->_fields as $name)
		{
			if ($this->_skippable AND $model->{$this->_skippable})
				continue;

			if ($model->{$name} === NULL)
			{
				$model->{$name} = $model->{$name}();
			}
		}

		if ( ! $this->_parent)
		{
			$model->is_frozen = TRUE;
			$model->is_just_frozen = TRUE;
		}

		$data->return = $model;
	}

	/**
	 * Unfreeze all the fields in this method.
	 * Also call "unfreeze" on all children.
	 *
	 * @param  Jam_Model      $model
	 * @param  Jam_Event_Data $data
	 * @return Jam_Model                self
	 */
	public function model_call_unfreeze(Jam_Model $model, Jam_Event_Data $data)
	{
		$this->call_associations_method($model, 'unfreeze');

		foreach ($this->_fields as $name)
		{
			$model->{$name} = NULL;
		}

		if ( ! $this->_parent)
		{
			$model->is_frozen = FALSE;
			$model->is_just_frozen = FALSE;
		}

		$data->return = $model;
	}

	/**
	 * Check if the model is frozen.
	 * Go up the chain of parents until a parent with is_frozen flag is reached.
	 * That way all the children use only the flag of the parent
	 * @param  Jam_Model      $model
	 * @param  Jam_Event_Data $data
	 * @return boolean
	 */
	public function model_call_is_frozen(Jam_Model $model, Jam_Event_Data $data)
	{
		if ($this->_parent)
		{
			$data->return = $model->get_insist($this->_parent)->is_frozen();
		}
		else
		{
			$data->return = $model->is_frozen;
		}
	}

	/**
	 * Check if the object has been frozen, but is not yet saved.
	 * Go up the chain of parents until it reaches the root, whose
	 * is_just_frozen flag is returned.
	 *
	 * @param  Jam_Model      $model
	 * @param  Jam_Event_Data $data
	 * @return boolean
	 */
	public function model_call_is_just_frozen(Jam_Model $model, Jam_Event_Data $data)
	{
		if ($this->_parent)
		{
			$data->return = $model
				->get_insist($this->_parent)
				->is_just_frozen();
		}
		else
		{
			$data->return = $model->is_just_frozen;
		}
	}
}
