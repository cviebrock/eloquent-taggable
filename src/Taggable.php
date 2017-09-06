<?php namespace Cviebrock\EloquentTaggable;

use Cviebrock\EloquentTaggable\Exceptions\NoTagsSpecifiedException;
use Cviebrock\EloquentTaggable\Models\Tag;
use Cviebrock\EloquentTaggable\Services\TagService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\JoinClause;


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
    public function tags(): MorphToMany
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
    protected function addOneTag(string $tagName)
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
    protected function removeOneTag(string $tagName)
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
    public function getTagListAttribute(): string
    {
        return app(TagService::class)->makeTagList($this);
    }

    /**
     * Get all normalized tags of a model as a delimited string.
     *
     * @return string
     */
    public function getTagListNormalizedAttribute(): string
    {
        return app(TagService::class)->makeTagList($this, 'normalized');
    }

    /**
     * Get all tags of a model as an array.
     *
     * @return array
     */
    public function getTagArrayAttribute(): array
    {
        return app(TagService::class)->makeTagArray($this);
    }

    /**
     * Get all normalized tags of a model as an array.
     *
     * @return array
     */
    public function getTagArrayNormalizedAttribute(): array
    {
        return app(TagService::class)->makeTagArray($this, 'normalized');
    }

    /**
     * Query scope for models that have all of the given tags.
     *
     * @param Builder $query
     * @param array|string $tags
     *
     * @return Builder
     * @throws \Cviebrock\EloquentTaggable\Exceptions\NoTagsSpecifiedException
     * @throws \ErrorException
     */
    public function scopeWithAllTags(Builder $query, $tags): Builder
    {
        /** @var TagService $service */
        $service = app(TagService::class);
        $normalized = $service->buildTagArrayNormalized($tags);

        // If there are no tags specified, then there
        // can't be any results so short-circuit
        if (count($normalized) === 0) {
            if (config('taggable.throwEmptyExceptions')) {
                throw new NoTagsSpecifiedException('Empty tag data passed to withAllTags scope.');
            }

            return $query->where(\DB::raw(1), 0);
        }

        $tagKeys = $service->getTagModelKeys($normalized);

        // If some of the tags specified don't exist, then there can't
        // be any models with all the tags, so so short-circuit
        if (count($tagKeys) !== count($normalized)) {
            return $query->where(\DB::raw(1), 0);
        }

        $morphTagKeyName = $this->tags()->getQualifiedRelatedPivotKeyName();

        return $this->prepareTableJoin($query, 'inner')
            ->whereIn($morphTagKeyName, $tagKeys)
            ->havingRaw("COUNT({$morphTagKeyName}) = ?", [count($tagKeys)]);
    }

    /**
     * Query scope for models that have any of the given tags.
     *
     * @param Builder $query
     * @param array|string $tags
     *
     * @return Builder
     * @throws \Cviebrock\EloquentTaggable\Exceptions\NoTagsSpecifiedException
     * @throws \ErrorException
     */
    public function scopeWithAnyTags(Builder $query, $tags): Builder
    {
        /** @var TagService $service */
        $service = app(TagService::class);
        $normalized = $service->buildTagArrayNormalized($tags);

        // If there are no tags specified, then there is
        // no filtering to be done so short-circuit
        if (count($normalized) === 0) {
            if (config('taggable.throwEmptyExceptions')) {
                throw new NoTagsSpecifiedException('Empty tag data passed to withAnyTags scope.');
            }

            return $query->where(\DB::raw(1), 0);
        }

        $tagKeys = $service->getTagModelKeys($normalized);

        $morphTagKeyName = $this->tags()->getQualifiedRelatedPivotKeyName();

        return $this->prepareTableJoin($query, 'inner')
            ->whereIn($morphTagKeyName, $tagKeys);
    }

    /**
     * Query scope for models that have any tag.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeIsTagged(Builder $query): Builder
    {
        return $this->prepareTableJoin($query, 'inner');
    }

    /**
     * Query scope for models that do not have all of the given tags.
     *
     * @param Builder $query
     * @param string|array $tags
     * @param bool $includeUntagged
     *
     * @return Builder
     * @throws \ErrorException
     */
    public function scopeWithoutAllTags(Builder $query, $tags, bool $includeUntagged = false): Builder
    {
        /** @var TagService $service */
        $service = app(TagService::class);
        $normalized = $service->buildTagArrayNormalized($tags);
        $tagKeys = $service->getTagModelKeys($normalized);
        $tagKeyList = implode(',', $tagKeys);

        $morphTagKeyName = $this->tags()->getQualifiedRelatedPivotKeyName();

        $query = $this->prepareTableJoin($query, 'left')
            ->havingRaw("COUNT(DISTINCT CASE WHEN ({$morphTagKeyName} IN ({$tagKeyList})) THEN {$morphTagKeyName} ELSE NULL END) < ?",
                [count($tagKeys)]);

        if (!$includeUntagged) {
            $query->havingRaw("COUNT(DISTINCT {$morphTagKeyName}) > 0");
        }

        return $query;
    }

    /**
     * Query scope for models that do not have any of the given tags.
     *
     * @param Builder $query
     * @param string|array $tags
     * @param bool $includeUntagged
     *
     * @return Builder
     * @throws \ErrorException
     */
    public function scopeWithoutAnyTags(Builder $query, $tags, bool $includeUntagged = false): Builder
    {
        /** @var TagService $service */
        $service = app(TagService::class);
        $normalized = $service->buildTagArrayNormalized($tags);
        $tagKeys = $service->getTagModelKeys($normalized);
        $tagKeyList = implode(',', $tagKeys);

        $morphTagKeyName = $this->tags()->getQualifiedRelatedPivotKeyName();

        $query = $this->prepareTableJoin($query, 'left')
            ->havingRaw("COUNT(DISTINCT CASE WHEN ({$morphTagKeyName} IN ({$tagKeyList})) THEN {$morphTagKeyName} ELSE NULL END) = 0");

        if (!$includeUntagged) {
            $query->havingRaw("COUNT(DISTINCT {$morphTagKeyName}) > 0");
        }

        return $query;
    }

    /**
     * Query scope for models that does not have have any tags.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeIsNotTagged(Builder $query): Builder
    {
        $morphForeignKeyName = $this->tags()->getQualifiedForeignPivotKeyName();

        return $this->prepareTableJoin($query, 'left')
            ->havingRaw("COUNT(DISTINCT {$morphForeignKeyName}) = 0");
    }

    /**
     * @param Builder $query
     * @param string $joinType
     *
     * @return Builder
     */
    private function prepareTableJoin(Builder $query, string $joinType): Builder
    {
        $modelKeyName = $this->getQualifiedKeyName();
        $morphTable = $this->tags()->getTable();
        $morphForeignKeyName = $this->tags()->getQualifiedForeignPivotKeyName();
        $morphTypeName = $morphTable . '.' . $this->tags()->getMorphType();

        $closure = function(JoinClause $join) use ($modelKeyName, $morphForeignKeyName, $morphTypeName) {
            $join->on($modelKeyName, $morphForeignKeyName)
                ->where($morphTypeName, static::class);
        };

        return $query
            ->select($this->getTable() . '.*')
            ->join($morphTable, $closure, null, null, $joinType)
            ->groupBy($modelKeyName);
    }

    /**
     * Get a collection of all the tag models used for the called class.
     *
     * @return Collection
     */
    public static function allTagModels(): Collection
    {
        return app(TagService::class)->getAllTags(static::class);
    }

    /**
     * Get an array of all tags used for the called class.
     *
     * @return array
     */
    public static function allTags(): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection $tags */
        $tags = static::allTagModels();

        return $tags->pluck('name')->sort()->all();
    }

    /**
     * Get all the tags used for the called class as a delimited string.
     *
     * @return string
     */
    public static function allTagsList(): string
    {
        return app(TagService::class)->joinList(static::allTags());
    }

    /**
     * Rename one the tags for the called class.
     *
     * @param string $oldTag
     * @param string $newTag
     *
     * @return int
     */
    public static function renameTag(string $oldTag, string $newTag): int
    {
        return app(TagService::class)->renameTags($oldTag, $newTag, static::class);
    }

    /**
     * Get the most popular tags for the called class.
     *
     * @param int $limit
     * @param int $minCount
     *
     * @return array
     */
    public static function popularTags(int $limit = null, int $minCount = null): array
    {
        /** @var Collection $tags */
        $tags = app(TagService::class)->getPopularTags($limit, static::class, $minCount);

        return $tags->pluck('taggable_count', 'name')->all();
    }

    /**
     * Get the most popular tags for the called class.
     *
     * @param int $limit
     * @param int $minCount
     *
     * @return array
     */
    public static function popularTagsNormalized(int $limit = null, int $minCount = null): array
    {
        /** @var Collection $tags */
        $tags = app(TagService::class)->getPopularTags($limit, static::class, $minCount);

        return $tags->pluck('taggable_count', 'normalized')->all();
    }
}
