<?php

namespace Cviebrock\EloquentTaggable\Traits;

use Cviebrock\EloquentTaggable\Util;
use Cviebrock\EloquentTaggable\Models\Tag;

/**
 * Class Taggable.
 */
trait Taggable
{
	/**
	 * Get a Collection of all Tags a Model has.
	 *
	 * @return mixed
	 */
	public function tags()
	{
		return $this->morphToMany('Cviebrock\EloquentTaggable\Models\Tag', 'taggable', 'taggable_taggables')
					->withTimestamps();
	}

	/**
	 * Attach one or multiple Tags to a Model.
	 *
	 * @param $tags
	 *
	 * @return $this
	 */
	public function tag($tags)
	{
		$tags = Util::buildTagArray($tags);

		foreach ($tags as $tag) {
			$this->addOneTag($tag);
		}

		return $this;
	}

	/**
	 * Detach one or multiple Tags from a Model.
	 *
	 * @param $tags
	 *
	 * @return $this
	 */
	public function untag($tags)
	{
		$tags = Util::buildTagArray($tags);

		foreach ($tags as $tag) {
			$this->removeOneTag($tag);
		}

		return $this;
	}

	/**
	 * Remove all Tags from a Model and assign the given ones.
	 *
	 * @param $tags
	 *
	 * @return $this
	 */
	public function retag($tags)
	{
		return $this->detag()->tag($tags);
	}

	/**
	 * Remove all Tags from a Model. Alias for removeAllTags.
	 *
	 * @return $this
	 */
	public function detag()
	{
		$this->removeAllTags();

		return $this;
	}

	/**
	 * Attach a single Tag to a Model. Creates the Tag if it doesn't exist.
	 *
	 * @param $string
	 */
	protected function addOneTag($string)
	{
		$tag = Tag::findOrCreate($string);

		if (!$this->tags->contains($tag->getKey())) {
			$this->tags()->attach($tag);
		}
	}

	/**
	 * Detach a single Tag to a Model.
	 *
	 * @param $string
	 */
	protected function removeOneTag($string)
	{
		if ($tag = Tag::findByName($string)) {
			$this->tags()->detach($tag);
		}
	}

	/**
	 * Remove all Tags from a Model.
	 */
	protected function removeAllTags()
	{
		$this->tags()->sync([]);
	}

	/**
	 * Get all tags of a Model as a string in which the tags are delimited
	 * by the character defined in config('taggable.delimiters').
	 *
	 * @return string
	 */
	public function getTagListAttribute()
	{
		return Util::makeTagList($this, 'name');
	}

	/**
	 * Get all normalized tags of a Model as a string in which the tags are delimited
	 * by the character defined in config('taggable.delimiters').
	 *
	 * @return string
	 */
	public function getTagListNormalizedAttribute()
	{
		return Util::makeTagList($this, 'normalized');
	}

	/**
	 * Get all tags of a Model as an array.
	 *
	 * @return mixed
	 */
	public function getTagArrayAttribute()
	{
		return Util::makeTagArray($this, 'name');
	}

	/**
	 * Get all normalized tags of a Model as an array.
	 *
	 * @return mixed
	 */
	public function getTagArrayNormalizedAttribute()
	{
		return Util::makeTagArray($this, 'normalized');
	}

	/**
	 * Scope for a Model that has all of the given Tags.
	 *
	 * @param $query
	 * @param $tags
	 *
	 * @return mixed
	 */
	public function scopeWithAllTags($query, $tags)
	{
		$tags = Util::buildTagArray($tags);
		$normalized = array_map(['\Cviebrock\EloquentTaggable\Util', 'normalizeName'], $tags);

		return $query->whereHas('tags', function ($q) use ($normalized) {
			$q->whereIn('normalized', $normalized);
		}, '=', count($normalized));
	}

	/**
	 * Scope for a Model that has any of the given Tags.
	 *
	 * @param $query
	 * @param array $tags
	 *
	 * @return mixed
	 */
	public function scopeWithAnyTags($query, $tags = [])
	{
		$tags = Util::buildTagArray($tags);

		if (empty($tags)) {
			return $query->has('tags');
		}

		$normalized = array_map(['\Cviebrock\EloquentTaggable\Util', 'normalizeName'], $tags);

		return $query->whereHas('tags', function ($q) use ($normalized) {
			$q->whereIn('normalized', $normalized);
		});
	}

	/**
	 * Get all tags for the called class.
	 *
	 * @return mixed
	 */
	public static function tagArray()
	{
		return Util::getAllTags(get_called_class());
	}

	/**
	 * Get all tags for the called class as a string in which the tags are delimited
	 * by the character defined in config('taggable.delimiters').
	 *
	 * @return string
	 */
	public static function tagList()
	{
		return Util::joinArray(Util::getAllTags(get_called_class()));
	}
}
