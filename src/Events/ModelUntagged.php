<?php

namespace Cviebrock\EloquentTaggable\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelUntagged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private $model;
    private $tags;

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Create a new event instance.
     *
     * @param mixed $model
     * @param mixed $tags
     */
    public function __construct($model, $tags)
    {
        $this->model = $model;
        $this->tags = $tags;
    }
}
