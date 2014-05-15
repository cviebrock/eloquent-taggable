<?php

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentTaggable\Taggable;
use Cviebrock\EloquentTaggable\TaggableImpl;


class Post extends Model implements Taggable {

	use TaggableImpl;

  protected $table = 'posts';

  public $timestamps = false;

	protected $fillable = array('title');

}