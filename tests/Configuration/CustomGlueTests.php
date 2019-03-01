<?php namespace Cviebrock\EloquentTaggable\Test\Configuration;

use Cviebrock\EloquentTaggable\Test\TestCase;
use Cviebrock\EloquentTaggable\Test\TestModel;


/**
 * Class CustomGlueTests
 */
class CustomGlueTests extends TestCase
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

        $app['config']->set('taggable.glue', '.');
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
     * Test tag list with custom glue.
     */
    public function testCustomGlue()
    {
        $this->assertEquals(
            'Apple.Banana.Cherry',
            $this->testModel->getTagListAttribute()
        );
    }

    /**
     * Test normalized tag list with custom glue.
     */
    public function testCustomGlueNormalized()
    {
        $this->assertEquals(
            'apple.banana.cherry',
            $this->testModel->getTagListNormalizedAttribute()
        );
    }
}
