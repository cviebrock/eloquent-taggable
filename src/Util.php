<?php
namespace Cviebrock\EloquentTaggable;

class Util {

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

	public static function makeTagList(Taggable $model, $field) {
		$tags = static::makeTagArray($model, $field);

		return Util::joinArray($tags);
	}

	public static function makeTagArray(Taggable $model, $field) {
		return $model->tags->lists($field, 'id');
	}

	public static function normalizeName($name) {
		$normalizer = config('taggable.normalizer');

		return call_user_func($normalizer, $name);
	}

	public static function getAllTags($className) {

		return DB::table('taggable_taggables')->distinct()
			->where('taggable_type', '=', $className)
			->join('taggable_tags', 'taggable_taggables.taggable_id', '=', 'taggable_tags.id')
			->orderBy('taggable_tags.normalized')
			->lists('taggable_tags.normalized');
	}

	public static function joinArray(array $array) {
		$delimiters = config('taggable.delimiters', ',');
		$glue = substr($delimiters, 0, 1);

		return implode($glue, $array);
	}
}
