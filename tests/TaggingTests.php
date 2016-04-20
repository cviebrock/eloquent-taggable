<?php namespace Cviebrock\EloquentTaggable\Test;

/**
 * Class TaggingTests
 */
class TaggingTests extends TestCase
{

    /**
     * Test basic tagging
     *
     * @test
     */
    public function testTagging()
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertEquals(3, count($this->testModel->tags));
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test adding tags from an array
     *
     * @test
     */
    public function testTaggingFromArray()
    {
        $this->testModel->tag(['Apple', 'Banana', 'Cherry']);

        $this->assertEquals(3, count($this->testModel->tags));
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test tagging with an alternate delimiter
     *
     * @test
     */
    public function testTaggingWithDelimiters()
    {
        $this->testModel->tag('Apple;Banana;Cherry');

        $this->assertEquals(3, count($this->testModel->tags));
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test adding additional tags
     *
     * @test
     */
    public function testTaggingAgain()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->tag('Durian');

        $this->assertEquals(4, count($this->testModel->tags));
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry', 'Durian'], $this->testModel->tagArray);
    }

    /**
     * Test removing tags
     *
     * @test
     */
    public function testUntagging()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->untag('Banana');

        $this->assertEquals(2, count($this->testModel->tags));
        $this->assertArrayValuesAreEqual(['Apple', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test removing all tags
     *
     * @test
     */
    public function testRemovingAllTags()
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertEquals(3, count($this->testModel->tags));
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);

        $this->testModel->detag();
        $this->assertEquals(0, count($this->testModel->tags));
    }

    /**
     * Test retagging tags
     *
     * @tags
     */
    public function testRetagging()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->retag('Etrog,Fig,Grape');

        $this->assertEquals(3, count($this->testModel->tags));
        $this->assertArrayValuesAreEqual(['Etrog', 'Fig', 'Grape'], $this->testModel->tagArray);
    }

    public function testNormalization()
    {
        $this->testModel->tag('Apple');
        $this->testModel->tag('apple');
        $this->testModel->tag('APPLE');

        $this->assertArrayValuesAreEqual(['Apple'], $this->testModel->tagArray);
    }
}
