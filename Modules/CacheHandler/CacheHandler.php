<?php namespace Modules\CacheHandler;

use Core\AbstractModule;

use Core\Router\Route;
use Core\Router\RouteCollection;

use Core\Hook\Hook;
use Core\Hook\HookCollection;

use Core\Database\Db;
use Core\Database\Query;

use Core\Path\Directory;

use Lib\Memcached;

class CacheHandler extends AbstractModule
{
    public function registerRoutes(): RouteCollection
    {
        return new RouteCollection([
            new Route('cache-flush', Route::TYPE_RAW, '\\Modules\\CacheHandler\\Controller\\Flush', [], true, true)
        ]);
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
