<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Taggable;
use Illuminate\Database\Eloquent\Model;


/**
 * Class TestModel
 */
class TestModel extends Model
{

    use Taggable;

    /**
     * @inheritdoc
     */
    protected $table = 'test_models';

    /**
     * @inheritdoc
     */
    public $timestamps = false;

    /**
     * @inheritdoc
     */
    protected $fillable = ['title'];
}
