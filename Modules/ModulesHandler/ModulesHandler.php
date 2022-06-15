<?php namespace Modules\ModulesHandler;

use Core\FileStream;
use Core\RouterProvider;
use Core\AbstractModule;

use Core\Hook\Hook;
use Core\Hook\HookCollection;

use Core\Path\File;
use Core\Path\Directory;

use Models\Module;

use Modules\ModulesHandler\Views\ModuleTemplate;

class ModulesHandler extends AbstractModule
{
    public function init(): void
    {
        $routerProvider = new RouterProvider();
        $routerProvider->setRoutesFromJsonFile($this->path . 'routes.json');
    }

    public function registerHooks(): HookCollection
    {
        return new HookCollection([ 
            new Hook('createModule', $this, 'hookCreateModule'),
            new Hook('updateModule', $this, 'hookUpdateModule'),
            new Hook('deleteModule', $this, 'hookDeleteModule')
        ]);
    }

    public function hookCreateModule(array $moduleData): ?Module
    {
        $module = new Module();
        $module->setModelData($moduleData);

        if ( $module->create() === false ) {
            return null;
        }

        $moduleDir = new Directory(_APP_BASE_DIR_ . 'Modules/' . $moduleData['name'], false);
        $moduleFile = new File($moduleDir->getPath() . '/' . $moduleData['name'] . _PHP_EXTENSION_);

        if ( $moduleDir->isExists() === false ) {
            $moduleDir->create();
        }

        if ( $moduleFile->isExists() === false ) {
            $file = new FileStream($moduleFile->getPath(), FileStream::ACCESS_RW);

            if ( $file->touch() ) {
                $file->open();
                $file->write(
                    (new ModuleTemplate($moduleData['name']))->render()
                );
            }
        }

        return $module;
    }

    public function hookUpdateModule(array $moduleData): bool
    {
        $module = new Module((int)$moduleData['id']);
        $module->setModelData($moduleData);

        return $module->update();
    }

    public function hookDeleteModule(int $moduleId): bool
    {
        $module = new Module($moduleId);

        return $module->delete();
    }
}
