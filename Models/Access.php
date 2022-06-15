<?php namespace Models;

use Core\Model;
use Core\ActiveRecord;

class Access extends Model
{
    protected string $title;
    protected string $description;

    protected static string $idField = 'id';
    protected static string $table = 'access';

    protected static bool   $dataCaching = true;

    protected static array  $definitions = [
        'id'          => ActiveRecord::TYPE_INT,
        'title'       => ActiveRecord::TYPE_STRING,
        'description' => ActiveRecord::TYPE_STRING
    ];
}
