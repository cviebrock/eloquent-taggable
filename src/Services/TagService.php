<?php namespace Cviebrock\EloquentTaggable\Services;

use Cviebrock\EloquentTaggable\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


/**
 * Class TagService
 *
 * @package Cviebrock\EloquentTaggable\Services
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
        $normalized = $this->normalize($tagName);

        return Tag::where('normalized', $normalized)->first();
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
     * Get all Tags for the given class.
     *
     * @param \Illuminate\Database\Eloquent\Model|string $class
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTags($class)
    {
        if ($class instanceof Model) {
            $class = get_class($class);
        }

        $sql = 'SELECT DISTINCT t.*' .
            ' FROM taggable_taggables tt LEFT JOIN taggable_tags t ON tt.tag_id=t.tag_id' .
            ' WHERE tt.taggable_type = ?';

        return Tag::fromQuery($sql, [$class]);
    }
}
