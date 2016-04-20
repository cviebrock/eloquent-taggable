<?php namespace Cviebrock\EloquentTaggable\Models;

use Cviebrock\EloquentTaggable\Services\TagService;
use Illuminate\Database\Eloquent\Model as Eloquent;


/**
 * Class Tag
 *
 * @package Cviebrock\EloquentTaggable\Models
 */
class Tag extends Eloquent
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'taggable_tags';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'tag_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'normalized',
    ];

    /**
     * Set the name attribute on the model.
     *
     * @param string $value
     */
    public function setNameAttribute($value)
    {
        $value = trim($value);
        $this->attributes['name'] = $value;
        $this->attributes['normalized'] = app(TagService::class)->normalize($value);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getAttribute('name');
    }
}
