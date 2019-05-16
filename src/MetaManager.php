<?php
/**
 * Regroup Metas in a simple class.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use ReflectionNamespace;
use Laramore\Traits\IsLocked;

class MetaManager
{
    use IsLocked;

    protected $metas = [];

    public function __construct(string $namespace=null)
    {
        if ($namespace) {
            $modelNamespace = new ReflectionNamespace($namespace);

            foreach ($modelNamespace->getClasses() as $modelClass) {
                if (in_array(HasLaramore::class, $modelClass->getTraitNames())) {
                    $this->addMeta($modelClass->getName()::getMeta());
                }
            }
        }
    }

    public function hasMetaFromTableName(string $tableName): bool
    {
        foreach ($this->getMetas() as $meta) {
            if ($meta->getTableName() === $tableName) {
                return true;
            }
        }

        return false;
    }

    public function getMetaFromTableName(string $tableName): Meta
    {
        foreach ($this->getMetas() as $meta) {
            if ($meta->getTableName() === $tableName) {
                return $meta;
            }
        }

        throw new \Exception('No Meta exists for the table '.$tableName);
    }

    public function getMetas()
    {
        return $this->metas;
    }

    public function getMetasWithTableNames()
    {
        $metas = [];

        foreach ($this->getMetas() as $meta) {
            $metas[$meta->getTableName()] = $meta;
        }

        return $metas;
    }

    public function addMeta(Meta $meta)
    {
        $tableName = $meta->getTableName();

        foreach ($this->getMetas() as $inMeta) {
            if ($meta === $inMeta) {
                throw new \Exception('This meta is already added');
            } else if ($inMeta->getTableName() === $tableName) {
                throw new \Exception('A meta already exists for this table');
            }
        }

        $metas[] = $meta;
    }

    public function locking()
    {
        foreach ($this->getMetas() as $meta) {
            $meta->lock();
        }

        foreach ($this->getMetas() as $meta) {
            if (!$meta->isLocked()) {
                throw new \Exception('All metas are not locked properly');
            }

            foreach ($meta->allFields() as $field) {
                if (!$field->isLocked()) {
                    throw new \Exception('All fields are not locked by their owner');
                }
            }
        }
    }
}
