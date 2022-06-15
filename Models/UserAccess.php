<?php namespace Models;

use Core\Model;
use Core\ActiveRecord;

class UserAccess extends Model
{
    protected int $access;

    protected static string $idField = 'user_id';
    protected static string $table = 'user_access';

    protected static bool   $dataCaching = true;

    protected static array  $definitions = [
        'user_id' => ActiveRecord::TYPE_INT,
        'access'  => ActiveRecord::TYPE_INT
    ];
}
