<?php namespace Cviebrock\EloquentTaggable;

use Cviebrock\EloquentTaggable\Models\Tag;
use Cviebrock\EloquentTaggable\Services\TagService;


/**
 * Class Taggable.
 */
trait Taggable
{

    /**
     * @var TagService
     */
    private $tagService;

    /**
     * Get the TagService instance.
     *
     * @return TagService
     */
    public static function bootTaggable()
    {
        static::created(function($model) {
            $model->tagService = app(TagService::class);
        });
    }

    /**
     * Get a Collection of all Tags a Model has.
     *
     * @return mixed
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggable_taggables')
            ->withTimestamps();
    }

    /**
     * Attach one or multiple Tags to a Model.
     *
     * @param $tags
     *
     * @return $this
     */
    public function tag($tags)
    {
        $tags = $this->tagService->buildTagArray($tags);

        foreach ($tags as $string) {
            $this->addOneTag($string);
        }

        return $this;
    }

    /**
     * Detach one or multiple Tags from a Model.
     *
     * @param $tags
     *
     * @return $this
     */
    public function untag($tags)
    {
        $tags = $this->tagService->buildTagArray($tags);

        foreach ($tags as $tag) {
            $this->removeOneTag($model, $tag);
        }

        return $this;
    }

    /**
     * Remove all Tags from a Model and assign the given ones.
     *
     * @param $tags
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

        return $this;
    }

    /**
     * Add one tag to the model.
     *
     * @param string $tagName
     */
    private function addOneTag($tagName)
    {
        $tag = $this->tagService->findOrCreate($tagName);

        if (!$this->tags->contains($tag->getKey())) {
            $this->tags()->attach($tag);
        }
    }

    /**
     * Remove one tag from the model
     *
     * @param string $tagName
     */
    protected function removeOneTag($tagName)
    {
        $tag = $this->tagService->find($tagName);

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
        return $this->tagService->makeTagList($this);
    }

    /**
     * Get all normalized tags of a Model as a string in which the tags are delimited
     * by the character defined in config('taggable.delimiters').
     *
     * @return string
     */
    public function getTagListNormalizedAttribute()
    {
        return $this->tagService->makeTagList($this, 'normalized');
    }

    /**
     * Get all tags of a Model as an array.
     *
     * @return mixed
     */
    public function getTagArrayAttribute()
    {
        return $this->tagService->makeTagArray($this);
    }

    /**
     * Get all normalized tags of a Model as an array.
     *
     * @return mixed
     */
    public function getTagArrayNormalizedAttribute()
    {
        return $this->tagService->makeTagArray($this, 'normalized');
    }

    /**
     * Scope for a Model that has all of the given Tags.
     *
     * @param $query
     * @param $tags
     *
     * @return mixed
     */
    public function scopeWithAllTags($query, $tags)
    {
        $tags = $this->tagService->buildTagArray($tags);

        $normalized = array_map([$this->tagService, 'normalize'], $tags);

        return $query->whereHas('tags', function ($q) use ($normalized) {
            $q->whereIn('normalized', $normalized);
        }, '=', count($normalized));
    }

    /**
     * Scope for a Model that has any of the given Tags.
     *
     * @param $query
     * @param array $tags
     *
     * @return mixed
     */
    public function scopeWithAnyTags($query, $tags = [])
    {
        $tags = $this->tagService->buildTagArray($tags);

        if (empty($tags)) {
            return $query->has('tags');
        }

        $normalized = array_map([$this->tagService, 'normalize'], $tags);

        return $query->whereHas('tags', function ($q) use ($normalized) {
            $q->whereIn('normalized', $normalized);
        });
    }

    /**
     * Get all tags used for the called class.
     *
     * @return mixed
     */
    public function allTags()
    {
        return $this->tagService->getAllTags($this);
    }

    /**
     * Get all tags for the called class as a string in which the tags are delimited
     * by the character defined in config('taggable.delimiters').
     *
     * @return string
     */
    public function allTagsList()
    {
        return $this->tagService->joinList($this->allTags());
    }
}
