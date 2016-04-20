<?php namespace Cviebrock\EloquentTaggable\Test\Configuration;

use Cviebrock\EloquentTaggable\Test\TestCase;


/**
 * Class CustomNormalizerTests
 */
class CustomNormalizerTests extends TestCase
{

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('taggable.normalizer', function ($string) {
            return strrev($string);
        });
    }

    /**
     * Test tag list with custom normalizer
     * (this shouldn't affect the "regular" tagArray)
     *
     * @test
     */
    public function testCustomNormalizer()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry'], $this->testModel->tagArray);
    }

    /**
     * Test normalized tag list with custom glue
     *
     * @test
     */
    public function testCustomNormalizerNormalized()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertArrayValuesAreEqual(['elppA', 'ananaB', 'yrrehC'], $this->testModel->tagArrayNormalized);
    }
}
