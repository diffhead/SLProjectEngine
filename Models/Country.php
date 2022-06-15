<?php namespace Models;

use Core\Model;
use Core\ActiveRecord;

use Services\ArrayService;

class Country extends Model
{
    protected string $title;
    protected string $iso;

    protected static string $idField = 'id';
    protected static string $table = 'country';

    protected static bool   $dataCaching = true;

    protected static array  $definitions = [
        'id'    => ActiveRecord::TYPE_INT,
        'title' => ActiveRecord::TYPE_STRING,
        'iso'   => ActiveRecord::TYPE_STRING
    ];

    public static function getByISO(string $iso): array
    {
        $country = static::where("iso = '$iso'");

        if ( ArrayService::isEmpty($country) ) {
            return [];
        }

        return ArrayService::pop($country);
    }
}
