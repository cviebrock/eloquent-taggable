<?php namespace Cviebrock\EloquentTaggable\Test;

/**
 * Class TaggableTest
 */
class TaggableTest extends TestCase
{

    public function testTagging()
    {
        $this->testModel->tag('Apple,Banana,Cherry');

        dd($this->testModel->tags->toArray());
    }
}
