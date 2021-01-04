<?php namespace Cviebrock\EloquentTaggable\Test;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;


/**
 * Class TestServiceProvider
 */
class TestServiceProvider extends LaravelServiceProvider
{

    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
