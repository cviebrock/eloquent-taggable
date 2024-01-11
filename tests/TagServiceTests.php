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
        self::assertEquals(TagService::class, get_class($this->service));
    }

    /**
     * Test building a tag array from an array
     */
    public function testBuildTagArrayFromArray(): void
    {
        $tags = $this->service->buildTagArray($this->testArray);

        self::assertArrayValuesAreEqual(
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

        self::assertArrayValuesAreEqual(
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

        self::assertArrayValuesAreEqual(
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

        self::assertArrayValuesAreEqual(
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

        self::assertArrayValuesAreEqual(
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

        self::assertArrayValuesAreEqual(
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

        self::assertArrayValuesAreEqual(
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

        self::assertEmpty($keys);
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

        self::assertCount(3, $allTags);
        self::assertArrayValuesAreEqual(
            $this->testArray,
            $allTags
        );

        $allTagsNormalized = $this->service->getAllTagsArrayNormalized(TestModel::class);
        self::assertCount(3, $allTagsNormalized);
        self::assertArrayValuesAreEqual(
            $this->testArrayNormalized,
            $allTagsNormalized
        );

        // check the dummy model
        $allTags = $this->service->getAllTagsArray($dummy);

        self::assertCount(2, $allTags);
        self::assertArrayValuesAreEqual(
            ['Apple', 'Durian'],
            $allTags
        );

        $allTagsNormalized = $this->service->getAllTagsArrayNormalized($dummy);
        self::assertCount(2, $allTagsNormalized);
        self::assertArrayValuesAreEqual(
            ['apple', 'durian'],
            $allTagsNormalized
        );

        // check all models
        $allTags = $this->service->getAllTagsArray();

        self::assertCount(4, $allTags);
        self::assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry', 'Durian'],
            $allTags
        );

        $allTagsNormalized = $this->service->getAllTagsArrayNormalized();
        self::assertCount(4, $allTagsNormalized);
        self::assertArrayValuesAreEqual(
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

        self::assertCount(2, $unusedTags);
        self::assertArrayValuesAreEqual(
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

        self::assertEquals(1, $count);

        // Check the test model's tags were renamed
        $model->load('tags');
        $testTags = $model->getTagArrayAttribute();

        self::assertCount(3, $testTags);
        self::assertArrayValuesAreEqual(
            ['Apricot', 'Banana', 'Cherry'],
            $testTags
        );

        // Check the dummy model's tags were not renamed
        $dummy->load('tags');
        $dummyTags = $dummy->getTagArrayAttribute();

        self::assertCount(2, $dummyTags);
        self::assertArrayValuesAreEqual(
            ['Apple', 'Durian'],
            $dummyTags
        );

        // Confirm the list of all tags
        $allTags = $this->service->getAllTagsArray();

        self::assertCount(5, $allTags);
        self::assertArrayValuesAreEqual(
            ['Apricot', 'Apple', 'Banana', 'Cherry', 'Durian'],
            $allTags
        );
    }

    /**
     * Test renaming a tag with a custom morph class key.
     */
    public function testRenamingTagWithCustomMorphClass(): void
    {
        // Create a model with a custom morph class key
        $morphModel = new class extends TestModel {
            protected $attributes = [
                'title' => 'testing morph model'
            ];
            public function getMorphClass()
            {
                return 'test-morph-model'; // can any custom key that is different from the class name
            }
        };
        $morphModel->save();

        $morphModel->tag('Apple');
        $morphModel->tag('Banana');
        $morphModel->tag('Cherry');

        // Rename the tags the morphModel class should have exactly 1 update
        $count = $this->service->renameTags('Apple', 'Apricot', $morphModel);
        $this->assertEquals(1, $count);
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

        self::assertEquals(1, $count);

        // Check the test model's tags were renamed
        $model->load('tags');
        $testTags = $model->getTagArrayAttribute();

        self::assertCount(3, $testTags);
        self::assertArrayValuesAreEqual(
            ['Apricot', 'Banana', 'Cherry'],
            $testTags
        );

        // Check the dummy model's tags were renamed
        $dummy->load('tags');
        $dummyTags = $dummy->getTagArrayAttribute();

        self::assertCount(2, $dummyTags);
        self::assertArrayValuesAreEqual(
            ['Apricot', 'Durian'],
            $dummyTags
        );

        // Confirm the list of all tags
        $allTags = $this->service->getAllTagsArray();

        self::assertCount(4, $allTags);
        self::assertArrayValuesAreEqual(
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

        self::assertEquals(0, $count);
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

        self::assertCount(4, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        self::assertEquals('Apple,Cherry,Durian,Banana', $popularNames);
    }

    /**
     * Test getting popular tags with a limit.
     */
    public function testPopularTagsWithLimit(): void
    {
        $this->preparePopularTags();

        $popular = $this->service->getPopularTags(2);

        self::assertCount(2, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        self::assertEquals('Apple,Cherry', $popularNames);
    }

    /**
     * Test getting popular tags with a limit and a model.
     */
    public function testPopularTagsWithLimitAndModel(): void
    {
        $this->preparePopularTags();

        $popular = $this->service->getPopularTags(2, TestDummy::class);

        self::assertCount(2, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        self::assertEquals('Durian,Cherry', $popularNames);
    }

    /**
     * Test getting popular tags with a minimum count.
     */
    public function testPopularTagsWithMinimum(): void
    {
        $this->preparePopularTags();

        $popular = $this->service->getPopularTags(10, null, 5);

        self::assertCount(3, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        self::assertEquals('Apple,Cherry,Durian', $popularNames);
    }

    /**
     * Test getting popular tags with a model minimum count.
     */
    public function testPopularTagsWithModelAndMinimum(): void
    {
        $this->preparePopularTags();

        $popular = $this->service->getPopularTags(10, TestModel::class, 5);

        self::assertCount(1, $popular);

        $popularNames = implode(',', $popular->pluck('name')->toArray());
        self::assertEquals('Apple', $popularNames);
    }
}
