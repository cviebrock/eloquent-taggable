<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Services\TagService;


/**
 * Class TagServiceTests
 */
class TagServiceTests extends TestCase
{

    /**
     * @var \Cviebrock\EloquentTaggable\Services\TagService
     */
    protected $service;

    /**
     * @var array
     */
    protected $testArray;

    /**
     * @var array
     */
    protected $testArrayNormalized;

    /**
     * @var string
     */
    protected $testString;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        // load the service
        $this->service = app(TagService::class);

        // helpers
        $this->testArray = ['Apple', 'Banana', 'Cherry'];
        $this->testArrayNormalized = ['apple', 'banana', 'cherry'];
        $this->testString = 'Apple,Banana,Cherry';
    }

    /**
     * Test the service was instantiated.
     */
    public function testServiceWasInstantiated(): void
    {
        $this->assertEquals(TagService::class, get_class($this->service));
    }

    /**
     * Test building a tag array from an array
     */
    public function testBuildTagArrayFromArray(): void
    {
        $tags = $this->service->buildTagArray($this->testArray);

        $this->assertArrayValuesAreEqual(
            $this->testArray,
            $tags
        );
    }

    /**
     * Test building a tag array from an object, which should
     * throw an exception.
     */
    public function testBuildTagArrayFromObject(): void
    {
        $object = new \stdClass;

        $this->expectException(\ErrorException::class);

        $this->service->buildTagArray($object);
    }

    /**
     * Test building a tag array from a Collection
     */
    public function testBuildTagArrayFromCollection(): void
    {
        $tags = $this->service->buildTagArray(collect($this->testArray));

        $this->assertArrayValuesAreEqual(
            $this->testArray,
            $tags
        );
    }

    /**
     * Test building a tag array from a string
     */
    public function testBuildTagArrayFromString(): void
    {
        $tags = $this->service->buildTagArray($this->testString);

        $this->assertArrayValuesAreEqual(
            $this->testArray,
            $tags
        );
    }

    /**
     * Test building a tag array from an array
     */
    public function testBuildNormalizedTagArrayFromArray(): void
    {
        $tags = $this->service->buildTagArrayNormalized($this->testArray);

        $this->assertArrayValuesAreEqual(
            $this->testArrayNormalized,
            $tags
        );
    }

    /**
     * Test building a tag array from a Collection
     */
    public function testBuildNormalizedTagArrayFromCollection(): void
    {
        $tags = $this->service->buildTagArrayNormalized(collect($this->testArray));

        $this->assertArrayValuesAreEqual(
            $this->testArrayNormalized,
            $tags
        );
    }

    /**
     * Test building a tag array from a string
     */
    public function testBuildNormalizedTagArrayFromString(): void
    {
        $tags = $this->service->buildTagArrayNormalized($this->testString);

        $this->assertArrayValuesAreEqual(
            $this->testArrayNormalized,
            $tags
        );
    }

    /**
     * Test getting the tag model keys from an array
     * of normalized tag names.
     */
    public function testGettingTagModelKeys(): void
    {
        // Create a model and generate some Tags
        $model = $this->newModel();
        $model->tag('Apple');
        $model->tag('Banana');
        $model->tag('Cherry');

        $keys = $this->service->getTagModelKeys(['apple', 'cherry']);

        $this->assertArrayValuesAreEqual(
            [1, 3],
            $keys
        );
    }

    /**
     * Test getting the tag model keys from an empty array.
     */
    public function testGettingTagModelKeysFromEmptyArray(): void
    {
        $keys = $this->service->getTagModelKeys();

        $this->assertEmpty($keys);
    }

    /**
     * Test getting all tag models.
     */
    public function testGettingAllTags(): void
    {
        // Create a model and generate some Tags
        $model = $this->newModel();
        $model->tag('Apple');
        $model->tag('Banana');
        $model->tag('Cherry');

        // Add a dummy model as well and tag it
        $dummy = $this->newDummy();
        $dummy->tag('Apple');
        $dummy->tag('Durian');

        // check the test model
        $allTags = $this->service->getAllTagsArray(TestModel::class);

        $this->assertCount(3, $allTags);
        $this->assertArrayValuesAreEqual(
            $this->testArray,
            $allTags
        );

        $allTagsNormalized = $this->service->getAllTagsArrayNormalized(TestModel::class);
        $this->assertCount(3, $allTagsNormalized);
        $this->assertArrayValuesAreEqual(
            $this->testArrayNormalized,
            $allTagsNormalized
        );

        // check the dummy model
        $allTags = $this->service->getAllTagsArray($dummy);

        $this->assertCount(2, $allTags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Durian'],
            $allTags
        );

        $allTagsNormalized = $this->service->getAllTagsArrayNormalized($dummy);
        $this->assertCount(2, $allTagsNormalized);
        $this->assertArrayValuesAreEqual(
            ['apple', 'durian'],
            $allTagsNormalized
        );

        // check all models
        $allTags = $this->service->getAllTagsArray();

        $this->assertCount(4, $allTags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry', 'Durian'],
            $allTags
        );

        $allTagsNormalized = $this->service->getAllTagsArrayNormalized();
        $this->assertCount(4, $allTagsNormalized);
        $this->assertArrayValuesAreEqual(
            ['apple', 'banana', 'cherry', 'durian'],
            $allTagsNormalized
        );
    }

    /**
     * Test finding all unused tags.
     */
    public function testGettingAllUnusedTags(): void
    {
        // Create a model and generate some tags
        $model = $this->newModel();
        $model->tag('Apple');
        $model->tag('Banana');
        $model->tag('Cherry');

        // remove some
        $model->untag(['Apple', 'Banana']);

        $unusedTags = $this->service->getAllUnusedTags();

        $this->assertCount(2, $unusedTags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana'],
            $unusedTags->pluck('name')->toArray()
        );
    }

    /**
     * Test renaming a tag.
     */
    public function testRenamingTag(): void
    {
        // Create a model and generate some tags
        $model = $this->newModel();
        $model->tag('Apple');
        $model->tag('Banana');
        $model->tag('Cherry');

        // Add a dummy model as well and tag it
        $dummy = $this->newDummy();
        $dummy->tag('Apple');
        $dummy->tag('Durian');

        // Rename the tags just for one model class
        $count = $this->service->renameTags('Apple', 'Apricot', TestModel::class);

        $this->assertEquals(1, $count);

        // Check the test model's tags were renamed
        $model->load('tags');
        $testTags = $model->getTagArrayAttribute();

        $this->assertCount(3, $testTags);
        $this->assertArrayValuesAreEqual(
            ['Apricot', 'Banana', 'Cherry'],
            $testTags
        );

        // Check the dummy model's tags were not renamed
        $dummy->load('tags');
        $dummyTags = $dummy->getTagArrayAttribute();

        $this->assertCount(2, $dummyTags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Durian'],
            $dummyTags
        );

        // Confirm the list of all tags
        $allTags = $this->service->getAllTagsArray();

        $this->assertCount(5, $allTags);
        $this->assertArrayValuesAreEqual(
            ['Apricot', 'Apple', 'Banana', 'Cherry', 'Durian'],
            $allTags
        );
    }

    /**
     * Test renaming a tag across all models.
     */
    public function testRenamingTagAllModels(): void
    {
        // Create a model and generate some Tags
        $model = $this->newModel();
        $model->tag('Apple');
        $model->tag('Banana');
        $model->tag('Cherry');

        // Add a dummy model as well and tag it
        $dummy = $this->newDummy();
        $dummy->tag('Apple');
        $dummy->tag('Durian');

        // Rename the tags just for all model classes
        $count = $this->service->renameTags('Apple', 'Apricot');

        $this->assertEquals(1, $count);

        // Check the test model's tags were renamed
        $model->load('tags');
        $testTags = $model->getTagArrayAttribute();

        $this->assertCount(3, $testTags);
        $this->assertArrayValuesAreEqual(
            ['Apricot', 'Banana', 'Cherry'],
            $testTags
        );

        // Check the dummy model's tags were renamed
        $dummy->load('tags');
        $dummyTags = $dummy->getTagArrayAttribute();

        $this->assertCount(2, $dummyTags);
        $this->assertArrayValuesAreEqual(
            ['Apricot', 'Durian'],
            $dummyTags
        );

        // Confirm the list of all tags
        $allTags = $this->service->getAllTagsArray();

        $this->assertCount(4, $allTags);
        $this->assertArrayValuesAreEqual(
            ['Apricot', 'Banana', 'Cherry', 'Durian'],
            $allTags
        );
    }

    /**
     * Test renaming a non-existent tag.
     */
    public function testRenamingNonExistingTag(): void
    {
        // Create a model and generate some Tags
        $model = $this->newModel();
        $model->tag('Apple');
        $model->tag('Banana');
        $model->tag('Cherry');

        // Rename the tags just for one model class
        $count = $this->service->renameTags('Durian', 'Date', TestModel::class);

        $this->assertEquals(0, $count);
    }

    /**
     * Test getting popular tags.
     */
    public function preparePopularTags(): void
    {
        // Generate some models and tags
        $this->newModel()->tag('Apple,Banana,Cherry');
        $this->newModel()->tag('Apple,Banana,Cherry');
        $this->newModel()->tag('Apple,Banana');
        $this->newModel()->tag('Apple,Cherry');
        $this->newModel()->tag('Apple,Cherry,Durian');
        $this->newModel()->tag('Apple,Durian');

        $this->newDummy()->tag('Apple,Cherry,Durian');
        $this->newDummy()->tag('Cherry,Durian');
        $this->newDummy()->tag('Durian');
    }

    /**
     * Test getting popular tags.
     */
    public function testPopularTags(): void
    {
        $this->preparePopularTags();

        // test all popular tags
        $popular = $this->service->getPopularTags();

        $this->assertCount(4, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        $this->assertEquals('Apple,Cherry,Durian,Banana', $popularNames);
    }

    /**
     * Test getting popular tags with a limit.
     */
    public function testPopularTagsWithLimit(): void
    {
        $this->preparePopularTags();

        $popular = $this->service->getPopularTags(2);

        $this->assertCount(2, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        $this->assertEquals('Apple,Cherry', $popularNames);
    }

    /**
     * Test getting popular tags with a limit and a model.
     */
    public function testPopularTagsWithLimitAndModel(): void
    {
        $this->preparePopularTags();

        $popular = $this->service->getPopularTags(2, TestDummy::class);

        $this->assertCount(2, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        $this->assertEquals('Durian,Cherry', $popularNames);
    }

    /**
     * Test getting popular tags with a minimum count.
     */
    public function testPopularTagsWithMinimum(): void
    {
        $this->preparePopularTags();

        $popular = $this->service->getPopularTags(10, null, 5);

        $this->assertCount(3, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        $this->assertEquals('Apple,Cherry,Durian', $popularNames);
    }

    /**
     * Test getting popular tags with a model minimum count.
     */
    public function testPopularTagsWithModelAndMinimum(): void
    {
        $this->preparePopularTags();

        $popular = $this->service->getPopularTags(10, TestModel::class, 5);

        $this->assertCount(1, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        $this->assertEquals('Apple', $popularNames);
    }
}
