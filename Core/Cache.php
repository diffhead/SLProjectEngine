<?php namespace Core;

use Core\Database\Db;
use Core\Database\Query;

use Lib\Memcached;

use Services\ArrayService;
use Services\FileService;
use Services\HelperService;

class Cache
{
    const DB = 1;
    const MEM = 2;
    const FILE = 3;

    private string $key = '';
    private int    $type = Cache::MEM;
    private int    $expiration;

    public function __construct(string $key, int $expiration = 3600, int $type = Cache::MEM)
    {
        $this->key = $key;
        $this->type = $type;
        $this->expiration = $expiration;
    }

    public function getData(): mixed
    {
        switch ( $this->type ) {
            case Cache::MEM:
                return (new Memcached)->get($this->key);

            case Cache::DB:
                $query = new Query;
                $query->select([ 'value' ])
                      ->from('cache')
                      ->where("key = '{$this->key}'")
                      ->and("expiration + extract(epoch FROM date_add) > extract(epoch FROM NOW())");

                $db = Db::getConnection();
                $db->execute($query);

                if ( $cacheData = $db->fetch() ) {
                    return HelperService::unserialize(
                        ArrayService::pop($cacheData)['value']
                    );
                }

                return false;

            case Cache::FILE:
                $cacheFile = new FileStream(_CACHE_DIR_ . 'file/' . $this->key);
                $currentTime = time();
                $fileChangedTime = FileService::getFileChangedTime($cacheFile->getFilePath());
                $fileExpired = $fileChangedTime === false || $fileChangedTime + $this->expiration < $currentTime;

                if ( $fileExpired || $cacheFile->open() === false ) {
                    return false;
                }

                return HelperService::unserialize($cacheFile->read());
        }
    }

    public function setData(mixed $value): bool
    {
        switch ( $this->type ) {
            case Cache::MEM:
                return (new Memcached)->set($this->key, $value, $this->expiration);

            case Cache::DB:
                $value = HelperService::serialize($value);

                $query = new Query;
                $query->select()->from('cache')->where("key = '{$this->key}'");

                $db = Db::getConnection();
                $db->execute($query);

                if ( ArrayService::isEmpty($db->fetch()) === false ) {
                    $query = new Query;
                    $query->delete()->from('cache')->where("key = '{$this->key}'");

                    if ( $db->execute($query) === false ) {
                        return false;
                    }
                }

                $query = new Query;
                $query->insert('cache', [ 'key', 'value', 'expiration' ])
                      ->values([ 
                          [ $this->key, $value, $this->expiration ]
                      ]);

                return $db->execute($query);

            case Cache::FILE:
                $value = HelperService::serialize($value);

                $cacheFile = new FileStream(_CACHE_DIR_ . 'file/' . $this->key, FileStream::ACCESS_RW);

                if ( $cacheFile->open() === false ) {
                    if ( $cacheFile->touch() === false ) {
                        return false;
                    }

                    $cacheFile->open();
                }

                return $cacheFile->write($value);
        }
    }
}
