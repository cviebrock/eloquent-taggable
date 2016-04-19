<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Taggable;
use Illuminate\Database\Eloquent\Model;


class TestModel extends Model
{

    use Taggable;

    protected $table = 'test_models';

    public $timestamps = false;

    protected $fillable = ['title'];
}
