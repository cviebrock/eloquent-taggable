<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Taggable;
use Illuminate\Database\Eloquent\Model;


class TestDummy extends Model
{

    use Taggable;

    protected $table = 'test_dummies';

    public $timestamps = false;

    protected $fillable = ['title'];
}
