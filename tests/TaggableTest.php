<?php

use Orchestra\Testbench\TestCase;

class TaggableTest extends TestCase
{
	/**
	 * Setup the test environment.
	 */
	public function setUp()
	{
		parent::setUp();

		// Create an artisan object for calling migrations
		$artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');

		// Call migrations specific to our tests, e.g. to seed the db
		$artisan->call('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/database/migrations'),
		]);

		// Call migrations for the package
		$artisan->call('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../src/Console/stubs'),
		]);
	}

	/**
	 * Define environment setup.
	 *
	 * @param Illuminate\Foundation\Application $app
	 */
	protected function getEnvironmentSetUp($app)
	{

		// set up database configuration
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', array(
			'driver' => 'sqlite',
			'database' => ':memory:',
			'prefix' => '',
		));
	}

	/**
	 * Get Sluggable package providers.
	 *
	 * @return array
	 */
	protected function getPackageProviders($app)
	{
		return [
			'Cviebrock\EloquentTaggable\ServiceProvider',
		];
	}

	protected function makePost()
	{
		return Post::create([
			'title' => \Illuminate\Support\Str::random(10),
		]);
	}

	public function testTag1()
	{
		$post = $this->makePost();
		$post->tag('Apple,Banana,Cherry');

		dd($post->tags);
	}
}
