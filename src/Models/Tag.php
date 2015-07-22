<?php namespace Cviebrock\EloquentTaggable\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Cviebrock\EloquentTaggable\Util;


/**
 * Class Tag
 * @package Cviebrock\EloquentTaggable\Models
 */
class Tag extends Eloquent {

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
		'normalized'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
	public function taggable() {
		return $this->morphTo();
	}


	/**
	 * @param $value
     */
	public function setNameAttribute($value) {
		$value = trim($value);
		$this->attributes['name'] = $value;
		$this->attributes['normalized'] = Util::normalizeName($value);
	}


	/**
	 * @param $name
	 * @return static
     */
	public static function findOrCreate($name) {
		if (!$tag = static::findByName($name)) {
			$tag = static::create(compact('name'));
		}

		return $tag;
	}

	/**
	 * @param $name
	 * @return mixed
     */
	public static function findByName($name) {
		$normalized = Util::normalizeName($name);

		return static::where('normalized', $normalized)->first();
	}

	/**
	 * @return mixed
     */
	public function __toString() {
		return $this->getAttribute('name');
	}
}
