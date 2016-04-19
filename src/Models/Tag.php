<?php namespace Cviebrock\EloquentTaggable\Models;

use Cviebrock\EloquentTaggable\Services\TagService;
use Cviebrock\EloquentTaggable\Util;
use Illuminate\Database\Eloquent\Model as Eloquent;


/**
 * Class Tag.
 */
class Tag extends Eloquent
{

    /**
     * @var string
     */
    protected $table = 'taggable_tags';

    /**
     * @var string
     */
    protected $primaryKey = 'tag_id';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'normalized',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function taggable()
    {
        return $this->morphTo();
    }

    /**
     * Set the name attribute on the model.
     *
     * @param $value
     */
    public function setNameAttribute($value)
    {
        $value = trim($value);
        $this->attributes['name'] = $value;
        $this->attributes['normalized'] = app(TagService::class)->normalize($value);
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->getAttribute('name');
    }
}
