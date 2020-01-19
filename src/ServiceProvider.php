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
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/config/taggable.php' => config_path('taggable.php'),
        ], 'config');

        if (!class_exists('CreateTaggableTable')) {
            $this->publishes([
                __DIR__.'/../resources/database/migrations/create_taggable_table.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_create_taggable_table.php'),
            ], 'migrations');
        }

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
