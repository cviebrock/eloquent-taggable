<?php namespace Cviebrock\EloquentTaggable;

use Illuminate\Database\Eloquent\Model as Eloquent;


class Tag extends Eloquent {

	protected $table = 'taggable_tags';

	protected $fillable = [
		'name',
		'normalized'
	];

	public function taggable() {
		return $this->morphTo();
	}


	public function setNameAttribute($value) {
		$value = trim($value);
		$this->attributes['name'] = $value;
		$this->attributes['normalized'] = Util::normalizeName($value);
	}


	public static function findOrCreate($name) {
		if (!$tag = static::findByName($name)) {
			$tag = static::create(compact('name'));
		}

		return $tag;
	}

	public static function findByName($name) {
		$normalized = Util::normalizeName($name);

		return static::where('normalized', $normalized)->first();
	}

	public function __toString() {
		return $this->getAttribute('name');
	}
}
