<?php namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;


/**
 * Class TestCase
 */
abstract class TestCase extends Orchestra
{

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'test']);

        $this->beforeApplicationDestroyed(function() {
            $this->artisan('migrate:rollback');
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        // set up database configuration
        $app['config']->set('database.default', 'test');

        $app['config']->set('database.connections.test', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
            TestServiceProvider::class,
        ];
    }

    /**
     * Custom test to see if two arrays have the same values, regardless
     * of indices or order.
     *
     * @param array $expected
     * @param array $actual
     */
    protected function assertArrayValuesAreEqual(array $expected, array $actual): void
    {
        $this->assertCount(count($expected), $actual);
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * Helper to generate a test model
     *
     * @param array $data
     *
     * @return \Cviebrock\EloquentTaggable\Test\TestModel
     */
    protected function newModel(array $data = ['title' => 'test']): TestModel
    {
        return TestModel::create($data);
    }

    /**
     * Helper to generate a test dummy model
     *
     * @param array $data
     *
     * @return \Cviebrock\EloquentTaggable\Test\TestDummy
     */
    protected function newDummy(array $data = ['title' => 'dummy']): TestDummy
    {
        return TestDummy::create($data);
    }
}
