<?php namespace Cviebrock\EloquentTaggable\Test;

/**
 * Class StaticTests
 */
class StaticTests extends TestCase
{

    /**
     * @var TestModel
     */
    protected $testModel;

    /**
     * @var TestModel
     */
    protected $testModel2;

    /**
     * @var TestDummy
     */
    protected $testDummy;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        // build some test models
        $this->testModel = $this->newModel()->tag('Apple,Banana,Cherry');
        $this->testModel2 = $this->newModel()->tag('Apple,Cherry');
        $this->testModel3 = $this->newModel()->tag('Apple');

        // build another model
        $this->testDummy = $this->newDummy()->tag('Apple,Durian');
    }

    /**
     * Test finding all the tags for a model.
     */
    public function testAllTags()
    {
        $tags = TestModel::allTags();

        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $tags);
    }

    /**
     * Test finding all the tags for a model, in a list.
     */
    public function testAllTagsList(): void
    {
        $tags = TestModel::allTagsList();

        $this->assertEquals('Apple,Banana,Cherry', $tags);
    }

    /**
     * Test renaming the tags for a model.
     */
    public function testRenameTags(): void
    {
        TestModel::renameTag('Apple', 'Apricot');

        $tags = TestModel::allTagsList();
        $this->assertEquals('Apricot,Banana,Cherry', $tags);

        // make sure the second model's tags didn't get renamed

        $tags = TestDummy::allTagsList();
        $this->assertEquals('Apple,Durian', $tags);
    }

    /**
     * Test getting the popular tags for a model.
     */
    public function testPopularTags(): void
    {
        $tags = TestModel::popularTags();
        $expected = [
            'Apple'  => 3,
            'Cherry' => 2,
            'Banana' => 1,
        ];

        $this->assertArrayValuesAreEqual($expected, $tags);
    }

    /**
     * Test getting the popular tags for a model, normalized.
     */
    public function testPopularTagsNormalized(): void
    {
        $tags = TestModel::popularTagsNormalized();
        $expected = [
            'apple'  => 3,
            'cherry' => 2,
            'banana' => 1,
        ];

        $this->assertArrayValuesAreEqual($expected, $tags);
    }

    /**
     * Test getting the popular tags for a model, with a limit.
     */
    public function testPopularTagsLimited(): void
    {
        $tags = TestModel::popularTags(2);

        $expected = [
            'Apple'  => 3,
            'Cherry' => 2,
        ];

        $this->assertArrayValuesAreEqual($expected, $tags);
    }

    /**
     * Test getting the popular tags for a model, with a limit.
     */
    public function testPopularTagsLimitedNormalized(): void
    {
        $tags = TestModel::popularTagsNormalized(2);

        $expected = [
            'apple'  => 3,
            'cherry' => 2,
        ];

        $this->assertArrayValuesAreEqual($expected, $tags);
    }
}
