<?php namespace Cviebrock\EloquentTaggable\Contracts;

interface Taggable {

	public function tags();

	public function tag($tags);

	public function untag($tags);

	public function retag($tags);

	public function detag();

	public function scopeWithAllTags($query, $tags);

	public function scopeWithAnyTags($query, $tags = array());

	public static function tagArray();

	public static function tagList();
}
