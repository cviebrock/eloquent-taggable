<?php namespace Cviebrock\EloquentTaggable;

/**
 * Class Util
 * @package Cviebrock\EloquentTaggable
 */
class Util {

	/**
	 * @param $tags
	 * @return array
	 */
	public static function buildTagArray($tags) {
		if (is_array($tags)) {
			return $tags;
		}

		if (is_string($tags)) {
			$delimiters = config('taggable.delimiters', ',');

			return preg_split('#[' . preg_quote($delimiters, '#') . ']#', $tags, null, PREG_SPLIT_NO_EMPTY);
		}

		return (array) $tags;
	}

	/**
	 * @param Taggable $model
	 * @param $field
	 * @return string
	 */
	public static function makeTagList(Taggable $model, $field) {
		$tags = static::makeTagArray($model, $field);

		return Util::joinArray($tags);
	}

	/**
	 * @param Taggable $model
	 * @param $field
	 * @return mixed
	 */
	public static function makeTagArray(Taggable $model, $field) {
		return $model->tags->lists($field, 'tag_id');
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public static function normalizeName($name) {
		$normalizer = config('taggable.normalizer');

		return call_user_func($normalizer, $name);
	}

	/**
	 * @param $className
	 * @return mixed
	 */
	public static function getAllTags($className) {

		return DB::table('taggable_taggables')->distinct()
			->where('taggable_type', '=', $className)
			->join('taggable_tags', 'taggable_taggables.taggable_id', '=', 'taggable_tags.tag_id')
			->orderBy('taggable_tags.normalized')
			->lists('taggable_tags.normalized');
	}

	/**
	 * @param array $array
	 * @return string
	 */
	public static function joinArray(array $array) {
		$delimiters = config('taggable.delimiters', ',');
		$glue = substr($delimiters, 0, 1);

		return implode($glue, $array);
	}
}
