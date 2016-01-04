<?php namespace Cviebrock\EloquentTaggable\Console;

use Illuminate\Database\Migrations\MigrationCreator;

/**
 * Class TaggableMigrationCreator
 *
 * @package Cviebrock\EloquentSluggable
 */
class TaggableMigrationCreator extends MigrationCreator
{

    /**
     * Get the path to the stubs folder
     *
     * @return string
     */
    public function getStubPath()
    {
        return __DIR__ . '/../../stubs';
    }

    /**
     * Get the migration stub file.
     *
     * @param  string $table
     * @param  bool $create
     * @return string
     */
    protected function getStub($table, $create)
    {
        return $this->files->get($this->getStubPath() . '/migration.php');
    }

    /**
     * @param string $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }
}
