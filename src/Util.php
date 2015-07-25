<?php

namespace Cviebrock\EloquentTaggable;

use Cviebrock\EloquentTaggable\Contracts\Taggable;
use Illuminate\Support\Facades\DB;

/**
 * Class Util.
 */
class Util
{
	/**
	 * Build an array of Tags from a string in which the tags are delimited
	 * by the character defined in config('taggable.delimiters').
	 *
	 * @param $tags
	 *
	 * @return array
	 */
	public static function buildTagArray($tags)
	{
		if (is_array($tags)) {
			return $tags;
		}

		if (is_string($tags)) {
			$delimiters = config('taggable.delimiters', ',');

			return preg_split('#['.preg_quote($delimiters, '#').']#', $tags, null, PREG_SPLIT_NO_EMPTY);
		}

		return (array) $tags;
	}

	/**
	 * Build a string in which the Tags are delimited by the character
	 * defined in config('taggable.delimiters').
	 *
	 * @param Taggable $model
	 * @param $field
	 *
	 * @return string
	 */
	public static function makeTagList(Taggable $model, $field)
	{
		$tags = static::makeTagArray($model, $field);

		return self::joinArray($tags->toArray());
	}

	/**
	 * Build an array which contains all Tags of the given model.
	 *
	 * @param Taggable $model
	 * @param $field
	 *
	 * @return mixed
	 */
	public static function makeTagArray(Taggable $model, $field)
	{
		return $model->tags->lists($field, 'tag_id');
	}

	/**
	 * Normalize the tag. Defaults to mb_strtolower($name).
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function normalizeName($name)
	{
		$normalizer = config('taggable.normalizer');

		return call_user_func($normalizer, $name);
	}

	/**
	 * Get all tags for the given class.
	 *
	 * @param $className
	 *
	 * @return mixed
	 */
	public static function getAllTags($className)
	{
		return DB::table('taggable_taggables')->distinct()
			->where('taggable_type', '=', $className)
			->join('taggable_tags', 'taggable_taggables.taggable_id', '=', 'taggable_tags.tag_id')
			->orderBy('taggable_tags.normalized')
			->lists('taggable_tags.normalized');
	}

	/**
	 * Join the given tags into a string in which the tags are delimited
	 * by the character defined in config('taggable.delimiters').
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	public static function joinArray(array $array)
	{
		$delimiters = config('taggable.delimiters', ',');
		$glue = substr($delimiters, 0, 1);

		return implode($glue, $array);
	}
}
