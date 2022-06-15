<?php namespace Core;

use DateTime;
use Exception;
use Throwable;
use ReflectionClass;

use Interfaces\Controller;
use Interfaces\ApplicationRequest;

use Core\Router;
use Core\Display;

use Core\Hook\HookProvider;
use Core\Log\ApplicationLogger;

use Models\User;
use Models\Module;
use Models\Session;

use Services\HelperService;
use Services\ArrayService;
use Services\ApplicationService;

class Application
{
    const WEB_ENVIRONMENT = 1;
    const CLI_ENVIRONMENT = 2;

    private Router  $router;
    private Display $display;
    private FileLog $logger;

    public function __construct(Router $router, Display $display) 
    {
        $this->router = $router;
        $this->display = $display;

        ApplicationLogger::notice('Application constructed');
    }

    public function initModules(): bool
    {
        if ( _ENABLE_MODULES_ ) {
            ApplicationLogger::notice('Modules initialization');

            if ( _APP_ENVIRONMENT_ === Application::WEB_ENVIRONMENT ) {
                $modulesDataCacheKey = 'modules.web';
                $modulesDataEnvironment = 'web';
            } else {
                $modulesDataCacheKey = 'modules.cli';
                $modulesDataEnvironment = 'cli';
            }

            $modulesDataCache = new Cache($modulesDataCacheKey, 3600, Cache::MEM);

            $modulesData = $modulesDataCache->getData();

            if ( $modulesData === false ) {
                $modulesData = Module::where("environment IN ('any', '$modulesDataEnvironment')");
                $modulesDataCache->setData($modulesData);
            }

            foreach ( $modulesData as $moduleData ) {
                $this->registerModule($moduleData);
            }

            ApplicationLogger::notice('Modules initialization ended' . PHP_EOL);

            return true;
        }

        ApplicationLogger::notice('Modules disabled. Skipping initialization');

        return false;
    }

    private function registerModule(array $moduleData): void
    {
        $moduleModel = new Module;
        $moduleModel->setModelData($moduleData);

        if ( $moduleModel->isValidModel() === false ) {
            ApplicationLogger::error("Module '{$moduleData['name']}' invalid model");

            throw new Exception("Invalid module model '{$moduleData['name']}'");
        }

        $moduleClass = "\\Modules\\{$moduleModel->name}\\{$moduleModel->name}";
        $moduleReflector = new ReflectionClass($moduleClass);

        $moduleInstance = $moduleReflector->newInstanceArgs([ $moduleModel ]);

        if ( $moduleInstance->isEnabled() ) {
            $moduleInstance->init();
            $moduleHooks = $moduleInstance->registerHooks();

            if ( $moduleHooks->length() ) {
                foreach ( $moduleHooks as $action ) {
                    HookProvider::register($action);
                }
            }

            ApplicationLogger::notice('Module ' . $moduleInstance->getName() . ' inited');
        } else {
            ApplicationLogger::notice('Module ' . $moduleInstance->getName() . ' disabled');
        }

        ModulesRegistry::setModule($moduleInstance->getName(), $moduleInstance);
    }

    public function run(ApplicationRequest $request): void
    {
        try {
            $this->initCurrentUser($request);
            $this->initCurrentController($request);

            $context = Context::getInstance();

            $controller = $context->controller;

            $controller->init();
            $controller->execute($request->getRequestData());
            
            $view = $controller->getView();
        } catch ( Throwable $e ) {
            ApplicationLogger::warning('Application throws an error controller');
            ApplicationLogger::error('Error: ' . $e->getMessage());

            if ( _APP_ENVIRONMENT_ === Application::WEB_ENVIRONMENT ) {
                $controller = new \Web\Controller\Error(500, $e->getMessage(), $e->getTraceAsString());
            } else {
                $controller = new \Cli\Controller\Error(500, $e->getMessage(), $e->getTraceAsString());
            }
            
            $controller->init();
            $controller->execute();
            
            $view = $controller->getView();
        } 

        $view->display();

        $this->display->echo();
    }

    private function initCurrentController(ApplicationRequest $request): void
    {
        if ( _APP_ENVIRONMENT_ === Application::WEB_ENVIRONMENT ) {
            $route = $this->router->getRoute($request->getRequestData()['url']);

            if ( $route ) {
                $routeParams = $route->getParams();

                if ( isset($routeParams['redirect']) ) {
                    $routeRedirect = $routeParams['redirect'];

                    $redirectCode = isset($routeRedirect['code']) ? (int)$routeRedirect['code'] : 303;
                    $redirectTarget = isset($routeRedirect['target']) ? (string)$routeRedirect['target'] : '/';

                    $controller = new \Web\Controller\Redirect($redirectCode, $redirectTarget);
                } else if ( ArrayService::inArray($routeParams, $request->getMethod()) === false ) {
                    $controller = new \Web\Controller\Error(405, 'Request method is not allowed');
                } else if ( $route->isOnlyAuthorized() && Context::getInstance()->user->isGuest() === true ) {
                    $controller = new \Web\Controller\Error(401, 'Non authorized');
                }
            } else {
                $controller = new \Web\Controller\Error(404, 'Route not found');
            }
        } else {
            $route = $this->router->getRoute($request->getRequestData()['action']);

            if ( HelperService::isNull($route) ) {
                $controller = new \Cli\Controller\Error(1, 'Route not found');
            }
        }

        if ( isset($controller) === false && $route ) {
            $controllerClass = $route->getController();
            $controller = new $controllerClass();
        }

        if ( ApplicationService::isController($controller) === false ) {
            throw new Exception("Current route's controller must to extend \\Core\\Controller");
        }

        Context::getInstance()->controller = $controller;
    }

    public function initCurrentUser(ApplicationRequest $request): void
    {
        if ( _APP_ENVIRONMENT_ === Application::WEB_ENVIRONMENT ) {
            $cookies = $request->getRequestData()['cookies'];

            foreach ( $cookies as $cookie ) {
                if ( $cookie->getName() === 'stoken' ) {
                    $session = Session::getByToken($cookie->getValue());

                    break;
                }
            }

            if ( isset($session) && $session->isExpired() === false ) {
                $user = new User($session->id);
            } else {
                $user = User::getGuestUser();
            }

            Context::getInstance()->user = $user;
        } else {
            Context::getInstance()->user = new User(2);
        }
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
