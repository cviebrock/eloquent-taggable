<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Models\Tag;


/**
 * Class ConnectionTests
 */
class ConnectionTests extends TestCase
{

    /**
     * @var TestModel
     */
    protected $testModel;

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
    public function setUp()
    {
        parent::setUp();

        // test model
        $this->testModel = $this->newModel();

        // migration for the second database connection
        $this->artisan('migrate', ['--database' => 'test2']);

        // tag model
        $this->testModel->tag('Apple,Banana,Cherry');
    }

    /**
     * Test basic tagging still works
     */
    public function testTagging()
    {
        $this->assertCount(3, $this->testModel->tags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test that the test model uses the default connection,
     * but the Tag model uses the second connection
     */
    public function testModelConnection()
    {
        $this->assertEquals('test', $this->testModel->getConnectionName());

        /** @var Tag $tag */
        $tag = $this->testModel->tags->first();

        $this->assertEquals('test2', $tag->getConnectionName());
    }
}
