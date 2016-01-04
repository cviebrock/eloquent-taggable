<?php

use Orchestra\Testbench\TestCase;

/**
 * Class TaggableTest
 */
class TaggableTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        // Call migrations specific to our tests, e.g. to seed the db
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../tests/database/migrations'),
        ]);

        // Call migrations for the package
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../stubs'),
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
//        // reset base path to point to our package's src directory
//        $app['path.base'] = __DIR__ . '/../src';

        // set up database configuration
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ));
    }

    /**
     * Get Sluggable package providers.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'Cviebrock\EloquentTaggable\ServiceProvider',
        ];
    }

    protected function makePost()
    {
        return Post::create([
            'title' => \Illuminate\Support\Str::random(10),
        ]);
    }

    public function testTag1()
    {
        $post = $this->makePost();
        $post->tag('Apple,Banana,Cherry');

        dd($post);
        dd($post->tags);
    }
}
