<?php namespace Modules\AuthModule\Controller;

use DateTime;

use Core\Controller;
use Core\Database\Db;

use Web\HttpCookie;
use Web\HttpHeader;

use Views\Json as JsonView;

use Models\User;
use Models\Session;

use Services\HttpService;
use Services\ArrayService;
use Services\CryptService;

class Login extends Controller
{
    const SUCCESS_LOGIN = 'Successfully log into account';
    
    const ERR_INVALID_DATA     = 'Invalid login data';
    const ERR_USER_INACTIVE    = 'User is inactive. Connect with an admin';
    const ERR_USER_NOT_FOUND   = 'User not found';    
    const ERR_INVALID_PASSWORD = 'Invalid user password';
    const ERR_SESSION_CREATION = 'Failed session creation';
    
    private User $user;

    public function init(): void
    {
        $this->view = new JsonView([ 'status' => false ]);
    }
    
    public function execute(array $params = []): bool
    {
        $loginData = $params['data'];
        
        $db = Db::getConnection();

        $loginData['login'] = isset($loginData['login']) ?  $db->escapeString($loginData['login']) : '';
        
        if ( 
            $this->isValidLoginData($loginData) === false ||
            $this->isUserExists($loginData)     === false
        ) {
            HttpService::setResponseCode(400);
            
            return true;
        }

        if ( $this->user->isActive() === false ) {
            HttpService::setResponseCode(401);

            $this->view->assign([
                'message' => Login::ERR_USER_INACTIVE
            ]);

            return false;
        }
        
        if ( $this->user->isValidPassword($loginData['password']) === false ) {
            HttpService::setResponseCode(401);
            
            $this->view->assign([
                'message' => Login::ERR_INVALID_PASSWORD
            ]);
            
            return false;
        }

        $dateTime  = new DateTime('now + 1 day');

        $sessionData = [
            'user_id'    => $this->user->id,
            'token'      => CryptService::encrypt(time() . $this->user->id . $this->user->name . $this->user->email),
            'expiration' => $dateTime->format('Y-m-d H:i:s')
        ];
        
        if ( $this->createSession($sessionData) === false ) {
            $this->view->assign([
                'message' => Login::ERR_SESSION_CREATION
            ]);
            
            return false;
        }
        
        $this->view->assign([
            'status'  => true,
            'message' => Login::SUCCESS_LOGIN
        ]);

        
        return true;
    }
    
    private function isValidLoginData(array $loginData): bool
    {
        if ( 
            ArrayService::isEmpty($loginData)       || 
            isset($loginData['login'])   === false  ||
            isset($loginData['password']) === false ||
            empty($loginData['login'])              ||
            empty($loginData['password'])
        ) {
            $this->view->assign([
                'message' => Login::ERR_INVALID_DATA
            ]);
            
            return false;
        }
        
        return true;
    }
    
    private function isUserExists(array $loginData): bool
    {
        $user = User::getByLogin($loginData['login']);
        
        if ( $user->isValidModel() === false ) {
            $this->view->assign([
                'message' => Login::ERR_USER_NOT_FOUND
            ]);
            
            return false;
        }
        
        $this->user = $user;

        return true;
    }
    
    private function createSession(array $sessionData): bool
    {        
        $session = new Session($this->user->id);
        
        if ( $session->isValidModel() === false ) {
            $session->setModelData($sessionData);
            
            if ( $session->create() ) {
                $sessionCookie = new HttpCookie('stoken', $session->token, time() + 3600 * 24);
            }
        } else {            
            $session->setModelData($sessionData);
            
            if ( $session->update() ) {
               $sessionCookie = new HttpCookie('stoken', $session->token, time() + 3600 * 24);
            }
        }

        if ( isset($sessionCookie) === false ) {
            return false;
        }

        HttpService::setResponseCode(200);
        HttpService::setResponseCookie($sessionCookie);

        return true;
    }
}
