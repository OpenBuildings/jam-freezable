# Freezable Behavior

[![Build Status](https://travis-ci.org/OpenBuildings/jam-freezable.png?branch=master)](https://travis-ci.org/OpenBuildings/jam-freezable)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/OpenBuildings/jam-freezable/badges/quality-score.png?s=a18a7155ede3ea670988997c93bb50a6d8b99316)](https://scrutinizer-ci.com/g/OpenBuildings/jam-freezable/)
[![Code Coverage](https://scrutinizer-ci.com/g/OpenBuildings/jam-freezable/badges/coverage.png?s=cbfda5344dfb62ae6c67098b31c277b45af1c501)](https://scrutinizer-ci.com/g/OpenBuildings/jam-freezable/)
[![Latest Stable Version](https://poser.pugx.org/openbuildings/jam-freezable/v/stable.png)](https://packagist.org/packages/openbuildings/jam-freezable)

**Freezable** is a [Jam ORM](https://github.com/OpenBuildings/jam) behavior for freezing dynamically computed values in the database.

---

Often one would have a method in the model which computes and returns a value. It could be a price, time or anything else.
The computation could be a heavy one or time sensitive (based on time, currency exchange rates and other).
Then you would need to save the value in a database column (a.k.a *freezing*) and in the future read the value from the field rather than the method.

The Freezable behavior allows you to do exactly that in an easy way.
The `freeze()`, `unfreeze()` and `is_frozen()` methos give you the convenience to easily get either the dynamically computed or the frozen value when needed.

## Usage

It has 3 parameters `associations`, `parent` and `fields`

``` php
class Some_Model extends Jam_Model {

	public static function initialize(Jam_Meta $meta)
	{
		$meta
			->behaviors(array(
				'freezable' => Jam::behavior('freezable', array(
					'fields' => 'price',
					'parent' => 'some_parent_model',
					'associations' => array('some_child', 'some_children'),
				)),
			));
	}
	//...
}
```

That means that whenever the model is *frozen* then the field named `price` will be assigned the value of the method `price()`.
And all the associations will be also *frozen*. The associations themselves have to be *freezable* (have the Freezable behavior attached) in order for this to work. And the `price()` method, as well as any other fields, have to take into account the value of the field. E.g.

``` php
public function price()
{
	return $this->price ? $this->price : $this->compute_price();
}
```

The parent association is used in order to find the value of `is_frozen`, so that only one model holds the value of the flag. So that if you call `is_frozen()` on a *freezable* that has a *parent*, then it will get that value from the parent.

## Details

TODO: add note about validation
TODO: add more examples

## License

Copyright (c) 2013 OpenBuildings, Inc. Developed by Ivan Kerin as part of [clippings.com](https://clippings.com).

Under BSD-3-Clause license, read [LICENSE](LICENSE) file.
