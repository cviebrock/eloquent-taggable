<?php namespace Cviebrock\EloquentTaggable\Test\Configuration;

use Cviebrock\EloquentTaggable\Test\TestCase;


/**
 * Class CustomDelimiterTests
 */
class CustomDelimiterTests extends TestCase
{

    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('taggable.delimiters', '/;,|');
    }

    /**
     * Test adding tags.
     */
    public function testCustomDelimiters()
    {
        $model = $this->newModel()->tag('Apple;Banana/Cherry,Durian|Etrog');

        $this->assertCount(5, $model->tags);
        $this->assertArrayValuesAreEqual(
            ['Apple', 'Banana', 'Cherry', 'Durian', 'Etrog'],
            $model->getTagArrayAttribute()
        );
    }
}
