<?php namespace Cviebrock\EloquentTaggable\Test\Configuration;

use Cviebrock\EloquentTaggable\Test\TestCase;
use Cviebrock\EloquentTaggable\Test\TestModel;


/**
 * Class CustomNormalizerTests
 */
class CustomNormalizerTests extends TestCase
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

        $app['config']->set('taggable.normalizer', function($string) {
            return strrev($string);
        });
    }

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->testModel = $this->newModel()->tag('Apple,Banana,Cherry');
    }

    /**
     * Test tag list with custom normalizer
     * (this shouldn't affect the "regular" tagArray)
     */
    public function testCustomNormalizer()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry'],
            $this->testModel->getTagArrayAttribute()
        );
    }

    /**
     * Test normalized tag list with custom glue.
     */
    public function testCustomNormalizerNormalized()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertArrayValuesAreEqual(
            ['elppA', 'ananaB', 'yrrehC'],
            $this->testModel->getTagArrayNormalizedAttribute()
        );
    }
}
