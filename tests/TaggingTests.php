<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Events\ModelTagged;
use Cviebrock\EloquentTaggable\Events\ModelUntagged;


/**
 * Class TaggingTests
 */
class TaggingTests extends TestCase
{

    /**
     * @var TestModel
     */
    protected $testModel;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->testModel = $this->newModel();
    }

    /**
     * Test ModelTagged event.
     */
    public function testModelTaggedEvent(): void
    {
        $this->expectsEvents(ModelTagged::class);

        $this->testModel->tag('Apple');
    }

    /**
     * Test ModelUntagged event.
     */
    public function testModelUntaggedEvent(): void
    {
        $this->testModel->tag('Apple');

        $this->expectsEvents(ModelUntagged::class);

        $this->testModel->untag('Apple');
    }

}
