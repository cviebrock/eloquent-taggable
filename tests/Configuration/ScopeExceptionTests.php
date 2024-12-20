<?php

namespace Cviebrock\EloquentTaggable\Test\Configuration;

use Cviebrock\EloquentTaggable\Exceptions\NoTagsSpecifiedException;
use Cviebrock\EloquentTaggable\Test\TestCase;
use Cviebrock\EloquentTaggable\Test\TestModel;

/**
 * Class ScopeTests.
 *
 * @internal
 */
class ScopeExceptionTests extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('taggable.throwEmptyExceptions', true);
    }

    /**
     * Test searching by all tags, but passing no tags.
     */
    public function testWithAllTagsEmpty()
    {
        $this->expectException(NoTagsSpecifiedException::class);

        TestModel::withAllTags('')->get();
    }

    /**
     * Test searching by any tags, but passing no tags.
     */
    public function testWithAnyTagsEmpty()
    {
        $this->expectException(NoTagsSpecifiedException::class);

        TestModel::withAnyTags('')->get();
    }
}
