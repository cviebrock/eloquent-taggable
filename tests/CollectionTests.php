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
    public function setUp()
    {
        parent::setUp();

        $this->testModel = $this->newModel()->tag('Apple,Banana,Cherry');
    }

    /**
     * Test adding tags.
     */
    public function testIsCollection()
    {
        $tags = $this->testModel->tags;

        $this->assertEquals(Collection::class, get_class($tags));
    }

    /**
     * Test getting the tag list.
     */
    public function testTagList()
    {
        $tagList = $this->testModel->tagList;

        $this->assertEquals('Apple,Banana,Cherry', $tagList);
    }

    /**
     * Test getting the normalized tag list.
     */
    public function testTagListNormalized()
    {
        $tagListNormalized = $this->testModel->tagListNormalized;

        $this->assertEquals('apple,banana,cherry', $tagListNormalized);
    }

    /**
     * Test getting the tag array.
     */
    public function testTagArray()
    {
        $tagArray = $this->testModel->tagArray;

        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $tagArray);
    }

    /**
     * Test getting the normalized tag array.
     */
    public function testTagArrayNormalized()
    {
        $tagArrayNormalized = $this->testModel->tagArrayNormalized;

        $this->assertArrayValuesAreEqual(['apple', 'banana', 'cherry'], $tagArrayNormalized);
    }
}
