<?php namespace Cviebrock\EloquentTaggable\Test;

use Illuminate\Database\Eloquent\Collection;


/**
 * Class CollectionTests
 */
class CollectionTests extends TestCase
{

    /**
     * Test adding tags.
     */
    public function testIsCollection()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $tags = $this->testModel->tags;

        $this->assertEquals(Collection::class, get_class($tags));
    }

    /**
     * Test getting the tag list.
     */
    public function testTagList()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertEquals('Apple,Banana,Cherry', $this->testModel->tagList);
    }

    /**
     * Test getting the normalized tag list.
     */
    public function testTagListNormalized()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertEquals('apple,banana,cherry', $this->testModel->tagListNormalized);
    }

    /**
     * Test getting the tag array.
     */
    public function testTagArray()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test getting the normalized tag array.
     */
    public function testTagArrayNormalized()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertArrayValuesAreEqual(['apple', 'banana', 'cherry'], $this->testModel->tagArrayNormalized);
    }

}
