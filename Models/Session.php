<?php namespace Models;

use DateTime;

use Core\Model;
use Core\ActiveRecord;

use Services\ArrayService;

class Session extends Model
{
    private DateTime $expirationDatetime;

    protected string $token;
    protected string $expiration;

    protected static bool   $dataCaching = true;

    protected static string $table = 'user_session';
    protected static string $idField = 'user_id';

    protected static array  $definitions = [
        'user_id'    => ActiveRecord::TYPE_INT,
        'token'      => ActiveRecord::TYPE_STRING,
        'expiration' => ActiveRecord::TYPE_STRING
    ];

    public function setModelData(array $data = []): void
    {
        parent::setModelData($data);

        if ( $this->isValidModel() ) {
            $this->expirationDatetime = new DateTime($this->expiration);
        }
    }

    public static function getByToken(string $token): null|Session
    {
        $modelData = static::where("token = '{$token}'");

        if ( ArrayService::isEmpty($modelData) ) {
            return null;
        }

        $model = new Session();
        $model->setModelData(ArrayService::pop($modelData));

        return $model;
    }

    public function isExpired(): bool
    {
        if ( $this->isValidModel() === false ) {
            return true;
        }

        return $this->expirationDatetime < new DateTime('now');
    }

    public function getExpirationFormatted(string $format): string
    {
        if ( $this->isValidModel() === false ) {
            return _APP_EMPTY_STRING_;
        }

        return $this->expirationDatetime->format($format);
    }
}
