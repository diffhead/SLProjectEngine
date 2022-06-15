<?php namespace Modules\CacheHandler;

use Core\AbstractModule;
use Core\RouterProvider;

use Core\Hook\Hook;
use Core\Hook\HookCollection;

use Core\Database\Db;
use Core\Database\Query;

use Core\Path\Directory;

use Lib\Memcached;

class CacheHandler extends AbstractModule
{
    public function init(): void
    {
        $routerProvider = new RouterProvider();
        $routerProvider->setRoutesFromJsonFile($this->path . 'routes.json');
    }

    public function registerHooks(): HookCollection
    {
        return new HookCollection([
            new Hook('flushCache', $this, 'hookFlushCache')
        ]);
    }

    public function hookFlushCache(): bool
    {
        $status = true;

        $memcached = new Memcached();

        $status &= $memcached->flush();

        $db = Db::getConnection();
        $query = new Query;
        $query->delete()->from('cache');

        $status &= $db->execute($query);

        $cacheDir = new Directory(_CACHE_DIR_ . 'file/');

        $status &= ($cacheDir->isExists() === false || $cacheDir->delete());
        $status &= $cacheDir->create();

        return $status;
    }
}
