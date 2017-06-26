<?php namespace Cviebrock\EloquentTaggable;

use Cviebrock\EloquentTaggable\Services\TagService;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;


/**
 * Class ServiceProvider
 *
 * @package Cviebrock\EloquentTaggable
 */
class ServiceProvider extends LaravelServiceProvider
{

    /**
     * @inheritdoc
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../resources/config/taggable.php' => config_path('taggable.php'),
        ], 'config');

        $this->loadMigrationsFrom(
            __DIR__.'/../resources/database/migrations'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../resources/config/taggable.php', 'taggable');

        $this->app->singleton(TagService::class);
    }
}
