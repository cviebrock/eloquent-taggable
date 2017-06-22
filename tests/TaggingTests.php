<?php namespace Cviebrock\EloquentTaggable\Test;

/**
 * Class TaggingTests
 */
class TaggingTests extends TestCase
{

    /**
     * Test basic tagging.
     */
    public function testTagging()
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test adding tags from an array.
     */
    public function testTaggingFromArray()
    {
        $this->testModel->tag(['Apple', 'Banana', 'Cherry']);

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test tagging with an alternate delimiter.
     */
    public function testTaggingWithDelimiters()
    {
        $this->testModel->tag('Apple;Banana;Cherry');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test adding additional tags.
     */
    public function testTaggingAgain()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->tag('Durian');

        $this->assertCount(4, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry', 'Durian'], $this->testModel->tagArray);
    }

    /**
     * Test removing tags.
     */
    public function testUntagging()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->untag('Banana');

        $this->assertCount(2, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(['Apple', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test removing all tags.
     */
    public function testRemovingAllTags()
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);

        $this->testModel->detag();
        $this->assertCount(0, $this->testModel->tags);
    }

    /**
     * Test retagging tags.
     */
    public function testRetagging()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->retag('Etrog,Fig,Grape');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(['Etrog', 'Fig', 'Grape'], $this->testModel->tagArray);
    }

    /**
     * Test tag normalization.
     */
    public function testNormalization()
    {
        $this->testModel->tag('Apple');
        $this->testModel->tag('apple');
        $this->testModel->tag('APPLE');

        $this->assertArrayValuesAreEqual(['Apple'], $this->testModel->tagArray);
    }

    /**
     * Test casting Tag to a string works
     */
    public function testTagToString()
    {
        $this->testModel->tag('Apple');

        $tag = $this->testModel->tags->first();
        $tagAsString = (string) $tag;

        $this->assertEquals('string', gettype($tagAsString));
        $this->assertEquals('Apple', $tagAsString);
    }
}
