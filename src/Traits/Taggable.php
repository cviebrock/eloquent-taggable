<?php namespace Cviebrock\EloquentTaggable\Traits;

use Cviebrock\EloquentTaggable\Util;
use Cviebrock\EloquentTaggable\Models\Tag;

/**
 * Class Taggable
 * @package Cviebrock\EloquentTaggable\Traits
 */
trait Taggable {

	/**
	 * @return mixed
	 */
	public function tags() {
		return $this->morphToMany('Cviebrock\EloquentTaggable\Models\Tag', 'taggable', 'taggable_taggables')
			->withTimestamps();
	}

	/**
	 * @param $tags
	 * @return $this
	 */
	public function tag($tags) {
		$tags = Util::buildTagArray($tags);
		foreach ($tags as $tag) {
			$this->addOneTag($tag);
		}

		return $this;
	}

	/**
	 * @param $tags
	 * @return $this
	 */
	public function untag($tags) {
		$tags = Util::buildTagArray($tags);
		foreach ($tags as $tag) {
			$this->removeOneTag($tag);
		}

		return $this;
	}

	/**
	 * @param $tags
	 * @return $this
	 */
	public function retag($tags) {
		return $this->detag()->tag($tags);
	}

	/**
	 * @return $this
	 */
	public function detag() {
		$this->removeAllTags();

		return $this;
	}

	/**
	 * @param $string
	 */
	protected function addOneTag($string) {
		$tag = Tag::findOrCreate($string);
		if (!$this->tags->contains($tag->getKey())) {
			$this->tags()->attach($tag);
		}
	}

	/**
	 * @param $string
	 */
	protected function removeOneTag($string) {
		if ($tag = Tag::findByName($string)) {
			$this->tags()->detach($tag);
		}
	}

	/**
	 *
	 */
	protected function removeAllTags() {
		$this->tags()->sync([]);
	}

	/**
	 * @return string
	 */
	public function getTagListAttribute() {
		return Util::makeTagList($this, 'name');
	}

	/**
	 * @return string
	 */
	public function getTagListNormalizedAttribute() {
		return Util::makeTagList($this, 'normalized');
	}

	/**
	 * @return mixed
	 */
	public function getTagArrayAttribute() {
		return Util::makeTagArray($this, 'name');
	}

	/**
	 * @return mixed
	 */
	public function getTagArrayNormalizedAttribute() {
		return Util::makeTagArray($this, 'normalized');
	}

	/**
	 * @param $query
	 * @param $tags
	 * @return mixed
	 */
	public function scopeWithAllTags($query, $tags) {
		$tags = Util::buildTagArray($tags);
		$normalized = array_map(['\Cviebrock\EloquentTaggable\Util', 'normalizeName'], $tags);

		return $query->whereHas('tags', function ($q) use ($normalized) {
			$q->whereIn('normalized', $normalized);
		}, '=', count($normalized));
	}

	/**
	 * @param $query
	 * @param array $tags
	 * @return mixed
	 */
	public function scopeWithAnyTags($query, $tags = []) {
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
	 * @return mixed
	 */
	public static function tagArray() {
		return Util::getAllTags(get_called_class());
	}

	/**
	 * @return string
	 */
	public static function tagList() {
		return Util::joinArray(Util::getAllTags(get_called_class()));
	}
}
