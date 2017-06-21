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
     * @var TestModel
     */
    protected $testModelAD;

    /**
     * @var TestDummy
     */
    protected $testDummyABC;

    /**
     * @var TestDummy
     */
    protected $testDummyAB;

    /**
     * @var TestDummy
     */
    protected $testDummyAD;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->testModelABC = $this->newModel(['title' => 'ABC']);
        $this->testModelABC->tag('Apple,Banana,Cherry');

        $this->testModelAB = $this->newModel(['title' => 'AB']);
        $this->testModelAB->tag('Apple,Banana');

        $this->testModelC = $this->newModel(['title' => 'C']);
        $this->testModelC->tag('Cherry');

        $this->testModelAD = $this->newModel(['title' => 'AD']);
        $this->testModelAD->tag('Apple,Durian');

        // extra data to check for cross-model issues
        $this->testDummyABC = TestDummy::create(['title' => 'title']);
        $this->testDummyABC->tag('Apple,Banana,Cherry');

        $this->testDummyAB = TestDummy::create(['title' => 'title']);
        $this->testDummyAB->tag('Apple,Banana');

        $this->testDummyAD = TestDummy::create(['title' => 'title']);
        $this->testDummyAD->tag('Apple,Durian');
    }

    /**
     * Test searching by all tags.
     */
    public function testWithAllTags()
    {
        /** @var Collection $models */
        $models = TestModel::withAllTags('Apple,Banana')->get();
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
     * Test searching by all tags, some of which don't exists.
     */
    public function testWithAllNonExistentTags()
    {
        /** @var Collection $models */
        $models = TestModel::withAllTags('Apple,Kumquat')->get();
        $keys = $models->modelKeys();

        $this->assertEmpty($keys);
    }

    /**
     * Test searching by any tags.
     */
    public function testWithAnyTags()
    {
        /** @var Collection $models */
        $models = TestModel::withAnyTags('Cherry,Durian')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModelABC->getKey(),
                $this->testModelC->getKey(),
                $this->testModelAD->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching by any tags, some of which don't exists.
     */
    public function testWithAnyNonExistentTags()
    {
        /** @var Collection $models */
        $models = TestModel::withAnyTags('Apple,Kumquat')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModelABC->getKey(),
                $this->testModelAB->getKey(),
                $this->testModelAD->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for a model with any tags (i.e. at least one tag).
     */
    public function testHasTags()
    {
        $models = TestModel::hasTags()->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModelABC->getKey(),
                $this->testModelAB->getKey(),
                $this->testModelC->getKey(),
                $this->testModelAD->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for models without any of the given tags.
     */
    public function testWithoutAllTags()
    {
        /** @var Collection $models */
        $models = TestModel::withoutAllTags('Apple,Banana')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel->getKey(),
                $this->testModelC->getKey(),
                $this->testModelAD->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for models without any of the given tags.
     */
    public function testWithoutAnyTags()
    {
        /** @var Collection $models */
        $models = TestModel::withoutAnyTags('Banana,Cherry')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel->getKey(),
                $this->testModelAD->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for models that have no tags at all.
     */
    public function testHasNoTags()
    {
        /** @var Collection $models */
        $models = TestModel::hasNoTags()->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel->getKey(),
            ],
            $keys
        );
    }
}
