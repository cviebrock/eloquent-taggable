<?php namespace Cviebrock\EloquentTaggable\Models;

use Cviebrock\EloquentTaggable\Services\TagService;
use Illuminate\Database\Eloquent\Model as Eloquent;


/**
 * Class Tag
 */
class Tag extends Eloquent
{

    /**
     * @inheritdoc
     */
    protected $table = 'taggable_tags';

    /**
     * @inheritdoc
     */
    protected $primaryKey = 'tag_id';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'name',
        'normalized',
    ];

    /**
     * @inheritdoc
     */
    public function __construct(array $attributes = [])
    {
        if ($connection = config('taggable.connection')) {
            $this->setConnection($connection);
        }

        parent::__construct($attributes);
    }


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
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getAttribute('name');
    }
}
