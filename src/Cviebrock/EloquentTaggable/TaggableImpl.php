<?php namespace Cviebrock\EloquentTaggable;

use Cviebrock\EloquentTaggable\Tag;

trait TaggableImpl {

	public function tags()
	{
		return $this->morphToMany('Cviebrock\EloquentTaggable\Tag', 'taggable', 'taggable_taggables')
			->withTimestamps();
	}


	public function tag($tags)
	{
		$tags = $this->buildTagArray($tags);
		foreach($tags as $tag)
		{
			$this->addOneTag($tag);
		}
		return $this;
	}

	public function untag($tags)
	{
		$tags = $this->buildTagArray($tags);
		foreach($tags as $tag)
		{
			$this->removeOneTag($tag);
		}
		return $this;
	}


	public function retag($tags)
	{
		return $this->detag()->tag($tags);
	}


	public function detag()
	{
		$this->removeAllTags();
		return $this;
	}


	protected function buildTagArray($tags)
	{
		if (is_array($tags)) return $tags;

		if (is_string($tags))
		{
			$delimiters = \Config::get('eloquent-taggable::delimiters', ',');
			return preg_split('#['.preg_quote($delimiters,'#').']#', $tags, null, PREG_SPLIT_NO_EMPTY);
		}

		return (array) $tags;
	}


	protected function addOneTag($string)
	{
		$tag = Tag::findOrCreate($string);
		if (!$this->tags->contains($tag->id))
		{
			$this->tags()->attach($tag);
		}
	}


	protected function removeOneTag($string)
	{
		if ($tag = Tag::findByName($string))
		{
			$this->tags()->detach($tag);
		}
	}

	protected function removeAllTags()
	{
		$this->tags()->sync(array());
	}


	public function getTagListAttribute()
	{
		return $this->makeTagList('name');
	}

	public function getTagListNormalizedAttribute()
	{
		return $this->makeTagList('normalized');
	}

	public function getTagArrayAttribute()
	{
		return $this->makeTagArray('name');
	}

	public function getTagArrayNormalizedAttribute()
	{
		return $this->makeTagArray('normalized');
	}

	protected function makeTagList($field)
	{
		$delimiters = \Config::get('eloquent-taggable::delimiters', ',');
		$glue = substr($delimiters, 0, 1);
		$tags = $this->makeTagArray($field);
		return implode($glue, $tags);
	}

	protected function makeTagArray($field)
	{
		return $this->tags->lists($field,'id');
	}



	public function scopeWithAllTags($query, $tags)
	{
		$tags = $this->buildTagArray($tags);
		$normalized = array_map(array('\Cviebrock\EloquentTaggable\Tag','normalizeName'), $tags);

		return $query->whereHas('tags', function($q) use ($normalized)
		{
			$q->whereIn('normalized', $normalized);
		}, '=', count($normalized));
	}

	public function scopeWithAnyTags($query, $tags=array()) {
		$tags = $this->buildTagArray($tags);

		if (empty($tags))
		{
			return $query->has('tags');
		}

		$normalized = array_map(array('\Cviebrock\EloquentTaggable\Tag','normalizeName'), $tags);
		return $query->whereHas('tags', function($q) use ($normalized)
		{
			$q->whereIn('normalized', $normalized);
		});
	}

}