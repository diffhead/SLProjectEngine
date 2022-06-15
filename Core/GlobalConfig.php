<?php namespace Core;

use Interfaces\Configuration;

use Core\Database\Db;
use Core\Database\Query;

use Services\ArrayService;
use Services\StringService;

class GlobalConfig implements Configuration
{
    public static function get(string $code): mixed
    {
        $query = new Query;
        $query->select([ 'value' ])->from('config')->where("key = '{$code}'");

        $db = Db::getConnection();

        if ( $db->execute($query) ) {
            if ( empty($fetched = $db->fetch()) ) {
                return false;
            }

            return ArrayService::pop($fetched)['value'];
        }

        return false;
    }

    public static function set(string $code, mixed $value): bool
    {
        if ( StringService::isString($value) === false ) {
            throw new Exception("GlobalConfig setter can only using string values");
        }

        $query = new Query;
        $query->select([ 'value' ])->from('config')->where("key = '$code'");

        $db = Db::getConnection();
        $db->execute($query);

        if ( ArrayService::isEmpty($db->fetch()) === false ) {
            $query = new Query;
            $query->update('config')->set($code, $value)->where("key = '$code'");

            return $db->execute($query);
        }

        $query = new Query;

        $query->insert('config', [ 'key', 'value' ])
              ->values([ 
                  [ $code, $value ] 
              ]);

        return $db->execute($query);
    }
}
