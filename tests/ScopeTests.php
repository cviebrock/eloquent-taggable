<?php namespace Cviebrock\EloquentTaggable\Test;

use Illuminate\Database\Eloquent\Collection;


/**
 * Class ScopeTests
 */
class ScopeTests extends TestCase
{

    /**
     * @var TestModel
     */
    protected $testModelABC;

    /**
     * @var TestModel
     */
    protected $testModelAB;

    /**
     * @var TestModel
     */
    protected $testModelC;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->testModelABC = $this->newModel();
        $this->testModelABC->tag('Apple,Banana,Cherry');

        $this->testModelAB = $this->newModel();
        $this->testModelAB->tag('Apple,Banana');

        $this->testModelC = $this->newModel();
        $this->testModelC->tag('Cherry');
    }

    /**
     * Test searching by all tags
     *
     * @test
     */
    public function testWithAllTags()
    {
        /** @var Collection $models */
        $models = TestModel::withAllTags('Banana')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModelABC->getKey(),
                $this->testModelAB->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching by any tags
     *
     * @test
     */
    public function testWithAnyTags()
    {
        /** @var Collection $models */
        $models = TestModel::withAnyTags('Banana,Cherry')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModelABC->getKey(),
                $this->testModelAB->getKey(),
                $this->testModelC->getKey(),
            ],
            $keys
        );
    }

    public function testWithoutTags()
    {
        /** @var Collection $models */
        $models = TestModel::withoutTags()->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel->getKey()
            ],
            $keys
        );
    }
}
