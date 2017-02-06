<?php namespace Sirthxalot\Parse\Console;

use Illuminate\Console\GeneratorCommand;

/**
 * Model Make Command
 * ==================================================================================
 *
 * An artisan command that will generate the stub for an entity extending the Parse
 * driver. This allows to quickly generate new entities ready to use with your Parse
 * driver in seconds.
 *
 * @package   Sirthxalot\Parse\Console
 * @author    Alexander Bösch (<sirthxalot.dev@gmail.com>)
 * @copyright (c) Copyright 2017, Alexander Bösch - All rights reserved.
 */
class ModelMakeCommand extends GeneratorCommand
{
    /**
     * Command Name
     *
     * @var string $name
     * A string that determine the name of the console command.
     */
    protected $name = 'parse:model';

    /**
     * Command Description
     *
     * @var string $description
     * A string that determine the description used for the console command.
     */
    protected $description = 'Create a new model for Parse usage.';

    /**
     * Command Type
     *
     * @var string $type
     * A string that determine the type for the console command.
     */
    protected $type = 'ObjectModel';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/model.stub';
    }
}
