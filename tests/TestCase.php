<?php

namespace Cviebrock\EloquentTaggable\Test;

use Cviebrock\EloquentTaggable\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Class TestCase.
 */
abstract class TestCase extends Orchestra
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        $this->beforeApplicationDestroyed(static function () {
            (new \CreateTestModelsTable())->down();
            // @phpstan-ignore-next-line class.notFound
            (new \CreateTaggableTable())->down();
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * {@inheritdoc}
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
     */
    protected static function assertArrayValuesAreEqual(array $expected, array $actual): void
    {
        self::assertCount(count($expected), $actual);
        self::assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * Helper to generate a test model.
     */
    protected function newModel(array $data = ['title' => 'test']): TestModel
    {
        return TestModel::create($data);
    }

    /**
     * Helper to generate a test dummy model.
     */
    protected function newDummy(array $data = ['title' => 'dummy']): TestDummy
    {
        return TestDummy::create($data);
    }

    /**
     * Set up the database.
     */
    private function setUpDatabase(): void
    {
        include_once __DIR__ . '/../resources/database/migrations/create_taggable_table.php.stub';
        // @phpstan-ignore-next-line class.notFound
        (new \CreateTaggableTable())->up();

        include_once __DIR__ . '/database/migrations/2013_11_04_163552_create_test_models_table.php';
        (new \CreateTestModelsTable())->up();
    }
}
