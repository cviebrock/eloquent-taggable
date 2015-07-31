<?php

namespace Cviebrock\EloquentTaggable\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Cviebrock\EloquentTaggable\Util;

/**
 * Class Tag.
 */
class Tag extends Eloquent
{
	/**
	 * @var string
	 */
	protected $table = 'taggable_tags';

	/**
	 * @var string
	 */
	protected $primaryKey = 'tag_id';

	/**
	 * @var array
	 */
	protected $fillable = [
		'name',
		'normalized',
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function taggable()
	{
		return $this->morphTo();
	}

	/**
	 * Set the name attribute on the model.
	 *
	 * @param $value
	 */
	public function setNameAttribute($value)
	{
		$value = trim($value);
		$this->attributes['name'] = $value;
		$this->attributes['normalized'] = Util::normalizeName($value);
	}

	/**
	 * Find a tag by its name or create a new one.
	 *
	 * @param $name
	 *
	 * @return static
	 */
	public static function findOrCreate($name)
	{
		if (!$tag = static::findByName($name)) {
			$tag = static::create(compact('name'));
		}

		return $tag;
	}

	/**
	 * Find a tag by its name.
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function findByName($name)
	{
		$normalized = Util::normalizeName($name);

		return static::where('normalized', $normalized)->first();
	}

	/**
	 * @return mixed
	 */
	public function __toString()
	{
		return $this->getAttribute('name');
	}
}
