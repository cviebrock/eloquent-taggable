<?php namespace Cviebrock\EloquentTaggable\Models;

use Cviebrock\EloquentTaggable\Services\TagService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;


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
     * Scope to find tags by name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByName(Builder $query, string $value): Builder
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
            $relation = $this->taggedModels($relatedClass);

            return tap($relation->getResults(), function($results) use ($key) {
                $this->setRelation($key, $results);
            });
        }
    }

    /**
     * Get the inverse of the polymorphic relation, via an attribute
     * defining the type of models to return.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    protected function taggedModels(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'taggable', env('DB_TABLE_PREFIX', '').'taggable_taggables', 'tag_id');
    }

    /**
     * Find the tag with the given name.
     *
     * @param string $value
     *
     * @return static|null
     */
    public static function findByName(string $value)
    {
        return app(TagService::class)->find($value);
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return (string) $this->getAttribute('name');
    }

}
