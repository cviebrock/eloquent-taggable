<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Events\ModelTagged;
use Cviebrock\EloquentTaggable\Events\ModelUntagged;
use Illuminate\Support\Facades\Event;

/**
 * Class EventTests
 */
class EventTests extends TestCase
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

        Event::fake();

        $this->testModel = $this->newModel();
        $this->testModel->tag('Apple');
    }

    /**
     * Test ModelTagged event.
     */
    public function testModelTaggedEvent(): void
    {
        Event::assertDispatched(ModelTagged::class);
    }

    /**
     * Test ModelUntagged event.
     */
    public function testModelUntaggedEvent(): void
    {
        $this->testModel->untag('Apple');

        Event::assertDispatched(ModelUntagged::class);
    }

}
