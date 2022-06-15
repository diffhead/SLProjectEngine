<?php namespace Core\Hook;

class HooksRegistry
{
    private static array $_HOOKS = [];

    public static function register(Hook $action): void
    {
        $hookName = $action->getHook();

        if ( isset(self::$_HOOKS[$hookName]) === false ) {
            self::$_HOOKS[$hookName] = [];
        }

        self::$_HOOKS[$hookName][] = $action;
    }

    public static function getHooks(string $hookName): array
    {
        if ( isset(self::$_HOOKS[$hookName]) === false ) {
            return [];
        }

        return self::$_HOOKS[$hookName];
    }
}
