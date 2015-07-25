<?php

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentTaggable\Contracts\Taggable;
use Cviebrock\EloquentTaggable\Traits\Taggable as TaggableImpl;

class Post extends Model implements Taggable
{
	use TaggableImpl;

	protected $table = 'posts';

	public $timestamps = false;

	protected $fillable = ['title'];
}
