<?php namespace Cviebrock\EloquentTaggable\Contracts;

/**
 * Interface Taggable
 * @package Cviebrock\EloquentTaggable\Contracts
 */
interface Taggable {

	/**
	 * @return mixed
     */
	public function tags();

	/**
	 * @param $tags
	 * @return mixed
     */
	public function tag($tags);

	/**
	 * @param $tags
	 * @return mixed
     */
	public function untag($tags);

	/**
	 * @param $tags
	 * @return mixed
     */
	public function retag($tags);

	/**
	 * @return mixed
     */
	public function detag();

	/**
	 * @param $query
	 * @param $tags
	 * @return mixed
     */
	public function scopeWithAllTags($query, $tags);

	/**
	 * @param $query
	 * @param array $tags
	 * @return mixed
     */
	public function scopeWithAnyTags($query, $tags = array());

	/**
	 * @return mixed
     */
	public static function tagArray();

	/**
	 * @return mixed
     */
	public static function tagList();
}
