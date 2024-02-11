<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Models\Tag;
use Cviebrock\EloquentTaggable\Services\TagService;


/**
 * Class ConnectionTests
 */
class ConnectionTests extends TestCase
{

    /** @var TestModel */
    protected $testModel;

    /** @var TestModel */
    protected $testModel2;

    /** @var TagService */
    private $service;

    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // second database for connection tests
        $app['config']->set('database.connections.test2', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => 'test2',
        ]);

        $app['config']->set('taggable.connection', 'test2');
    }

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        // test model
        $this->testModel = $this->newModel();
        $this->testModel2 = $this->newModel();

        // migration for the second database connection
        $this->artisan('migrate', ['--database' => 'test2']);

        $this->beforeApplicationDestroyed(function() {
            $this->artisan('migrate:rollback', ['--database' => 'test2']);
        });

        // tag models
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->testModel2->tag('Banana,Durian');

        // load the service
        $this->service = app(TagService::class);
    }

    /**
     * Test basic tagging still works
     */
    public function testTagging(): void
    {
        self::assertCount(3, $this->testModel->tags);
        self::assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test that the test model uses the default connection,
     * but the Tag model uses the second connection and the table prefix.
     */
    public function testModelConnection(): void
    {
        $defaultConnection = $this->app['config']->get('database.default');
        self::assertEquals($defaultConnection, $this->testModel->getConnectionName());

        /** @var Tag $tag */
        $tag = $this->testModel->tags->first();

        self::assertEquals('test2', $tag->getConnectionName());
        self::assertEquals('test2', $tag->getConnection()->getTablePrefix());
    }

    /**
     * Test that the tag table prefix is used for getAllTags()
     */
    public function testPrefixWhenGettingAllTags(): void
    {
        // check the test model
        $allTags = $this->service->getAllTagsArray(TestModel::class);

        self::assertCount(4, $allTags);
        self::assertArrayValuesAreEqual(
            ['Apple','Banana','Cherry','Durian'],
            $allTags
        );

        // check the popular tags
        $popularTags = $this->service->getPopularTags(1);
        self::assertCount(1, $popularTags);

        $popularTag = $popularTags->first();
        self::assertEquals('Banana', $popularTag->name);
        self::assertEquals(2, $popularTag->taggable_count);

        // check unused tags
        $this->testModel2->untag('Durian');
        $unusedTags = $this->service->getAllUnusedTags();
        self::assertCount(1, $unusedTags);

        $unusedTag = $unusedTags->first();
        self::assertEquals('Durian',$unusedTag->name);

    }
}
