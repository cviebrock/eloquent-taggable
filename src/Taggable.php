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
     * Get a collection of all Tags a Model has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggable_taggables')
            ->withTimestamps();
    }

    /**
     * Attach one or multiple Tags to a Model.
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
     * Detach one or multiple Tags from a Model.
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
     * Remove all Tags from a Model and assign the given ones.
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
     * Get all tags of a Model as a string in which the tags are delimited
     * by the character defined in config('taggable.delimiters').
     *
     * @return string
     */
    public function getTagListAttribute()
    {
        return app(TagService::class)->makeTagList($this);
    }

    /**
     * Get all normalized tags of a Model as a string in which the tags are delimited
     * by the character defined in config('taggable.delimiters').
     *
     * @return string
     */
    public function getTagListNormalizedAttribute()
    {
        return app(TagService::class)->makeTagList($this, 'normalized');
    }

    /**
     * Get all tags of a Model as an array.
     *
     * @return array
     */
    public function getTagArrayAttribute()
    {
        return app(TagService::class)->makeTagArray($this);
    }

    /**
     * Get all normalized tags of a Model as an array.
     *
     * @return array
     */
    public function getTagArrayNormalizedAttribute()
    {
        return app(TagService::class)->makeTagArray($this, 'normalized');
    }

    /**
     * Scope for a Model that has all of the given tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|string $tags
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAllTags(Builder $query, $tags)
    {
        $normalized = app(TagService::class)->buildTagArrayNormalized($tags);

        return $query->has('tags', '=', count($normalized), 'and', function (Builder $q) use ($normalized) {
            $q->whereIn('normalized', $normalized);
        });
    }

    /**
     * Scope for a Model that has any of the given tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $tags
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAnyTags(Builder $query, $tags = [])
    {
        $normalized = app(TagService::class)->buildTagArrayNormalized($tags);

        if (empty($normalized)) {
            return $query->has('tags');
        }

        return $query->has('tags', '>', 0, 'and', function (Builder $q) use ($normalized) {
            $q->whereIn('normalized', $normalized);
        });
    }

    /**
     * Scope for a Model that doesn't have any tags.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutTags(Builder $query)
    {
        return $query->has('tags', '=', 0);
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
