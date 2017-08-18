<?php namespace Cviebrock\EloquentTaggable\Services;

use Cviebrock\EloquentTaggable\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;


/**
 * Class TagService
 */
class TagService
{

    /**
     * Find an existing tag by name.
     *
     * @param string $tagName
     *
     * @return \Cviebrock\EloquentTaggable\Models\Tag|null
     */
    public function find($tagName)
    {
        return Tag::byName($tagName)->first();
    }

    /**
     * Find an existing tag (or create a new one) by name.
     *
     * @param string $tagName
     *
     * @return \Cviebrock\EloquentTaggable\Models\Tag
     */
    public function findOrCreate($tagName)
    {
        $tag = $this->find($tagName);

        if (!$tag) {
            $tag = Tag::create(['name' => $tagName]);
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
    public function buildTagArray($tags)
    {
        if (is_array($tags)) {
            return $tags;
        }

        if ($tags instanceof Collection) {
            return $this->buildTagArray($tags->all());
        }

        if (is_string($tags)) {
            return preg_split(
                '#[' . preg_quote(config('taggable.delimiters'), '#') . ']#',
                $tags,
                null,
                PREG_SPLIT_NO_EMPTY
            );
        }

        throw new \ErrorException(
            __CLASS__ . '::' . __METHOD__ . ' expects parameter 1 to be string, array or Collection; ' .
            gettype($tags) . ' given'
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
    public function buildTagArrayNormalized($tags)
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
    public function getTagModelKeys(array $normalized = [])
    {
        if (count($normalized) === 0) {
            return [];
        }

        return Tag::whereIn('normalized', $normalized)
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
    public function makeTagList(Model $model, $field = 'name')
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
    public function joinList(array $array)
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
    public function makeTagArray(Model $model, $field = 'name')
    {
        /** @var \Illuminate\Database\Eloquent\Collection $tags */
        $tags = $model->tags;

        return $tags->pluck($field)->all();
    }

    /**
     * Normalize a string.
     *
     * @param string $string
     *
     * @return mixed
     */
    public function normalize($string)
    {
        return call_user_func(config('taggable.normalizer'), $string);
    }

    /**
     * Get all Tags for the given class, or all classes.
     *
     * @param \Illuminate\Database\Eloquent\Model|string|null $class
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTags($class = null)
    {
        if ($class === null) {
            return Tag::all();
        }

        if ($class instanceof Model) {
            $class = get_class($class);
        }

        $sql = 'SELECT DISTINCT t.* FROM taggable_taggables tt LEFT JOIN taggable_tags t ON tt.tag_id=t.tag_id' .
            ' WHERE tt.taggable_type = ?';

        return Tag::fromQuery($sql, [$class]);
    }

    /**
     * Get all tag names for the given class, or all classes.
     *
     * @param \Illuminate\Database\Eloquent\Model|string|null $class
     *
     * @return array
     */
    public function getAllTagsArray($class = null)
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
    public function getAllTagsArrayNormalized($class = null)
    {
        $tags = $this->getAllTags($class);

        return $tags->pluck('normalized')->toArray();
    }

    /**
     * Get all Tags that are unused by any model.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUnusedTags()
    {
        $sql = 'SELECT t.* FROM taggable_tags t LEFT JOIN taggable_taggables tt ON tt.tag_id=t.tag_id ' .
            'WHERE tt.taggable_id IS NULL';

        return Tag::fromQuery($sql);
    }

    /**
     * Get the most popular models, optionally limited and/or filtered by class.
     *
     * @param int|null $limit
     * @param \Illuminate\Database\Eloquent\Model|string|null $class
     *
     * @return mixed
     */
    public function getPopularTags($limit = 10, $class = null)
    {
        $sql = 'SELECT t.* FROM taggable_tags t LEFT JOIN taggable_taggables tt ON tt.tag_id=t.tag_id';
        $bindings = [];

        if ($class !== null) {
            $sql .= ' WHERE tt.taggable_type IS ?';
            $bindings[] = ($class instanceof Model) ? get_class($class) : $class;
        }

        $sql .= ' GROUP BY t.tag_id ORDER BY COUNT(t.tag_id) DESC';

        if ($limit) {
            $sql .= ' LIMIT ?';
            $bindings[] = $limit;
        }

        return Tag::fromQuery($sql, $bindings);
    }

    /**
     * @param string $oldName
     * @param string $newName
     * @param \Illuminate\Database\Eloquent\Model|string|null $class
     *
     * @return int
     */
    public function renameTags($oldName, $newName, $class = null)
    {
        // If no class is specified, we can do the rename with a simple SQL update
        if ($class === null) {
            return Tag::where('normalized', $this->normalize($oldName))
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
