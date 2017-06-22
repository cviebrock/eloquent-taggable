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
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
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
    public function testServiceWasInstantiated()
    {
        $this->assertEquals(TagService::class, get_class($this->service));
    }

    /**
     * Test building a tag array from an array
     */
    public function testBuildTagArrayFromArray()
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
    public function testBuildTagArrayFromObject()
    {
        $object = new \stdClass;

        $this->expectException(\ErrorException::class);

        $this->service->buildTagArray($object);
    }

    /**
     * Test building a tag array from a Collection
     */
    public function testBuildTagArrayFromCollection()
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
    public function testBuildTagArrayFromString()
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
    public function testBuildNormalizedTagArrayFromArray()
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
    public function testBuildNormalizedTagArrayFromCollection()
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
    public function testBuildNormalizedTagArrayFromString()
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
    public function testGettingTagModelKeys()
    {
        // First, tag the test model so we have some Tags
        $this->testModel->tag('Apple');
        $this->testModel->tag('Banana');
        $this->testModel->tag('Cherry');

        $keys = $this->service->getTagModelKeys(['apple', 'cherry']);

        $this->assertArrayValuesAreEqual(
            [1, 3],
            $keys
        );
    }

    /**
     * Test getting the tag model keys from an empty array.
     */
    public function testGettingTagModelKeysFromEmptyArray()
    {
        $keys = $this->service->getTagModelKeys([]);

        $this->assertEmpty($keys);
    }

    /**
     * Test getting all tag models.
     */
    public function testGettingAllTags()
    {
        // First, tag the test model so we have some Tags
        $this->testModel->tag('Apple');
        $this->testModel->tag('Banana');
        $this->testModel->tag('Cherry');

        // Add a dummy model as well and tag it
        $dummy = TestDummy::create(['title' => 'dummy']);
        $dummy->tag('Apple');
        $dummy->tag('Durian');

        // check the test model
        $allTags = $this->service->getAllTags(TestModel::class);

        $this->assertEquals(3, $allTags->count());
        $plucked = $allTags->pluck('name')->toArray();

        $this->assertArrayValuesAreEqual(
            $this->testArray,
            $plucked
        );

        // check the dummy model
        $allTags = $this->service->getAllTags($dummy);

        $this->assertEquals(2, $allTags->count());
        $plucked = $allTags->pluck('name')->toArray();

        $this->assertArrayValuesAreEqual(
            ['Apple', 'Durian'],
            $plucked
        );
    }
}
