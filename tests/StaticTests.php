<?php namespace Cviebrock\EloquentTaggable\Test;

/**
 * Class StaticTests
 */
class StaticTests extends TestCase
{

    /**
     * @var TestModel
     */
    protected $testModelAB;

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

        $this->testModel->tag('Apple,Banana,Cherry');

        $this->testModelAB = $this->newModel()->tag('Apple,Banana,Cherry');

        // Tag another model

        $this->testDummy = TestDummy::create(['title' => 'title']);
        $this->testDummy->tag('Durian');
    }

    /**
     * Test finding all the tags for a model
     *
     * @test
     */
    public function testAllTags()
    {
        $tags = TestModel::allTags();

        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $tags);
    }

    /**
     * Test finding all the tags for a model
     *
     * @test
     */
    public function testAllTagsList()
    {
        $tags = TestModel::allTagsList();

        $this->assertEquals('Apple,Banana,Cherry', $tags);
    }
}
