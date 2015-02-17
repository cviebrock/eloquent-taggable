<?php namespace Cviebrock\EloquentTaggable;

use Illuminate\Console\Command;

class MigrationCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'taggable:migrate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create migrations for the Taggable database table';

	/**
	 * The filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * List of migrations to create (matches stub names).
	 *
	 * @var array
	 */
	protected $migrations = [
		'create_taggable_tables'
	];


	public function __construct(FileSystem $files) {
		parent::__construct();
		$this->files = $files;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire() {
		foreach ($this->migrations as $migration) {

			$fullPath = $this->createMigration($migration);
			$this->files->put($fullPath, $this->getStub($migration));
		}
		$this->info('Migrations successfully created!');

		$this->call('dump-autoload');
	}

	/**
	 * Create a base migration file for the model.
	 *
	 * @return string
	 */
	protected function createMigration($migration) {
		$path = $this->laravel['path'] . '/database/migrations';

		return $this->laravel['migration.creator']->create($migration, $path);
	}


	/**
	 * Get the contents of the sluggable migration stub.
	 *
	 * @return string
	 */
	protected function getStub($migration) {
		$stub = $this->files->get($this->getStubPath() . '/0000_00_00_000000_' . $migration . '.php');

		return $stub;
	}

	/**
	 * Get the path to the stubs.
	 *
	 * @return string
	 */
	public function getStubPath() {
		return __DIR__ . '/../stubs';
	}
}
