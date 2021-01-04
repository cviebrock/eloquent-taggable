<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Services\TagService;


/**
 * Class TaggingTests
 */
class TaggingTests extends TestCase
{

    /**
     * @var TestModel
     */
    protected $testModel;

    /**
     * @var TagService
     */
    protected $service;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->testModel = $this->newModel();
        $this->service = app(TagService::class);
    }

    /**
     * Test basic tagging.
     */
    public function testTagging(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        self::assertCount(3, $this->testModel->tags);
        self::assertArrayValuesAreEqual(
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

        self::assertCount(3, $this->testModel->tags);
        self::assertArrayValuesAreEqual(
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

        self::assertCount(3, $this->testModel->tags);
        self::assertArrayValuesAreEqual(
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

        self::assertCount(4, $this->testModel->tags);
        self::assertArrayValuesAreEqual(
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

        self::assertCount(2, $this->testModel->tags);
        self::assertArrayValuesAreEqual(
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

        self::assertCount(3, $this->testModel->tags);
        self::assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );

        $this->testModel->detag();
        self::assertCount(0, $this->testModel->tags);
    }

    /**
     * Test retagging tags.
     */
    public function testRetagging(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel->retag('Etrog,Fig,Grape');

        self::assertCount(3, $this->testModel->tags);
        self::assertArrayValuesAreEqual(
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

        self::assertCount(3, $this->testModel->tags);
        self::assertArrayValuesAreEqual(
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

        self::assertArrayValuesAreEqual(
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

        self::assertEquals('string', gettype($tagAsString));
        self::assertEquals('Apple', $tagAsString);
    }

    /**
     * Test that tagging a model with duplicate tags only
     * tags the model once
     */
    public function testNonDuplicateTagging(): void
    {
        $this->testModel->tag('Apple, Apple');
        self::assertCount(1, $this->testModel->tags);

        $this->testModel->tag(['Banana', 'banana', 'BaNaNa ']);
        self::assertCount(2, $this->testModel->tags);

        $this->testModel->tag('Cherry');
        $this->testModel->tag('CHERRY');
        self::assertCount(3, $this->testModel->tags);

        self::assertCount(3, $this->service->getAllTags());
    }

    /**
     * Test that a deleted model removes relation with tags
     */
    public function testDeleteModel(): void
    {
        $this->testModel->tag('Apple');
        $this->testModel->delete(); // the model is now soft deleted
        self::assertCount(1, $this->testModel->tags);

        $this->testModel->forceDelete();
        self::assertCount(0, $this->testModel->tags);

        // the dummy has not soft delete logic
        $dummy = $this->newDummy();
        $dummy->tag('Apple');
        $dummy->delete();
        self::assertCount(0, $dummy->tags);
    }

    /**
     * Test the hasTags method
     */
    public function testHasTags(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        self::assertTrue($this->testModel->hasTag('Apple'));
        self::assertTrue($this->testModel->hasTag('Banana'));
        self::assertTrue($this->testModel->hasTag('Cherry'));
        self::assertFalse($this->testModel->hasTag('Durian'));
    }

    /**
     * Test tagging a model using Tag IDs
     */
    public function testTagById(): void
    {
        // initialize some tags
        $this->testModel->tag('Apple,Banana,Cherry,Durian');

        $apple = $this->service->find('apple');
        $banana = $this->service->find('banana');

        // create a new model and tag it by ID
        $newModel = $this->newModel();
        $newModel->tagById([
            $apple->getKey(),
            $banana->getKey(),
        ]);

        self::assertArrayValuesAreEqual(
            ['Apple', 'Banana'],
            $newModel->getTagArrayAttribute()
        );
    }

    /**
     * Test untagging a model using Tag IDs
     */
    public function testUntagById(): void
    {
        // initialize some tags
        $this->testModel->tag('Apple,Banana,Cherry,Durian');

        // grab some of them
        $apple = $this->service->find('Apple');
        $banana = $this->service->find('Banana');

        // untag by ID
        $this->testModel->untagById([
            $apple->getKey(),
            $banana->getKey(),
        ]);

        self::assertArrayValuesAreEqual(
            ['Cherry', 'Durian'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test retagging a model using Tag IDs
     */
    public function testRetagById(): void
    {
        // initialize some tags
        $this->testModel->tag('Apple,Banana,Cherry,Durian');

        // grab some of them
        $apple = $this->service->find('Apple');
        $cherry = $this->service->find('Cherry');

        // retag by ID
        $this->testModel->retagById([
            $apple->getKey(),
            $cherry->getKey(),
        ]);

        self::assertArrayValuesAreEqual(
            ['Apple', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    public function testNormalizedAccents(): void
    {
        $this->testModel->tag('Péché');
        $this->testModel->tag('Peche');
        $this->testModel->tag('péché');

        self::assertArrayValuesAreEqual(
            ['Péché', 'Peche'],
            $this->testModel->getTagArrayAttribute()
        );
    }
}
