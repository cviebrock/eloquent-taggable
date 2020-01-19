<?php namespace Cviebrock\EloquentTaggable\Test\Configuration;

use Cviebrock\EloquentTaggable\Models\Tag;
use Cviebrock\EloquentTaggable\Test\TestCase;


/**
 * Class CustomTableTests
 */
class CustomTableTests extends TestCase
{

    /**
     * @var \Cviebrock\EloquentTaggable\Test\TestModel
     */
    protected $testModel;

    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('taggable.tables', [
            'taggable_tags'      => 'custom_tags',
            'taggable_taggables' => 'custom_taggables',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->testModel = $this->newModel();
    }

    public function testItCanGetTheCorrectTableName(): void
    {
        self::assertEquals('custom_tags', config('taggable.tables.taggable_tags'));
        self::assertEquals('custom_taggables', config('taggable.tables.taggable_taggables'));
    }

    public function testTagging(): void
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );

        $tag = $this->testModel->tags->first();
        $this->assertInstanceOf(Tag::class, $tag);
    }
}
