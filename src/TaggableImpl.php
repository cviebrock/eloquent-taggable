<?php namespace Cviebrock\EloquentTaggable;


trait TaggableImpl {

	public function tags() {
		return $this->morphToMany('Cviebrock\EloquentTaggable\Tag', 'taggable', 'taggable_taggables')
			->withTimestamps();
	}

	public function tag($tags) {
		$tags = Util::buildTagArray($tags);
		foreach ($tags as $tag) {
			$this->addOneTag($tag);
		}

		return $this;
	}

	public function untag($tags) {
		$tags = Util::buildTagArray($tags);
		foreach ($tags as $tag) {
			$this->removeOneTag($tag);
		}

		return $this;
	}

	public function retag($tags) {
		return $this->detag()->tag($tags);
	}

	public function detag() {
		$this->removeAllTags();

		return $this;
	}

	protected function addOneTag($string) {
		$tag = Tag::findOrCreate($string);
		if (!$this->tags->contains($tag->getKey())) {
			$this->tags()->attach($tag);
		}
	}

	protected function removeOneTag($string) {
		if ($tag = Tag::findByName($string)) {
			$this->tags()->detach($tag);
		}
	}

	protected function removeAllTags() {
		$this->tags()->sync(array());
	}

	public function getTagListAttribute() {
		return Util::makeTagList($this, 'name');
	}

	public function getTagListNormalizedAttribute() {
		return Util::makeTagList($this, 'normalized');
	}

	public function getTagArrayAttribute() {
		return Util::makeTagArray($this, 'name');
	}

	public function getTagArrayNormalizedAttribute() {
		return Util::makeTagArray($this, 'normalized');
	}

	public function scopeWithAllTags($query, $tags) {
		$tags = Util::buildTagArray($tags);
		$normalized = array_map(array('\Cviebrock\EloquentTaggable\Util', 'normalizeName'), $tags);

		return $query->whereHas('tags', function ($q) use ($normalized) {
			$q->whereIn('normalized', $normalized);
		}, '=', count($normalized));
	}

	public function scopeWithAnyTags($query, $tags = array()) {
		$tags = Util::buildTagArray($tags);

		if (empty($tags)) {
			return $query->has('tags');
		}

		$normalized = array_map(array('\Cviebrock\EloquentTaggable\Util', 'normalizeName'), $tags);

		return $query->whereHas('tags', function ($q) use ($normalized) {
			$q->whereIn('normalized', $normalized);
		});
	}

	public static function tagArray() {
		return Util::getAllTags( get_called_class() );
	}

	public static function tagList() {
		return Util::joinArray( Util::getAllTags( get_called_class() ));
	}

}
