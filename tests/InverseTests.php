<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Models\Tag;


/**
 * Class InverseTests
 */
class InverseTests extends TestCase
{

    /**
     * @var TestModel
     */
    protected $testModel1;

    /**
     * @var TestModel
     */
    protected $testModel2;

    /**
     * @var TestModel
     */
    protected $testModel3;

    /**
     * @var TestModel
     */
    protected $testModel4;

    /**
     * @var TestModel
     */
    protected $testModel5;

    /**
     * @var TestModel
     */
    protected $testModel6;

    /**
     * @var TestModel
     */
    protected $testModel7;

    /**
     * @var TestModel
     */
    protected $testModel8;

    /**
     * @var TestDummy
     */
    protected $testDummy1;

    /**
     * @var TestDummy
     */
    protected $testDummy2;

    /**
     * @var TestDummy
     */
    protected $testDummy3;

    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // set up database configuration
        $app['config']->set('taggable.taggedModels', [
            'test_models'  => TestModel::class,
            'test_dummies' => TestDummy::class,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        // test models
        $this->testModel1 = $this->newModel(); // no tags
        $this->testModel2 = $this->newModel()->tag('Apple');
        $this->testModel3 = $this->newModel()->tag('Apple,Banana');
        $this->testModel4 = $this->newModel()->tag('Apple,Banana,Cherry');
        $this->testModel5 = $this->newModel()->tag('Cherry');
        $this->testModel6 = $this->newModel()->tag('Apple,Durian');
        $this->testModel7 = $this->newModel()->tag('Banana,Durian');
        $this->testModel8 = $this->newModel()->tag('Apple,Banana,Durian');

        // extra data to check for cross-model issues
        $this->testDummy1 = $this->newDummy()->tag('Apple,Banana,Cherry');
        $this->testDummy2 = $this->newDummy()->tag('Apple,Banana');
        $this->testDummy3 = $this->newDummy()->tag('Apple,Durian');
    }

    /**
     * Test finding a model via tag.
     */
    public function testTagFindModels(): void
    {
        // load the tag
        $tag = Tag::findByName('Banana');

        // Check the test models
        $models = $tag->test_models;
        $keys = $models->modelKeys();

        self::assertArrayValuesAreEqual(
            [
                $this->testModel3->getKey(),
                $this->testModel4->getKey(),
                $this->testModel7->getKey(),
                $this->testModel8->getKey(),
            ],
            $keys
        );

        // Check the dummy models
        $models = $tag->test_dummies;
        $keys = $models->modelKeys();

        self::assertArrayValuesAreEqual(
            [
                $this->testDummy1->getKey(),
                $this->testModel2->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test finding a model via tag.
     */
    public function testTagFindModelsNonExistent(): void
    {
        // First ensure the tag exists, but isn't attached to any model
        $this->testModel1->tag('Fig');
        $this->testModel1->detag();

        // Load the tag
        $tag = Tag::findByName('Fig');

        // Check the test models
        $models = $tag->test_models;

        self::assertEmpty($models);
    }
}
