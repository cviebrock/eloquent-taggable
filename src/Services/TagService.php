<?php namespace Cviebrock\EloquentTaggable\Services;

use Cviebrock\EloquentTaggable\Models\Tag;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


/**
 * Class Util.
 */
class TagService
{

    /**
     * @var array
     */
    protected $config;

    /**
     * TagService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
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
     * Convert a delimited string into an array of tag strings.
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
     * Convert a delimited string into an array of normalized tag strings.
     *
     * @param string|array $tags
     *
     * @return array
     */
    public function buildTagArrayNormalized($tags)
    {
        $tags = $this->buildTagArray($tags);

        return array_map([$this, 'normalize'], $tags);
    }

    /**
     * Build a delimited string from a model's tags.
     *
     * @param Model $model
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
     * @return string
     */
    public function joinList(array $array)
    {
        return implode($this->config['glue'], $array);
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
        return call_user_func($this->config['normalizer'], $string);
    }

    /**
     * Get all Tags for the given class.
     *
     * @param Model|string $class
     *
     * @return Collection
     */
    public function getAllTags($class)
    {
        if ($class instanceof Model) {
            $class = get_class($class);
        }

        $sql = 'SELECT DISTINCT t.*' .
          ' FROM taggable_taggables tt LEFT JOIN taggable_tags t ON tt.tag_id=t.tag_id' .
          ' WHERE tt.taggable_type = ?';

        return Tag::hydrateRaw($sql, [$class]);
    }
}
