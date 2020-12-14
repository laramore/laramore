<?php
/**
 * Regroup all Metas and prepare them.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2019
 * @license MIT
 */

namespace Laramore\Eloquent;

use Laramore\Contracts\{
    Prepared, Manager\LaramoreManager, Eloquent\LaramoreModel
};
use Laramore\Traits\{
    IsLocked, IsPrepared
};
use ReflectionNamespace, ReflectionClass;

class MetaManager implements Prepared, LaramoreManager
{
    use IsLocked, IsPrepared;

    /**
     * List all managed Metas.
     *
     * @var array
     */
    protected $metas = [];

    /**
     * Meta class.
     *
     * @var string
     */
    protected static $metaClass = Meta::class;

    /**
     * Pivot meta class.
     *
     * @var string
     */
    protected static $pivotMetaClass = PivotMeta::class;

    /**
     * Define all models.
     */
    public function __construct()
    {
        $modelClasses = (new ReflectionNamespace(config('app.models_namespace', 'App\\Models')))->getClassNames();
        $modelClasses = \array_filter($modelClasses, function ($class) {
            return \is_subclass_of($class, BaseModel::class) && !(new ReflectionClass($class))->isAbstract();
        });

        $pivotClasses = (new ReflectionNamespace(config('app.pivots_namespace', 'App\\Pivots')))->getClassNames();
        $pivotClasses = \array_filter($pivotClasses, function ($class) {
            return \is_subclass_of($class, BasePivot::class) && !(new ReflectionClass($class))->isAbstract();
        });

        $this->metas = \array_fill_keys(
            \array_merge($modelClasses, $pivotClasses), null
        );
    }

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
     * @return Meta
     */
    public function get(string $modelClass): Meta
    {
        if ($this->isPreparing()) {
            if (!isset($this->metas[$modelClass]) || \is_null($this->metas[$modelClass])) {
                return $this->prepareMeta($modelClass);
            }
        }

        return $this->metas[$modelClass];
    }

    /**
     * Define meta used.
     *
     * @param  Meta $meta
     * @return boolean
     */
    public function set(Meta $meta)
    {
        $this->needsToBeUnlocked();

        return $this->metas[$meta->getModelClass()] = $meta;
    }

    /**
     * Return all metas.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->metas;
    }

    /**
     * Prepare all metas.
     *
     * @return void
     */
    protected function preparing()
    {
        foreach (\array_keys($this->metas) as $modelClass) {
            if (!$this->has($modelClass)) {
                $this->prepareMeta($modelClass);
            }
        }
    }

    /**
     * Set all metas as prepared.
     *
     * @return void
     */
    protected function prepared()
    {
        foreach ($this->metas as $meta) {
            $meta->setPrepared();
        }
    }

    /**
     * Prepare a new meta class.
     *
     * @param string $modelClass
     * @return Meta
     */
    protected function prepareMeta(string $modelClass): Meta
    {
        $this->needsToBePreparing();

        if (!\is_subclass_of($modelClass, LaramoreModel::class)) {
            throw new \LogicException("Cannot create a meta from a non LaramoreModel. `$modelClass` given.");
        }

        $this->metas[$modelClass] = $meta = \is_subclass_of($modelClass, BasePivot::class) ? new PivotMeta($modelClass) : new Meta($modelClass);

        $modelClass::prepareMeta($meta);

        return $meta;
    }

    /**
     * Lock all metas and checks that everything is locked as expected.
     *
     * @return void
     * @throws \LogicException If an object is not locked properly.
     */
    protected function locking()
    {
        $this->needsToBePrepared();

        foreach ($this->all() as $meta) {
            $meta->lock();
        }

        foreach ($this->all() as $meta) {
            if (!$meta->isLocked()) {
                throw new \LogicException('All metas are not locked properly');
            }

            foreach ($meta->getFields() as $field) {
                if (!$field->isLocked()) {
                    throw new \LogicException('All fields are not locked by their owner');
                }
            }
        }
    }
}
