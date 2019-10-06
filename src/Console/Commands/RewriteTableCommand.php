<?php

namespace Viviniko\Rewrite\Console\Commands;

use Viviniko\Support\Console\CreateMigrationCommand;

class RewriteTableCommand extends CreateMigrationCommand
{
    /**
     * @var string
     */
    protected $name = 'rewrite:table';

    /**
     * @var string
     */
    protected $description = 'Create a migration for the url rewrite service table';

    /**
     * @var string
     */
    protected $stub = __DIR__ . '/stubs/rewrite.stub';

    /**
     * @var string
     */
    protected $migration = 'create_rewrite_table';
}
