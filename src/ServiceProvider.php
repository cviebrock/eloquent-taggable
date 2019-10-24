<?php namespace Cviebrock\EloquentTaggable;

use Cviebrock\EloquentTaggable\Services\TagService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
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
    public function boot(Filesystem $filesystem)
    {
        $this->publishes([
            __DIR__ . '/../resources/config/taggable.php' => config_path('taggable.php'),
        ], 'config');


        $this->publishes([
            __DIR__.'/../resources/database/migrations/0000_00_00_000000_create_taggable_table.php' => $this->getMigrationFileName($filesystem)
        ], 'eloquent-taggable-migrations');

        $package_migration = glob($this->app->databasePath()."/migrations/*_create_taggable_table.php");

        if (count($package_migration) > 0) {
            $this->loadMigrationsFrom(database_path('migrations'));
        } else {
            $this->loadMigrationsFrom(
                __DIR__.'/../resources/database/migrations'
            );
        }
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

    /**
     * @param Filesystem $filesystem
     * @return mixed
     */
    private function getMigrationFileName(Filesystem $filesystem)
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
               return $filesystem->glob($path.'*_create_taggable_table.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_create_taggable_table.php")
            ->first();
    }
}
