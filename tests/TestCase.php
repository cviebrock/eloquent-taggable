<?php namespace Cviebrock\EloquentTaggable\Test;

use Orchestra\Testbench\TestCase as Orchestra;


/**
 * Class TestCase
 *
 * @package Tests
 */
abstract class TestCase extends Orchestra
{

    /**
     * @var TestModel
     */
    protected $testModel;

    /**
     * @var array
     */
    protected $testData = ['title' => 'title'];

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->testModel = $this->newModel();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        // Create the taggable tables
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__ . '/../resources/database/migrations'),
        ]);

        // Create our test tables
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__ . '/database/migrations'),
        ]);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // set up database configuration
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Cviebrock\EloquentTaggable\ServiceProvider::class
        ];
    }

    /**
     * Custom test to see if two arrays have the same values, regardless
     * of indices or order.
     *
     * @param array $expected
     * @param array $actual
     */
    protected function assertArrayValuesAreEqual(array $expected, array $actual)
    {
        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual, '', 0.0, 10, true);
    }

    /**
     * Helper to generate a test model
     *
     * @param array $data
     * @return \Cviebrock\EloquentTaggable\Test\TestModel
     */
    protected function newModel($data = ['title' => 'test'])
    {
        return TestModel::create($data);
    }
}
