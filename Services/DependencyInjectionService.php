<?php namespace Services;

use Exception;
use ReflectionClass;

use Core\FileStream;
use Core\DependencyInjection\Action;
use Core\DependencyInjection\Container;

use Factories\DependencyInjection\ContainerFactory;
use Factories\DependencyInjection\ActionFactory;

class DependencyInjectionService
{
    private static array $containers;

    private static function initContainers(): void
    {
        $containersConfigPath = _APP_BASE_DIR_ . 'containers.json';

        $containersFile = new FileStream($containersConfigPath);

        if ( $containersFile->open() === false ) {
            throw new Exception("Containers config '$containersConfigPath' reading error.");
        }

        $containersText = $containersFile->read();
        $containersJson = JsonService::decode($containersText);

        if ( JsonService::lastError() !== JSON_ERROR_NONE ) {
            throw new Exception("Containers config reading error: " . JsonService::lastError());
        }

        self::$containers = [];

        foreach ( $containersJson as $containerName => $containerData ) {
            $containerClass = $containerData['class'];
            $containerConstructorArgs = self::getContainerArgsActions($containerData['constructor-args']);

            self::$containers[$containerName] = ContainerFactory::get($containerData['class'], $containerConstructorArgs);
        }
    }

    private static function getContainerArgsActions(array $containerArgsDataByEntities): array
    {
        $argumentsByEntities = [];

        foreach ( $containerArgsDataByEntities as $entity => $arguments ) {
            foreach ( $arguments as $argument ) {
                $argumentsOfArgument = self::getArgsActionsRecursive($argument['arguments']);
                $argumentsByEntities[$entity][] = ActionFactory::get($argument['type'], $argument['value'], $argumentsOfArgument);
            }
        }

        return $argumentsByEntities;
    }

    private static function getArgsActionsRecursive($args): array
    {
        $finalArray = [];

        foreach ( $args as $arg ) {
            $actionType = $arg['type'];
            $actionValue = $arg['value'];
            $actionArguments = [];

            if ( $actionType !== 'static-value' && empty($arg['arguments']) === false ) {
                $actionArguments = self::getArgsActionsRecursive($arg['arguments']);
            }

            $finalArray[] = new Action($actionType, $actionValue, $actionArguments);
        }

        return $finalArray;
    }

    public static function getContainer(string $container): Container
    {
        if ( isset(self::$containers) === false ) {
            self::initContainers();
        }

        if ( self::isContainerExists($container) === false ) {
            throw new Exception("Container '$container' isnt exists.");
        }

        return self::$containers[$container];
    }

    private static function isContainerExists(string $container): bool
    {
        return isset(self::$containers[$container]);
    }

    public static function executeActions(array $actionsArray): array
    {
        $actionsResults = [];

        foreach ( $actionsArray as $action ) {
            if ( self::isAction($action) ) {
                $actionsResults[] = self::executeActionRecursive($action);
            }
        }

        return $actionsResults;
    }

    public static function isAction(mixed $isAction): bool
    {
        return $isAction instanceof Action;
    }

    private static function executeActionRecursive(Action $action): mixed
    {
        $actionType = $action->getType();
        $actionValue = $action->getValue();
        $actionArgs = $action->getArguments();

        $actionArgsExecuted = [];

        foreach ( $actionArgs as $argument ) {
            if ( self::isAction($argument) ) {
                $actionArgsExecuted[] = self::executeActionRecursive($argument);
            } else {
                $actionArgsExecuted[] = $argument;
            }
        }

        switch ( $actionType ) {
            case 'class':
                    $reflectionClass = new ReflectionClass($actionValue);

                    return $reflectionClass->newInstanceArgs($actionArgsExecuted);

            case 'static-value': 

                    return $actionValue;

            case 'static-method':

                    return call_user_func_array($actionValue, $actionArgsExecuted);
        }
    }
}
