<?php namespace Cviebrock\EloquentTaggable;

use Cviebrock\EloquentTaggable\Events\ModelTagged;
use Cviebrock\EloquentTaggable\Events\ModelUntagged;
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
     * Property to control sequence on alias
     *
     * @var int
     */
    private $taggableAliasSequence = 0;

    /**
     * Boot the trait.
     *
     * Listen for the deleting event of a model, then remove the relation between it and tags
     */
    protected static function bootTaggable(): void
    {
        static::deleting(function ($model) {
            if (!method_exists($model, 'runSoftDelete') || $model->isForceDeleting()) {
                $model->detag();
            }
        });
    }

    /**
     * Get a collection of all tags the model has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags(): MorphToMany
    {
        $model = config('taggable.model');
        $table = config('taggable.tables.taggable_taggables', 'taggable_taggables');
        return $this->morphToMany($model, 'taggable', $table, 'taggable_id', 'tag_id')
            ->withTimestamps();
    }

    /**
     * Attach one or multiple tags to the model.
     *
     * @param string|array $tags
     *
     * @return self
     */
    public function tag($tags): self
    {
        $tags = app(TagService::class)->buildTagArray($tags);

        foreach ($tags as $tagName) {
            $this->addOneTag($tagName);
            $this->load('tags');
        }

        event(new ModelTagged($this, $tags));

        return $this;
    }

    /**
     * Attach one or more existing tags to a model,
     * identified by the tag's IDs.
     *
     * @param int|int[] $ids
     *
     * @return $this
     */
    public function tagById($ids): self
    {
        $tags = app(TagService::class)->findByIds($ids);
        $names = $tags->pluck('name')->all();

        return $this->tag($names);
    }

    /**
     * Detach one or multiple tags from the model.
     *
     * @param string|array $tags
     *
     * @return self
     */
    public function untag($tags): self
    {
        $tags = app(TagService::class)->buildTagArray($tags);

        foreach ($tags as $tagName) {
            $this->removeOneTag($tagName);
        }

        event(new ModelUntagged($this, $tags));

        return $this->load('tags');
    }

    /**
     * Detach one or more existing tags to a model,
     * identified by the tag's IDs.
     *
     * @param int|int[] $ids
     *
     * @return $this
     */
    public function untagById($ids): self
    {
        $tags = app(TagService::class)->findByIds($ids);
        $names = $tags->pluck('name')->all();

        return $this->untag($names);
    }

    /**
     * Remove all tags from the model and assign the given ones.
     *
     * @param string|array $tags
     *
     * @return self
     */
    public function retag($tags): self
    {
        return $this->detag()->tag($tags);
    }

    /**
     * Remove all tags from the model and assign the given ones by ID.
     *
     * @param int|int[] $ids
     *
     * @return self
     */
    public function retagById($ids): self
    {
        return $this->detag()->tagById($ids);
    }

    /**
     * Remove all tags from the model.
     *
     * @return self
     */
    public function detag(): self
    {
        $this->tags()->sync([]);

        return $this->load('tags');
    }

    /**
     * Add one tag to the model.
     *
     * @param string $tagName
     */
    protected function addOneTag(string $tagName): void
    {
        /** @var Tag $tag */
        $tag = app(TagService::class)->findOrCreate($tagName);
        $tagKey = $tag->getKey();

        if (!$this->getAttribute('tags')->contains($tagKey)) {
            $this->tags()->attach($tagKey);
        }
    }

    /**
     * Remove one tag from the model
     *
     * @param string $tagName
     */
    protected function removeOneTag(string $tagName): void
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
     * Determine if a given tag is attached to the model.
     *
     * @param Tag|string $tag
     *
     * @return bool
     */
    public function hasTag($tag): bool
    {
        if ($tag instanceof Tag) {
            $normalized = $tag->getAttribute('normalized');
        } else {
            $normalized = app(TagService::class)->normalize($tag);
        }

        return in_array($normalized, $this->getTagArrayNormalizedAttribute(), true);
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

        $alias = $this->taggableCreateNewAlias(__FUNCTION__);
        $morphTagKeyName = $this->getQualifiedRelatedPivotKeyNameWithAlias($alias);

        return $this->prepareTableJoin($query, 'inner', $alias)
            ->whereIn($morphTagKeyName, $tagKeys)
            ->havingRaw("COUNT(DISTINCT {$morphTagKeyName}) = ?", [count($tagKeys)]);
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

        $alias = $this->taggableCreateNewAlias(__FUNCTION__);
        $morphTagKeyName = $this->getQualifiedRelatedPivotKeyNameWithAlias($alias);

        return $this->prepareTableJoin($query, 'inner', $alias)
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
        $alias = $this->taggableCreateNewAlias(__FUNCTION__);
        return $this->prepareTableJoin($query, 'inner', $alias);
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

        $alias = $this->taggableCreateNewAlias(__FUNCTION__);
        $morphTagKeyName = $this->getQualifiedRelatedPivotKeyNameWithAlias($alias);

        $query = $this->prepareTableJoin($query, 'left', $alias)
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

        $alias = $this->taggableCreateNewAlias(__FUNCTION__);
        $morphTagKeyName = $this->getQualifiedRelatedPivotKeyNameWithAlias($alias);

        $query = $this->prepareTableJoin($query, 'left', $alias)
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
        $alias = $this->taggableCreateNewAlias(__FUNCTION__);
        $morphForeignKeyName = $this->getQualifiedForeignPivotKeyNameWithAlias($alias);

        return $this->prepareTableJoin($query, 'left', $alias)
            ->havingRaw("COUNT(DISTINCT {$morphForeignKeyName}) = 0");
    }

    /**
     * @param Builder $query
     * @param string $joinType
     *
     * @return Builder
     */
    private function prepareTableJoin(Builder $query, string $joinType, string $alias): Builder
    {
        $morphTable = $this->tags()->getTable();
        $morphTableAlias = $morphTable.'_'.$alias;

        $modelKeyName = $this->getQualifiedKeyName();
        $morphForeignKeyName = $this->getQualifiedForeignPivotKeyNameWithAlias($alias);

        $morphTypeName = $morphTableAlias.'.'. $this->tags()->getMorphType();
        $morphClass = $this->tags()->getMorphClass();

        $closure = function(JoinClause $join) use ($modelKeyName, $morphForeignKeyName, $morphTypeName, $morphClass) {
            $join->on($modelKeyName, $morphForeignKeyName)
                ->where($morphTypeName, $morphClass);
        };

        return $query
            ->select($this->getTable() . '.*')
            ->join($morphTable.' as '.$morphTableAlias, $closure, null, null, $joinType)
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
    public static function popularTags(int $limit = null, int $minCount = 1): array
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
    public static function popularTagsNormalized(int $limit = null, int $minCount = 1): array
    {
        /** @var Collection $tags */
        $tags = app(TagService::class)->getPopularTags($limit, static::class, $minCount);

        return $tags->pluck('taggable_count', 'normalized')->all();
    }

    /**
     * Returns the Related Pivot Key Name with the table alias.
     *
     * @param string $alias
     *
     * @return string
     */
    private function getQualifiedRelatedPivotKeyNameWithAlias(string $alias): string
    {
        $morph = $this->tags();

        return $morph->getTable() . '_' . $alias .
            '.' . $morph->getRelatedPivotKeyName();
    }

    /**
     * Returns the Foreign Pivot Key Name with the table alias.
     *
     * @param string $alias
     *
     * @return string
     */
    private function getQualifiedForeignPivotKeyNameWithAlias(string $alias): string
    {
        $morph = $this->tags();

        return $morph->getTable() . '_' . $alias .
            '.' . $morph->getForeignPivotKeyName();
    }

    /**
     * Create a new alias to use on scopes to be able to combine many scopes
     *
     * @param string $scope
     * 
     * @return string
     */
    private function taggableCreateNewAlias(string $scope): string
    {
        $this->taggableAliasSequence++;
        $alias = strtolower($scope) . '_' . $this->taggableAliasSequence;

        return $alias;
    }
}
