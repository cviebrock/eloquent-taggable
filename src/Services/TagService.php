<?php

namespace Cviebrock\EloquentTaggable\Services;

use Cviebrock\EloquentTaggable\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection as BaseCollection;


/**
 * Class TagService
 */
class TagService
{

    /**
     * @var Tag
     */
    protected $tagModel;

    public function __construct()
    {
        $this->tagModel = config('taggable.model', Tag::class);
    }

    /**
     * Find an existing tag by name.
     *
     * @param string $tagName
     *
     * @return Tag|null
     */
    public function find(string $tagName)
    {
        return $this->tagModel::byName($tagName)->first();
    }

    /**
     * Find an existing tag (or create a new one) by name.
     *
     * @param string $tagName
     *
     * @return Tag
     */
    public function findOrCreate(string $tagName): Tag
    {
        $tag = $this->find($tagName);

        if (!$tag) {
            $tag = $this->tagModel::create(['name' => $tagName]);
        }

        return $tag;
    }

    /**
     * Convert a delimited string into an array of tag strings.
     *
     * @param string|array|\Illuminate\Support\Collection $tags
     *
     * @return array
     * @throws \ErrorException
     */
    public function buildTagArray($tags): array
    {
        if (is_array($tags)) {
            $array = $tags;
        } elseif ($tags instanceof BaseCollection) {
            $array = $this->buildTagArray($tags->all());
        } elseif (is_string($tags)) {
            $array = preg_split(
                '#[' . preg_quote(config('taggable.delimiters'), '#') . ']#',
                $tags,
                null,
                PREG_SPLIT_NO_EMPTY
            );
        } else {

            throw new \ErrorException(
                __CLASS__ . '::' . __METHOD__ . ' expects parameter 1 to be string, array or Collection; ' .
                gettype($tags) . ' given'
            );
        }

        return array_filter(
            array_map('trim', $array)
        );
    }

    /**
     * Convert a delimited string into an array of normalized tag strings.
     *
     * @param string|array|\Illuminate\Support\Collection $tags
     *
     * @return array
     * @throws \ErrorException
     */
    public function buildTagArrayNormalized($tags): array
    {
        $tags = $this->buildTagArray($tags);

        return array_map([$this, 'normalize'], $tags);
    }

    /**
     * Return an array of tag models for the given normalized tags
     *
     * @param array $normalized
     *
     * @return array
     */
    public function getTagModelKeys(array $normalized = []): array
    {
        if (count($normalized) === 0) {
            return [];
        }

        return $this->tagModel::whereIn('normalized', $normalized)
            ->pluck('tag_id')
            ->toArray();
    }

    /**
     * Build a delimited string from a model's tags.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $field
     *
     * @return string
     */
    public function makeTagList(Model $model, string $field = 'name'): string
    {
        $tags = $this->makeTagArray($model, $field);

        return $this->joinList($tags);
    }

    /**
     * Join a list of strings together using glue.
     *
     * @param array $array
     *
     * @return string
     */
    public function joinList(array $array): string
    {
        return implode(config('taggable.glue'), $array);
    }

    /**
     * Build a simple array of a model's tags.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $field
     *
     * @return array
     */
    public function makeTagArray(Model $model, string $field = 'name'): array
    {
        /** @var Collection $tags */
        $tags = $model->tags;

        return $tags->pluck($field)->all();
    }

    /**
     * Normalize a string.
     *
     * @param string $string
     *
     * @return string
     */
    public function normalize(string $string): string
    {
        return call_user_func(config('taggable.normalizer'), $string);
    }

    /**
     * Get all Tags for the given class, or all classes.
     *
     * @param \Illuminate\Database\Eloquent\Model|string|null $class
     *
     * @return Collection
     */
    public function getAllTags($class = null): Collection
    {
        if ($class === null) {
            return $this->tagModel::all();
        }

        if ($class instanceof Model) {
            $class = get_class($class);
        }

        $sql = 'SELECT DISTINCT t.* FROM taggable_taggables tt LEFT JOIN taggable_tags t ON tt.tag_id=t.tag_id' .
            ' WHERE tt.taggable_type = ?';

        return $this->tagModel::fromQuery($sql, [$class]);
    }

