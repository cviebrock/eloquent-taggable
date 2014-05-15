<?php

use Orchestra\Testbench\TestCase;


class TaggableTest extends TestCase {

  /**
   * Setup the test environment.
   */
	public function setUp()
	{
		parent::setUp();

		// Create an artisan object for calling migrations
		$artisan = $this->app->make('artisan');

		// Call migrations specific to our tests, e.g. to seed the db
		$artisan->call('migrate', array(
			'--database' => 'testbench',
			'--path'     => '../tests/database/migrations',
		));

		// Call migrations for the package
		$artisan->call('migrate', array(
			'--database' => 'testbench',
			'--path'     => '../src/migrations',
		));


	}


  /**
   * Define environment setup.
   *
   * @param  Illuminate\Foundation\Application    $app
   * @return void
   */
	protected function getEnvironmentSetUp($app)
	{
		// reset base path to point to our package's src directory
		$app['path.base'] = __DIR__ . '/../src';

		// set up database configuration
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', array(
				'driver'   => 'sqlite',
				'database' => ':memory:',
				'prefix'   => '',
		));

	}


  /**
   * Get Sluggable package providers.
   *
   * @return array
   */
	protected function getPackageProviders()
	{
		return array('Cviebrock\EloquentTaggable\TaggableServiceProvider');
	}


	protected function makePost()
	{
		return Post::create(array(
			'title' => \Str::random(10)
		));
	}

}
