<?php namespace Cviebrock\EloquentTaggable\Test;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;


/**
 * Class TestServiceProvider
 *
 * @package Cviebrock\EloquentTaggable
 */
class TestServiceProvider extends LaravelServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
