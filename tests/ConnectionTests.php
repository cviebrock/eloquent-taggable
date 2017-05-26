<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\Models\Tag;


/**
 * Class ConnectionTests
 */
class ConnectionTests extends TestCase
{

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
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
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // migration for the second database connection
        $this->artisan('migrate', ['--database' => 'test2']);
    }

    /**
     * Test basic tagging still works
     */
    public function testTagging()
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertEquals(3, count($this->testModel->tags));
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test that the test model uses the default connection,
     * but the Tag model uses the second connection
     */
    public function testModelConnection()
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertEquals('test', $this->testModel->getConnectionName());

        /** @var Tag $tag */
        $tag = $this->testModel->tags->first();

        $this->assertEquals('test2', $tag->getConnectionName());
    }

}
