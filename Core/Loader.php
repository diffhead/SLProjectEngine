<?php namespace Core;

use Core\Application;
use Core\Log\ApplicationLogger;

use Services\DependencyInjectionService;
use Services\ApplicationService;

use Config\ApplicationConfig;
use Config\DatabaseConfig;
use Config\MemcachedConfig;
use Config\CryptServiceConfig;

class Loader 
{
    public static function loadClass(string $className): void
    {
        $className = str_replace('\\', '/', $className);
        $classPath = _APP_BASE_DIR_ . $className . _PHP_EXTENSION_;

        if ( file_exists($classPath) ) {
            require_once($classPath);
        }
    }

    public function __construct() 
    {
        $this->initSplLoader();
        $this->initConstants();
        $this->initContext();
        $this->initConfigs();
        $this->initIniSettings();
        $this->initVendor();
    }

    public function bootstrap(): void
    {
        $context = Context::getInstance();

        $context->application->initModules();
        $context->application->run($context->applicationRequest);
    }

    private function initSplLoader(): void
    {
        spl_autoload_register('\\Core\\Loader::loadClass');
    }

    private function initConstants(): void
    {
        define('_PHP_EXTENSION_', '.php');
        define('_APP_EMPTY_STRING_', '');

        if ( php_sapi_name() === 'cli' ) {
            define('_APP_BASE_DIR_', getenv('PWD') . '/');
            define('_APP_ENVIRONMENT_', Application::CLI_ENVIRONMENT);
        } else {
            define('_APP_BASE_DIR_', $_SERVER['DOCUMENT_ROOT'] . '/');
            define('_APP_ENVIRONMENT_', Application::WEB_ENVIRONMENT);
        }

        define('_PHP_INPUT_MAX_LENGTH_', 32 * 1024 * 1024);
    }

    private function initContext(): void
    {
        $diContainer = DependencyInjectionService::getContainer('context');

        Context::setInstance(
            $diContainer->get(_APP_ENVIRONMENT_)
        );
    }

    private function initConfigs(): void
    {
        $configJson = ApplicationService::getGlobalConfigJson();

        ApplicationConfig::setFields([
            'host'      => (string)$configJson['host'],
            'port'      => (int)$configJson['port'],
            'dev'       => (bool)$configJson['dev'],
            'modules'   => (bool)$configJson['modules'],
            'cacheDir' => (string)$configJson['cacheDir']
        ]);

        DatabaseConfig::setFields([
            'driver'   => (string)$configJson['storage']['database']['driver'],
            'host'     => (string)$configJson['storage']['database']['host'],
            'port'     => (string)$configJson['storage']['database']['port'],
            'username' => (string)$configJson['storage']['database']['username'],
            'password' => (string)$configJson['storage']['database']['password'],
            'database' => (string)$configJson['storage']['database']['database']
        ]);

        MemcachedConfig::setFields([
            'enabled' => (bool)$configJson['storage']['memcached']['enabled'],
            'servers' => (array)$configJson['storage']['memcached']['servers']
        ]);

        CryptServiceConfig::setFields([
            'passphrase' => (string)$configJson['cryptService']['passphrase'],
            'algorythm'  => (string)$configJson['cryptService']['algorythm'],
            'initvector' => (string)$configJson['cryptService']['initvector']
        ]);

        define('_ENABLE_MODULES_', ApplicationConfig::get('modules'));
        define('_DEV_MODE_', ApplicationConfig::get('dev'));
        define('_CACHE_DIR_', _APP_BASE_DIR_ . ApplicationConfig::get('cacheDir') . '/');
        define('_MODULES_DIR_', _APP_BASE_DIR_ . 'Modules/');
    }

    private function initIniSettings(): void
    {
        $iniSettings = [
            'memory_limit'           => '64M',
            'allow_url_fopen'        => 1,
            'error_log'              => _APP_BASE_DIR_ . '/log/nginx/error_' . date('dmY') . '.log',
            'error_reporting'        => _DEV_MODE_ ? E_ALL : E_STRICT,
            'display_errors'         => _DEV_MODE_ ? 1 : 0,
            'display_startup_errors' => _DEV_MODE_ ? 1 : 0
        ];

        foreach ( $iniSettings as $option => $value ) {
            $ini = new Ini($option);

            if ( $ini->setValue((string)$value) === false ) {
                ApplicationLogger::warning('Ini option: ' . $option . ' wasnt set to value - ' . $value);
            }
        }
    }

    private function initVendor(): void
    {
        require_once(_APP_BASE_DIR_ . 'vendor/autoload.php');
    }
}
