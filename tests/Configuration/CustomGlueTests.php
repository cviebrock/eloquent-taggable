<?php namespace Cviebrock\EloquentTaggable\Test\Configuration;

use Cviebrock\EloquentTaggable\Test\TestCase;


/**
 * Class CustomGlueTests
 */
class CustomGlueTests extends TestCase
{

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('taggable.glue', '.');
    }

    /**
     * Test tag list with custom glue
     *
     * @test
     */
    public function testCustomGlue()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertEquals('Apple.Banana.Cherry', $this->testModel->tagList);
    }

    /**
     * Test normalized tag list with custom glue
     *
     * @test
     */
    public function testCustomGlueNormalized()
    {
        $this->testModel->tag('Apple,Banana,Cherry');
        $this->assertEquals('apple.banana.cherry', $this->testModel->tagListNormalized);
    }
}
