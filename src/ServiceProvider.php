<?php namespace Cviebrock\EloquentTaggable;


use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishConfigs();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__ . '/../config/taggable.php', 'taggable'
		);
	}

	/**
	 * Publish the configuration file.
	 */
	private function publishConfigs()
	{
		$configPath = __DIR__ . '/../config/taggable.php';
		$this->publishes([
			$configPath => config_path('taggable.php')
		], 'config');
	}


	public function registerCommands()
	{
		$this->app['taggable.migrate'] = $this->app->share(function($app)
		{
			return new MigrationCommand;
		});
		$this->commands('taggable.migrate');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}
