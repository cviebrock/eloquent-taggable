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
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // build some test models
        $this->testModel = $this->newModel()->tag('Apple,Banana,Cherry');
        $this->testModel2 = $this->newModel()->tag('Apple,Cherry');

        // build another model
        $this->testDummy = $this->newDummy()->tag('Durian');
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
    public function testAllTagsList()
    {
        $tags = TestModel::allTagsList();

        $this->assertEquals('Apple,Banana,Cherry', $tags);
    }
}
