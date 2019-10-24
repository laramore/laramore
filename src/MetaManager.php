<?php
/**
 * Regroup all Metas and prepare them.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore;

use Laramore\Traits\IsLocked;

class MetaManager
{
    use IsLocked;

    /**
     * List all managed Metas.
     *
     * @var array
     */
    protected $metas = [];

    /**
     * Indicate if a Meta exists for a specific table name.
     *
     * @param  string $tableName
     * @return boolean
     */
    public function hasForTableName(string $tableName): bool
    {
        foreach ($this->all() as $meta) {
            if ($meta->getTableName() === $tableName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the Meta for a specific table name.
     *
     * @param  string $tableName
     * @return Meta
     * @throws \ErrorException If no meta with the given table name exists.
     */
    public function getForTableName(string $tableName): Meta
    {
        foreach ($this->all() as $meta) {
            if ($meta->getTableName() === $tableName) {
                return $meta;
            }
        }

        throw new \ErrorException('No meta exists for the table '.$tableName);
    }

    /**
     * Indicate if a meta exists a specific model.
     *
     * @param  string $modelClass
     * @return boolean
     */
    public function has(string $modelClass): bool
    {
        return isset($this->metas[$modelClass]);
    }

    /**
     * Indicate if a meta exists a specific model.
     *
     * @param  string $modelClass
     * @return boolean
     */
    public function get(string $modelClass): Meta
    {
        return $this->metas[$modelClass];
    }

    /**
     * Return the meta for a specific model.
     *
     * @param  string $modelClass
     * @return boolean
     */
    public function all(): array
    {
        return $this->metas;
    }

    /**
     * Return all Metas, indexed by their table name.
     *
     * @return array
     */
    public function allWithTableNames(): array
    {
        $metas = [];

        foreach ($this->all() as $meta) {
            $metas[$meta->getTableName()] = $meta;
        }

        return $metas;
    }

    /**
     * Add a meta.
     *
     * @param Meta $meta
     * @return self
     * @throws \LogicException If the meta already exists or one already exists for a the same table name.
     */
    public function add(Meta $meta)
    {
        $this->needsToBeUnlocked();

        $tableName = $meta->getTableName();

        foreach ($this->all() as $modelClass => $inMeta) {
            if ($meta === $inMeta) {
                throw new \LogicException('This meta is already added');
            } else if ($inMeta->getTableName() === $tableName) {
                throw new \LogicException('A meta already exists for this table');
            } else if ($modelClass === $meta->getModelClass()) {
                throw new \LogicException('A meta already exists for this model');
            }
        }

        $this->metas[$meta->getModelClass()] = $meta;

        return $this;
    }

    /**
     * Lock all metas and checks that everything is locked as expected.
     *
     * @return void
     * @throws \LogicException If an object is not locked properly.
     */
    public function locking()
    {
        foreach ($this->all() as $meta) {
            $meta->lock();
        }

        foreach ($this->all() as $meta) {
            if (!$meta->isLocked()) {
                throw new \LogicException('All metas are not locked properly');
            }

            foreach ($meta->all() as $field) {
                if (!$field->isLocked()) {
                    throw new \LogicException('All fields are not locked by their owner');
                }
            }
        }
    }
}
