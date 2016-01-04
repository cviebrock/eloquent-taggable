<?php namespace Cviebrock\EloquentTaggable;

use Cviebrock\EloquentTaggable\Console\TaggableMigrationCreator;
use Cviebrock\EloquentTaggable\Console\TaggableTableCommand;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

/**
 * Class ServiceProvider
 * @package Cviebrock\EloquentTaggable
 */
class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->handleConfigs();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCreator();
        $this->registerCommands();
    }

    /**
     * Register the configuration.
     *
     * @return void
     */
    protected function handleConfigs()
    {
        $configPath = realpath(__DIR__ . '/../config/taggable.php');
        $this->publishes([$configPath => config_path('taggable.php')]);
        $this->mergeConfigFrom($configPath, 'taggable');
    }

    /**
     * Register the migration creator.
     *
     * @return void
     */
    protected function registerCreator()
    {
        $this->app->singleton('taggable.creator', function ($app) {
            return new TaggableMigrationCreator($app['files']);
        });
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->app->singleton('taggable.command.table', function ($app) {
            return new TaggableTableCommand($app['taggable.creator']);
        });

        $this->commands('taggable.command.table');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['taggable.command.table'];
    }
}
