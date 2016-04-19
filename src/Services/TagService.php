<?php

namespace Cviebrock\EloquentTaggable\Services;

use Cviebrock\EloquentTaggable\Models\Tag;
use Cviebrock\EloquentTaggable\Taggable;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Util.
 */
class TagService
{

    /**
     * @var \Illuminate\Database\Connection
     */
    protected $db;

    /**
     * @var array
     */
    protected $config;

    /**
     * TagService constructor.
     *
     * @param \Illuminate\Database\Connection $db
     * @param array $config
     */
    public function __construct(Connection $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Find an existing tag by name.
     *
     * @param string $tagName
     * @return Tag|null
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
     * @return Tag
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
     * Convert a delimited string into an array.
     *
     * @param string|array $tags
     *
     * @return array
     */
    public function buildTagArray($tags)
    {
        if (is_array($tags)) {
            return $tags;
        }

        if (is_string($tags)) {
            return preg_split('#[' . preg_quote($this->config['delimiters'], '#') . ']#', $tags, null,
                PREG_SPLIT_NO_EMPTY);
        }

        return (array)$tags;
    }

    /**
     * Build a delimited string from a model's tags.
     *
     * @param Taggable $model
     * @param string $field
     *
     * @return string
     */
    public function makeTagList(Taggable $model, $field = 'name')
    {
        $tags = $this->makeTagArray($model, $field);

        return $this->joinList($tags);
    }

    /**
     * @param array $array
     * @return mixed
     */
    public function joinList(array $array)
    {
        return implode($this->config['list_glue'], $array);
    }

    /**
     * Build a simple array of a model's tags.
     *
     * @param Model $model
     * @param $field
     *
     * @return array
     */
    public function makeTagArray(Model $model, $field = 'name')
    {
        /** @var Collection $tags */
        $tags = $model->tags;

        return $tags->pluck($field, 'tag_id')->all();
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
        return call_user_func($this->config['normalizer'], $string);
    }

    /**
     * Get all tags for the given class.
     *
     * @param Model|string $class
     *
     * @return array
     */
    public function getAllTags($class)
    {
        if ($class instanceof Model) {
            $class = get_class($class);
        }

        return $this->db->table('taggable_taggables')->distinct()
            ->where('taggable_type', '=', $class)
            ->join('taggable_tags', 'taggable_taggables.taggable_id', '=', 'taggable_tags.tag_id')
            ->orderBy('taggable_tags.normalized')
            ->pluck('taggable_tags.normalized');
    }
}
