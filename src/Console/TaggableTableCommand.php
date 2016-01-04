<?php namespace Cviebrock\EloquentTaggable\Console;

use Illuminate\Database\Console\Migrations\BaseCommand;

class TaggableTableCommand extends BaseCommand
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
    protected $description = 'Create a migration for the taggable database tables';

    /**
     * @var TaggableMigrationCreator
     */
    protected $creator;

    /**
     * Create a new taggable table command instance.
     *
     * @param TaggableMigrationCreator $creator
     */
    public function __construct(TaggableMigrationCreator $creator)
    {
        parent::__construct();

        $this->creator = $creator;
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $this->writeMigration('create_taggable_tables');

        $this->line('<info>Don\'t forget to run</info> composer dump-autoload <info>to register the migration.</info>');
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string $name
     * @return string
     */
    protected function writeMigration($name)
    {
        $path = $this->getMigrationPath();

        $file = pathinfo($this->creator->create($name, $path),
            PATHINFO_FILENAME);

        $this->line("<info>Created Migration:</info> $file");
    }
}
