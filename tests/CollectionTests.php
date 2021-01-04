<?php namespace Cviebrock\EloquentTaggable\Test;

use Illuminate\Database\Eloquent\Collection;


/**
 * Class CollectionTests
 */
class CollectionTests extends TestCase
{

    /**
     * @var TestModel
     */
    protected $testModel;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->testModel = $this->newModel()->tag('Apple,Banana,Cherry');
    }

    /**
     * Test adding tags.
     */
    public function testIsCollection(): void
    {
        $tags = $this->testModel->tags;

        self::assertEquals(Collection::class, get_class($tags));
    }

    /**
     * Test getting the tag list.
     */
    public function testTagList(): void
    {
        $tagList = $this->testModel->tagList;

        self::assertEquals('Apple,Banana,Cherry', $tagList);
    }

    /**
     * Test getting the normalized tag list.
     */
    public function testTagListNormalized(): void
    {
        $tagListNormalized = $this->testModel->tagListNormalized;

        self::assertEquals('apple,banana,cherry', $tagListNormalized);
    }

    /**
     * Test getting the tag array.
     */
    public function testTagArray(): void
    {
        $tagArray = $this->testModel->tagArray;

        self::assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $tagArray);
    }

    /**
     * Test getting the normalized tag array.
     */
    public function testTagArrayNormalized(): void
    {
        $tagArrayNormalized = $this->testModel->tagArrayNormalized;

        self::assertArrayValuesAreEqual(['apple', 'banana', 'cherry'], $tagArrayNormalized);
    }
}
