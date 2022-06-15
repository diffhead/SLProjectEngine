<?php namespace Modules\AuthModule\Controller;

use DateTime;

use Core\Context;
use Core\Validator;
use Core\Controller;

use Core\Database\Db;

use Views\Json as JsonView;

use Models\User;
use Models\Access;
use Models\UserAccess;

use Services\ArrayService;
use Services\CryptService;
use Services\StringService;
use Services\HttpService;

class Register extends Controller
{
    const ERR_EMPTY_DATA      = 'Empty user data sent';
    const ERR_VALIDATION      = 'Fields validation failed';
    const ERR_ALREADY_EXISTS  = 'User with this login or email exists yet';
    const ERR_CREATION_FAILED = 'User creation failed';
    
    const SUCCESS_CREATION    = 'User created successfully';

    private Validator $validator;

    public function init(): void
    {
        $this->view = new JsonView([ 'status' => false ]);

        $this->validator = new Validator([
            'login' => [
                'required' => true,
                'pattern'  => '/^(?=.{5,20}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/m',

                'length'   => [
                    'from' => 5,
                    'to'   => 20 
                ]
            ],

            'password' => [
                'required' => true,
                'pattern'  => '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[a-zA-Z]).{6,}$/m',

                'length'   => [
                    'from' => 6
                ]
            ],

            'phone' => [
                'pattern' => '/^[0-9\-\+]{9,15}$/m'
            ],

            'email' => [
                'required' => true,
                'pattern'  => '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD'
            ]
        ]);
    }

    public function execute(array $params = []): bool
    {
        $userData = $params['data'];
        
        if (  
            $this->isNotEmptyDataChecking($userData)  === false ||
            $this->isFormValidChecking($userData)     === false ||
            $this->isUserNotExistsChecking($userData) === false
        ) {
            HttpService::setResponseCode(400);
            
            return false;
        }
        
        $userData = $this->prepareData($userData);

        $user = new User();
        $user->setModelData($userData);
        
        if ( $user->create() === false ) {
            HttpService::setResponseCode(500);
            
            $this->view->assign([
                'message' => Register::ERR_CREATION_FAILED
            ]);
            
            return false;
        }

        /* Default access is 'User' */
        if ( $this->setUserAccess($user, new Access(3)) === false ) {
            HttpService::setResponseCode(500);
            
            $this->view->assign([
                'message' => Register::ERR_CREATION_FAILED
            ]);

            return false;
        }

        $this->view->assign([
            'status'  => true,
            'message' => Register::SUCCESS_CREATION
        ]);
            
        return true;
    }
    
    private function isNotEmptyDataChecking(array $data): bool
    {
        if ( ArrayService::isEmpty($data) ) {
            $this->view->assign([
                'message' => Register::ERR_EMPTY_DATA
            ]);
            
            return false;
        }
        
        return true;
    }
    
    private function isFormValidChecking(array $data): bool
    {
        if ( $this->validator->validate($data) === false ) {
            $this->view->assign([
                'message' => Register::ERR_VALIDATION,
                'info'    => $this->validator->getErrors()
            ]);
            
            return false;
        }
        
        return true;
    }
    
    private function isUserNotExistsChecking(array $data): bool
    {
        $userByEmailOrLogin = User::where("email = '{$data['email']}' OR login = '{$data['login']}'");
        
        if ( ArrayService::isEmpty($userByEmailOrLogin) === false ) {
            $this->view->assign([
                'message' => Register::ERR_ALREADY_EXISTS
            ]);
            
            return false;
        }
        
        return true;
    }
    
    private function prepareData(array $data): array
    {
        $db = Db::getConnection();
        
        $date = new DateTime('now');
        $dateTimestamp = $date->format('Y-m-d H:i:s');
        
        foreach ( $data as $key => &$value ) {
            if ( $key === 'password' ) {
                $value = CryptService::encrypt($value);
            }
            
            if ( StringService::isString($value) ) {
                $value = $db->escapeString($value);
            }
        }
        
        return ArrayService::merge([
        
            'active'     => true,
            'email'      => '',
            'login'      => '',
            'phone'      => '',
            'name'       => '',
            'thirdname'  => '',
            'secondname' => '',
            'date_add'   => $dateTimestamp,
            'date_upd'   => $dateTimestamp,
            'password'   => '',
            'last_login' => $dateTimestamp
            
        ], $data);
    }

    private function setUserAccess(User $user, Access $access): bool
    {
        $userAccess = new UserAccess();
        $userAccess->setModelData([
            'user_id' => $user->id,
            'access'  => $access->id
        ]);

        return $userAccess->create();
    }
}
