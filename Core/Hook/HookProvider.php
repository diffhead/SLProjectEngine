<?php namespace Core\Hook;

class HookProvider
{
    public static function register(Hook $action): void
    {
        HooksRegistry::register($action);
    }

    public static function execute(string $hookName, array $args = []): HookResultCollection
    {
        $collection = new HookResultCollection();
        $actions = HooksRegistry::getHooks($hookName);

        foreach ( $actions as $action ) {
            $collection->putItemIntoCollection($action->execute($args));
        }

        return $collection;
    }
}
