<?php

namespace Cviebrock\EloquentTaggable\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Composer;
use Illuminate\Filesystem\Filesystem;

class TaggableTableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'taggable:table';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a migration for the taggable database table';

	/**
	 * The filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * @var \Illuminate\Foundation\Composer
	 */
	protected $composer;

	/**
	 * Create a new taggable table command instance.
	 *
	 * @param \Illuminate\Filesystem\Filesystem $files
	 * @param \Illuminate\Foundation\Composer   $composer
	 */
	public function __construct(Filesystem $files, Composer $composer)
	{
		parent::__construct();

		$this->files = $files;
		$this->composer = $composer;
	}

	/**
	 * Execute the console command.
	 */
	public function fire()
	{
		$fullPath = $this->createBaseMigration();

		$this->files->put($fullPath, $this->files->get(__DIR__.'/stubs/database.stub'));

		$this->info('Migration created successfully!  Don\'t forget to run "artisan migrate".');

		$this->composer->dumpAutoloads();
	}

	/**
	 * Create a base migration file for the session.
	 *
	 * @return string
	 */
	protected function createBaseMigration()
	{
		$name = 'create_taggable_table';

		$path = $this->laravel->databasePath().'/migrations';

		return $this->laravel['migration.creator']->create($name, $path);
	}
}
