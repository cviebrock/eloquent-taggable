<?php namespace Cviebrock\EloquentTaggable;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Tag extends Eloquent {

	protected $table = 'taggable_tags';

	protected $fillable = array(
		'name',
		'normalized'
	);

	public function taggable()
	{
		return $this->morphTo();
	}


	public function setNameAttribute($value)
	{
		$value = trim($value);
		$this->attributes['name'] = $value;
		$this->attributes['normalized'] = static::normalizeName($value);
	}


	public static function normalizeName($value)
	{
		$normalizer = \Config::get('eloquent-taggable::normalizer');
		return call_user_func($normalizer, $value);
	}


	public static function findOrCreate($name)
	{
		if (!$tag = static::findByName($name))
		{
			$tag = static::create(compact('name'));
		}
		return $tag;
	}

	public static function findByName($name)
	{
		$normalized = static::normalizeName($name);
		return static::where('normalized',$normalized)->first();
	}

	public function __toString()
	{
		return $this->name;
	}


}