<?php namespace Cviebrock\EloquentTaggable;

use Cviebrock\EloquentTaggable\Models\Tag;
use Cviebrock\EloquentTaggable\Services\TagService;
use Illuminate\Database\Eloquent\Builder;


/**
 * Class Taggable
 *
 * @package Cviebrock\EloquentTaggable
 */
trait Taggable
{

    /**
     * Get a collection of all tags the model has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggable_taggables', 'taggable_id', 'tag_id')
            ->withTimestamps();
    }

    /**
     * Attach one or multiple tags to the model.
     *
     * @param string|array $tags
     *
     * @return $this
     */
    public function tag($tags)
    {
        $tags = app(TagService::class)->buildTagArray($tags);

        foreach ($tags as $tagName) {
            $this->addOneTag($tagName);
        }

        return $this->load('tags');
    }

    /**
     * Detach one or multiple tags from the model.
     *
     * @param string|array $tags
     *
     * @return $this
     */
    public function untag($tags)
    {
        $tags = app(TagService::class)->buildTagArray($tags);

        foreach ($tags as $tagName) {
            $this->removeOneTag($tagName);
        }

        return $this->load('tags');
    }

    /**
     * Remove all tags from the model and assign the given ones.
     *
     * @param string|array $tags
     *
     * @return $this
     */
    public function retag($tags)
    {
        return $this->detag()->tag($tags);
    }

    /**
     * Remove all tags from the model.
     *
     * @return $this
     */
    public function detag()
    {
        $this->tags()->sync([]);

        return $this->load('tags');
    }

    /**
     * Add one tag to the model.
     *
     * @param string $tagName
     */
    protected function addOneTag($tagName)
    {
        $tag = app(TagService::class)->findOrCreate($tagName);

        if (!$this->tags->contains($tag->getKey())) {
            $this->tags()->attach($tag->getKey());
        }
    }

    /**
     * Remove one tag from the model
     *
     * @param string $tagName
     */
    protected function removeOneTag($tagName)
    {
        $tag = app(TagService::class)->find($tagName);

        if ($tag) {
            $this->tags()->detach($tag);
        }
    }

    /**
     * Get all the tags of the model as a delimited string.
     *
     * @return string
     */
    public function getTagListAttribute()
    {
        return app(TagService::class)->makeTagList($this);
    }

    /**
     * Get all normalized tags of a model as a delimited string.
     *
     * @return string
     */
    public function getTagListNormalizedAttribute()
    {
        return app(TagService::class)->makeTagList($this, 'normalized');
    }

    /**
     * Get all tags of a model as an array.
     *
     * @return array
     */
    public function getTagArrayAttribute()
    {
        return app(TagService::class)->makeTagArray($this);
    }

    /**
     * Get all normalized tags of a model as an array.
     *
     * @return array
     */
    public function getTagArrayNormalizedAttribute()
    {
        return app(TagService::class)->makeTagArray($this, 'normalized');
    }

    /**
     * Query scope for models that have all of the given tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|string $tags
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithAllTags(Builder $query, $tags)
    {
        /** @var TagService $service */
        $service = app(TagService::class);
        $normalized = $service->buildTagArrayNormalized($tags);

        // If there are no tags specified, then there
        // can't be any results so short-circuit
        if (count($normalized) === 0) {
            return $query->where(\DB::raw(1), 0);
        }

        $tagKeys = $service->getTagModelKeys($normalized);

        // If some of the tags specified don't exist, then there can't
        // be any models with all the tags, so so short-circuit
        if (count($tagKeys) !== count($normalized)) {
            return $query->where(\DB::raw(1), 0);
        }

        return $this->getMorphQuery($query, $tagKeys, count($tagKeys));
    }

    /**
     * Query scope for models that have any of the given tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|string $tags
     *
     * @return mixed
     */
    public function scopeWithAnyTags(Builder $query, $tags)
    {
        /** @var TagService $service */
        $service = app(TagService::class);
        $normalized = $service->buildTagArrayNormalized($tags);

        // If there are no tags specified, then there is
        // no filtering to be done so short-circuit
        if (count($normalized) === 0) {
            return $query;
        }

        $tagKeys = $service->getTagModelKeys($normalized);

        return $this->getMorphQuery($query, $tagKeys);
    }

    /**
     * Query scope for models that have any tag.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasTags(Builder $query)
    {
        return $this->getMorphQuery($query);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $tagKeys
     *
     * @return mixed
     */
    private function getMorphQuery(Builder $query, array $tagKeys = [], $limit = 0)
    {
        $keyColumn = $this->getQualifiedKeyName();
        $morphTable = $this->tags()->getTable();
        $morphKeyName = $this->tags()->getQualifiedRelatedKeyName();
        $morphForeignKeyName = $this->tags()->getQualifiedForeignKeyName();
        $morphTypeColumnName = $morphTable . '.' . $this->tags()->getMorphType();

        $query->select($this->getTable() . '.*')
            ->leftJoin($morphTable, $keyColumn, $morphForeignKeyName)
            ->where($morphTypeColumnName, static::class)
            ->groupBy($keyColumn);

        if (count($tagKeys)) {
            $query->whereIn($morphKeyName, $tagKeys);
        }

        if ($limit > 0) {
            $query->havingRaw("COUNT({$morphKeyName}) >= ?", [$limit]);
        }

        return $query;
    }

    /**
     * Query scope for models that do not have all of the given tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $tags
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutAllTags(Builder $query, $tags)
    {
        $keys = static::withAllTags($tags)->pluck($this->getKeyName());

        return $query->whereNotIn($this->getKeyName(), $keys);
    }

    /**
     * Query scope for models that do not have any of the given tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $tags
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutAnyTags(Builder $query, $tags)
    {
        $keys = static::withAnyTags($tags)->pluck($this->getKeyName());

        return $query->whereNotIn($this->getKeyName(), $keys);
    }

    /**
     * Query scope for models that does not have have any tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHasNoTags(Builder $query)
    {
        $keys = static::hasTags()->pluck($this->getKeyName());

        return $query->whereNotIn($this->getKeyName(), $keys);
    }

    /**
     * Get an array of all tags used for the called class.
     *
     * @return array
     */
    public static function allTags()
    {
        /** @var \Illuminate\Database\Eloquent\Collection $tags */
        $tags = app(TagService::class)->getAllTags(get_called_class());

        return $tags->pluck('name')->sort()->all();
    }

    /**
     * Get all the tags used for the called class as a delimited string.
     *
     * @return string
     */
    public static function allTagsList()
    {
        return app(TagService::class)->joinList(static::allTags());
    }
}
