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
    public function hasMetaForTableName(string $tableName): bool
    {
        foreach ($this->getMetas() as $meta) {
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
     */
    public function getMetaForTableName(string $tableName): Meta
    {
        foreach ($this->getMetas() as $meta) {
            if ($meta->getTableName() === $tableName) {
                return $meta;
            }
        }

        throw new \Exception('No Meta exists for the table '.$tableName);
    }

    /**
     * Return all Metas.
     *
     * @return array
     */
    public function getMetas(): array
    {
        return $this->metas;
    }

    /**
     * Return all Metas, indexed by their table name.
     *
     * @return array
     */
    public function getMetasWithTableNames(): array
    {
        $metas = [];

        foreach ($this->getMetas() as $meta) {
            $metas[$meta->getTableName()] = $meta;
        }

        return $metas;
    }

    /**
     * Add a meta.
     *
     * @param Meta $meta
     * @return static
     */
    public function addMeta(Meta $meta)
    {
        $this->checkLock();

        $tableName = $meta->getTableName();

        foreach ($this->getMetas() as $inMeta) {
            if ($meta === $inMeta) {
                throw new \Exception('This meta is already added');
            } else if ($inMeta->getTableName() === $tableName) {
                throw new \Exception('A meta already exists for this table');
            }
        }

        $this->metas[] = $meta;

        return $this;
    }

    /**
     * Lock all metas and checks that everything is locked as expected.
     *
     * @return void
     */
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
