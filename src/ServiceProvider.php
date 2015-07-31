<?php

namespace Cviebrock\EloquentTaggable;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

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
		$app = $this->app;

		if (version_compare($app::VERSION, '5.0') < 0) {
			// Laravel 4
			$this->package('cviebrock/eloquent-taggable', 'taggable', realpath(__DIR__));
		} else {
			// Laravel 5
			$configPath = realpath(__DIR__.'/config/config.php');
			$this->publishes([
				$configPath => config_path('taggable.php'),
			]);
			$this->mergeConfigFrom($configPath, 'taggable');
		}
	}

	/**
	 * Register the service provider.
	 */
	public function register()
	{
		$this->app->singleton('taggable.command.table', function ($app) {
			return new Console\TaggableTableCommand($app['files'], $app['composer']);
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
