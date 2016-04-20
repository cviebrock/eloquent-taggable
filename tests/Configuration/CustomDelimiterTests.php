<?php namespace Cviebrock\EloquentTaggable\Test\Configuration;

use Cviebrock\EloquentTaggable\Test\TestCase;


/**
 * Class CustomDelimiterTests
 */
class CustomDelimiterTests extends TestCase
{

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('taggable.delimiters', '/;,|');
    }

    /**
     * Test adding tags.
     *
     * @test
     */
    public function testCustomDelimiters()
    {
        $this->testModel->tag('Apple;Banana/Cherry,Durian|Etrog');

        $this->assertEquals(5, count($this->testModel->tags));
        $this->assertArrayValuesAreEqual(['Apple', 'Banana', 'Cherry', 'Durian', 'Etrog'], $this->testModel->tagArray);
    }
}
