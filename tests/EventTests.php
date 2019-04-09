<?php namespace Cviebrock\EloquentTaggable\Test;

/**
 * Class EventTests
 */
class EventTests extends TestCase
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

        $this->testModel = $this->newModel();
    }

    /**
     * Test basic tagging.
     */
    public function testTagging(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test adding tags from an array.
     */
    public function testTaggingFromArray(): void
    {
        $this->testModel->tag(['Apple', 'Banana', 'Cherry']);

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test tagging with an alternate delimiter.
     */
    public function testTaggingWithDelimiters(): void
    {
        $this->testModel->tag('Apple;Banana;Cherry');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test adding additional tags.
     */
    public function testTaggingAgain(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->tag('Durian');

        $this->assertCount(4, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry', 'Durian'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test removing tags.
     */
    public function testUntagging(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->untag('Banana');

        $this->assertCount(2, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test removing all tags.
     */
    public function testRemovingAllTags(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );

        $this->testModel->detag();
        $this->assertCount(0, $this->testModel->tags);
    }

    /**
     * Test retagging tags.
     */
    public function testRetagging(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->retag('Etrog,Fig,Grape');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Etrog', 'Fig', 'Grape'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test retagging a model that has no tags.
     */
    public function testRetaggingOnUntagged(): void
    {
        $this->testModel->tag('Etrog,Fig,Grape');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Etrog', 'Fig', 'Grape'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test tag normalization.
     */
    public function testNormalization(): void
    {
        $this->testModel->tag('Apple');
        $this->testModel->tag('apple');
        $this->testModel->tag('APPLE');

        $this->assertArrayValuesAreEqual(
            ['Apple'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test casting Tag to a string works
     */
    public function testTagToString(): void
    {
        $this->testModel->tag('Apple');

        $tag = $this->testModel->tags->first();
        $tagAsString = (string) $tag;

        $this->assertEquals('string', gettype($tagAsString));
        $this->assertEquals('Apple', $tagAsString);
    }

    /**
     * Test that tagging a model with duplicate tags only
     * tags the model once
     */
    public function testNonDuplicateTagging(): void
    {
        $this->testModel->tag('Apple, Apple');
        $this->assertCount(1, $this->testModel->tags);

        $this->testModel->tag(['Banana', 'banana', 'BaNaNa ']);
        $this->assertCount(2, $this->testModel->tags);
    }

    /**
     * Test that a deleted model removes relation with tags
     */
    public function testDeleteModel(): void
    {
        $this->testModel->tag('Apple');
        $this->testModel->delete(); // the model is now soft deleted
        $this->assertCount(1, $this->testModel->tags);

        $this->testModel->forceDelete();
        $this->assertCount(0, $this->testModel->tags);

        // the dummy has not soft delete logic
        $dummy = $this->newDummy();
        $dummy->tag('Apple');
        $dummy->delete();
        $this->assertCount(0, $dummy->tags);
    }

    /**
     * Test the hasTags method
     */
    public function testHasTags(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertTrue($this->testModel->hasTag('Apple'));
        $this->assertTrue($this->testModel->hasTag('Banana'));
        $this->assertTrue($this->testModel->hasTag('Cherry'));
        $this->assertFalse($this->testModel->hasTag('Durian'));
    }
}
