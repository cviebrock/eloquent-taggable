<?php

namespace Cviebrock\EloquentTaggable\Test;

class CustomMigrationTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('taggable.tables.taggable_tags', 'test_taggable_tags');
        $app['config']->set('taggable.tables.taggable_taggables', 'test_taggable_taggables');
    }

    public function test_it_can_get_the_correct_table_name()
    {
        self::assertEquals('test_taggable_tags', config('taggable.tables.taggable_tags'));
        self::assertEquals('test_taggable_taggables', config('taggable.tables.taggable_taggables'));
    }

}
