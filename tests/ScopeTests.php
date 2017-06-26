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
    public function setUp()
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
     * Test searching by all tags.
     */
    public function testWithAllTags()
    {
        /** @var Collection $models */
        $models = TestModel::withAllTags('Apple,Banana')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel3->getKey(),
                $this->testModel4->getKey(),
                $this->testModel8->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching by all tags, but passing no tags.
     */
    public function testWithAllTagsEmpty()
    {
        /** @var Collection $models */
        $models = TestModel::withAllTags('')->get();

        $this->assertEmpty($models);
    }

    /**
     * Test searching by all tags, some of which don't exists.
     */
    public function testWithAllNonExistentTags()
    {
        /** @var Collection $models */
        $models = TestModel::withAllTags('Apple,Kumquat')->get();

        $this->assertEmpty($models);
    }

    /**
     * Test searching by any tags.
     */
    public function testWithAnyTags()
    {
        /** @var Collection $models */
        $models = TestModel::withAnyTags('Apple,Banana')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel2->getKey(),
                $this->testModel3->getKey(),
                $this->testModel4->getKey(),
                $this->testModel6->getKey(),
                $this->testModel7->getKey(),
                $this->testModel8->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching by any tags, but passing no tags.
     */
    public function testWithAnyTagsEmpty()
    {
        /** @var Collection $models */
        $models = TestModel::withAnyTags('')->get();

        $this->assertEmpty($models);
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
                $this->testModel2->getKey(),
                $this->testModel3->getKey(),
                $this->testModel4->getKey(),
                $this->testModel6->getKey(),
                $this->testModel8->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for a model with any tags (i.e. at least one tag).
     */
    public function testIsTagged()
    {
        $models = TestModel::isTagged()->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel2->getKey(),
                $this->testModel3->getKey(),
                $this->testModel4->getKey(),
                $this->testModel5->getKey(),
                $this->testModel6->getKey(),
                $this->testModel7->getKey(),
                $this->testModel8->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for models without all of the given tags,
     * not including models with no tags (default behaviour).
     */
    public function testWithoutAllTags()
    {
        /** @var Collection $models */
        $models = TestModel::withoutAllTags('Apple,Banana')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel2->getKey(),
                $this->testModel5->getKey(),
                $this->testModel6->getKey(),
                $this->testModel7->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for models without any of the given tags,
     * including models with no tags.
     */
    public function testWithoutAllTagsIncludingUntagged()
    {
        /** @var Collection $models */
        $models = TestModel::withoutAllTags('Apple,Banana', true)->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel1->getKey(),
                $this->testModel2->getKey(),
                $this->testModel5->getKey(),
                $this->testModel6->getKey(),
                $this->testModel7->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for models without any of the given tags,
     * not including models with no tags (default behaviour).
     */
    public function testWithoutAnyTags()
    {
        /** @var Collection $models */
        $models = TestModel::withoutAnyTags('Apple,Banana')->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel5->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for models without any of the given tags.
     * including models with no tags.
     */
    public function testWithoutAnyTagsIncludingUntagged()
    {
        /** @var Collection $models */
        $models = TestModel::withoutAnyTags('Apple,Banana', true)->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel1->getKey(),
                $this->testModel5->getKey(),
            ],
            $keys
        );
    }

    /**
     * Test searching for models that have no tags at all.
     */
    public function testIsNotTagged()
    {
        /** @var Collection $models */
        $models = TestModel::isNotTagged()->get();
        $keys = $models->modelKeys();

        $this->assertArrayValuesAreEqual(
            [
                $this->testModel1->getKey(),
            ],
            $keys
        );
    }
}
