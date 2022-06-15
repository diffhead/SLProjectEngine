<?php namespace Models;

use Interfaces\User as UserInterface;

use Core\Cache;
use Core\Model;
use Core\ActiveRecord;

use Core\Database\Db;
use Core\Database\Query;

use Services\ArrayService;
use Services\CryptService;

class User extends Model implements UserInterface
{
    private Access  $access;
    private Session $session;

    protected string $name;
    protected string $email;
    protected string $login;
    protected string $phone;
    protected bool   $active;
    protected string $date_add;
    protected string $date_upd;
    protected string $password;
    protected string $thirdname;
    protected string $secondname;
    protected string $last_login;

    protected static string $idField = 'id';
    protected static string $table = 'users';

    protected static bool   $dataCaching = true;

    protected static array  $definitions = [
        'id'         => ActiveRecord::TYPE_INT,
        'active'     => ActiveRecord::TYPE_BOOL,
        'name'       => ActiveRecord::TYPE_STRING,
        'email'      => ActiveRecord::TYPE_STRING,
        'login'      => ActiveRecord::TYPE_STRING,
        'phone'      => ActiveRecord::TYPE_STRING,
        'date_add'   => ActiveRecord::TYPE_STRING,
        'date_upd'   => ActiveRecord::TYPE_STRING,
        'password'   => ActiveRecord::TYPE_STRING,
        'thirdname'  => ActiveRecord::TYPE_STRING,
        'last_login' => ActiveRecord::TYPE_STRING,
        'secondname' => ActiveRecord::TYPE_STRING
    ];

    public static function getGuestUser(): User
    {
        $cache = new Cache('Model.User.Guest', 3600 * 24 * 7);

        $userData = $cache->getData();

        if ( $userData === false ) {
            $query = new Query;
            $query->select([ 'u.*' ])
                  ->from('users', 'u')
                  ->join('user_access', 'ua', 'left', 'u.id = ua.user_id')
                  ->join('access', 'a', 'left', 'ua.access = a.id')
                  ->where('a.title = \'guest\'');

            $db = Db::getConnection();
            $db->execute($query);

            $userData = ArrayService::pop($db->fetch());

            $cache->setData($userData);
        }

        $user = new User();
        $user->setModelData($userData);

        return $user;
    }
    
    public static function getByLogin(string $login): User
    {
        $userData = (array)ArrayService::pop(User::where("login = '{$login}'"));
        
        $user = new User();
        $user->setModelData($userData);
        
        return $user;
    }
    
    public function isValidPassword(string $password): bool
    {
        return CryptService::decrypt($this->password) === $password;
    }

    public function isGuest(): bool
    {
        if ( $this->isValidModel() === false ) {
            return true;
        }

        if ( isset($this->access) === false ) {
            $this->initAccess();
        }

        return $this->access->title === 'guest';
    }

    private function initAccess(): void
    {
        if ( $this->isValidModel() === false || isset($this->access) ) {
            return;
        }

        $userAccess = new UserAccess($this->id);

        if ( $userAccess->isValidModel() ) {
            $this->access = new Access($userAccess->access);
        }
    }

    public function isAdmin(): bool
    {
        if ( $this->isValidModel() === false ) {
            return false;
        }

        if ( isset($this->access) === false ) {
            $this->initAccess();
        }

        return $this->access->title === 'admin';
    }

    public function isActive(): bool
    {
        if ( $this->isValidModel() === false ) {
            return false;
        }

        return $this->active;
    }

    public function getAccessId(): int
    {
        $this->initAccess();

        /* Model can to be an invalid */
        if ( isset($this->access) === false ) {
            return 0;
        }

        return $this->access->id;
    }
}
