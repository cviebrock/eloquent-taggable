<?php namespace Cviebrock\EloquentTaggable\Test;

/**
 * Class TaggableTest
 */
class TaggableTest extends TestCase
{

    public function testTagging()
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        $this->assertEquals(3, count($this->testModel->tags));
    }
}
