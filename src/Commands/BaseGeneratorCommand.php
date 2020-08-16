<?php
/**
 * Base for all generator commands.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Commands;

use Illuminate\Support\{
    Str, Carbon
};
use Illuminate\Console\GeneratorCommand;

abstract class BaseGeneratorCommand extends GeneratorCommand
{
    /**
     * Build the class with the given name.
     *
     * @param  mixed $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $stub = $this->replaceDate($stub, (string) Carbon::now());
        $stub = $this->replaceModelClass($stub, $this->guessModelClass($this->getNameInput()));

        return $stub;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string $stub
     * @param  string $date
     * @return string
     */
    protected function replaceDate(string $stub, string $date)
    {
        return \str_replace('DummyDate', $date, $stub);
    }

    /**
     * Return model class, guessed from the name.
     *
     * @param string $name
     * @return string
     */
    protected function guessModelClass(string $name)
    {
        if (Str::endsWith($name, $this->type)) {
            $name = Str::replaceLast($this->type, '', $name);
        }

        return '\\'.config('metas.models_namespace', 'App').'\\'.$name;
    }

    /**
     * Replace the model class name for the given stub.
     *
     * @param  string $stub
     * @param  string $modelClass
     * @return string
     */
    protected function replaceModelClass(string $stub, string $modelClass)
    {
        return \str_replace('DummyModelClass', $modelClass, $stub);
    }
}
