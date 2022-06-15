<?php namespace Core;

use ReflectionClass;

use Models\Module as ModuleModel;

use Core\Hook\HookCollection;

abstract class AbstractModule
{
    protected ModuleModel $model;

    protected string $name;
    protected string $path;
    protected string $namespace;

    abstract public function init(): void;

    final public function __construct(ModuleModel $model)
    {
        $this->model = $model;

        $this->name = $model->name;
        $this->path = _MODULES_DIR_ . $model->name . '/';

        $reflectionClass = new ReflectionClass(static::class);

        $this->namespace = $reflectionClass->getNamespaceName();
    }

    public function registerHooks(): HookCollection
    {
        return new HookCollection();
    }

    public function enable(): bool
    {
        $this->model->enable = true;

        return $this->model->update();
    }

    public function disable(): bool
    {
        $this->model->enable = false;

        return $this->model->update();
    }

    public function isEnabled(): bool
    {
        return $this->model->enable;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function getPath(): string
    {
        return $this->path;
    }
}