    /**
     * Get all tag names for the given class, or all classes.
     *
     * @param \Illuminate\Database\Eloquent\Model|string|null $class
     *
     * @return array
     */
    public function getAllTagsArray($class = null): array
    {
        $tags = $this->getAllTags($class);

        return $tags->pluck('name')->toArray();
    }

    /**
     * Get all normalized tag names for the given class, or all classes.
     *
     * @param \Illuminate\Database\Eloquent\Model|string|null $class
     *
     * @return array
     */
    public function getAllTagsArrayNormalized($class = null): array
    {
        $tags = $this->getAllTags($class);

        return $tags->pluck('normalized')->toArray();
    }

    /**
     * Get all Tags that are unused by any model.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUnusedTags(): Collection
    {
        $sql = 'SELECT t.* FROM taggable_tags t LEFT JOIN taggable_taggables tt ON tt.tag_id=t.tag_id ' .
            'WHERE tt.taggable_id IS NULL';

        return $this->tagModel::fromQuery($sql);
    }

    /**
     * Get the most popular tags, optionally limited and/or filtered by class.
     *
     * @param int $limit
     * @param \Illuminate\Database\Eloquent\Model|string|null $class
     * @param int $minCount
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularTags(int $limit = null, $class = null, int $minCount = 1): Collection
    {
        $sql = 'SELECT t.*, COUNT(t.tag_id) AS taggable_count FROM taggable_tags t LEFT JOIN taggable_taggables tt ON tt.tag_id=t.tag_id';
        $bindings = [];

        if ($class) {
            $sql .= ' WHERE tt.taggable_type = ?';
            $bindings[] = ($class instanceof Model) ? get_class($class) : $class;
        }

        // group by everything to handle strict and non-strict mode in MySQL
        $sql .= ' GROUP BY t.tag_id, t.name, t.normalized, t.created_at, t.updated_at';

        if ($minCount > 1) {
            $sql .= ' HAVING taggable_count >= ?';
            $bindings[] = $minCount;
        }

        $sql .= ' ORDER BY taggable_count DESC';

        if ($limit) {
            $sql .= ' LIMIT ?';
            $bindings[] = $limit;
        }

        return $this->tagModel::fromQuery($sql, $bindings);
    }

    /**
     * Rename tags, across all or only one model.
     *
     * @param string $oldName
     * @param string $newName
     * @param \Illuminate\Database\Eloquent\Model|string|null $class
     *
     * @return int
     */
    public function renameTags(string $oldName, string $newName, $class = null): int
    {
        // If no class is specified, we can do the rename with a simple SQL update
        if ($class === null) {
            return $this->tagModel::where('normalized', $this->normalize($oldName))
                ->update([
                    'name'       => $newName,
                    'normalized' => $this->normalize($newName),
                ]);
        }

        if (!($class instanceof Model)) {
            $class = new $class;
        }

        // First find the old tag
        $oldTag = $this->find($oldName);

        // If the old tag doesn't exist, we can short-circuit the process
        if (!$oldTag) {
            return 0;
        }

        // Find or create the new tag
        $newTag = $this->findOrCreate($newName);

        /** @var MorphToMany $morph */
        $morph = $class->tags();
        $pivot = $morph->newPivot();

        $relatedKeyName = $pivot->getRelatedKey();
        $relatedMorphType = $morph->getMorphType();

        return $pivot
            ->where($relatedKeyName, $oldTag->getKey())
            ->where($relatedMorphType, get_class($class))
            ->update([
                $relatedKeyName => $newTag->getKey(),
            ]);
    }
}
