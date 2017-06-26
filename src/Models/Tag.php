<?php namespace Cviebrock\EloquentTaggable\Models;

use Cviebrock\EloquentTaggable\Services\TagService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Tag
 */
class Tag extends Model
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
     * Get the inverse of the polymorphic relation, via an attribute
     * defining the type of models to return.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function taggedModels($class)
    {
        return $this->morphedByMany($class, 'taggable', 'taggable_taggables', 'tag_id');
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
     * Find the tag with the given name.
     *
     * @param string $value
     *
     * @return static|null
     */
    public static function findByName($value)
    {
        return static::byName($value)->first();
    }

    /**
     * Scope to find tags by name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByName(Builder $query, $value)
    {
        $normalized = app(TagService::class)->normalize($value);

        return $query->where('normalized', $normalized);
    }

    /**
     * @inheritdoc
     */
    public function getRelationValue($key)
    {
        // Check for regular relation first
        if ($return = parent::getRelationValue($key)) {
            return $return;
        }

        // Check if the relation is defined via configuration
        $relatedClass = array_get(config('taggable.taggedModels'), $key);

        if ($relatedClass) {
            $relation = $this->morphedByMany($relatedClass, 'taggable', 'taggable_taggables', 'tag_id');

            return tap($relation->getResults(), function($results) use ($key) {
                $this->setRelation($key, $results);
            });
        }
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string) $this->getAttribute('name');
    }

}
