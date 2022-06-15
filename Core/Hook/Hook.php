<?php namespace Core\Hook;

use Exception;

use Interfaces\CollectionItem;

use Services\ClassService;
use Services\HelperService;

class Hook implements CollectionItem
{
    private string  $hook;
    private string  $sign = '';
    private string  $method;
    private ?Object $class = null;

    public function __construct(string $hookName, ?Object $class = null, string $method = '')
    {
        $this->hook = $hookName;
        $this->class = $class;
        $this->method = $method;
    }

    public function getValue(string $property): mixed
    {
        return $this->$property;
    }

    public function getUniqueId(): string
    {
        return $this->getSign();
    }

    public function execute(array $args = []): HookResult
    {
        $callable = $this->class ? [ $this->class, $this->method ] : $this->method;
        $methodExists = false;
        $hookResult = new HookResult($this->hook, $this->sign);

        if ( $this->class ) {
            $methodExists = ClassService::methodExists($this->class, $this->method);
        } else {
            $methodExists = HelperService::isCallable($callable);
        }

        if ( $methodExists === false ) {
            $hookResult->setFailed();
            $hookResult->setErrorCode(HookResult::ERROR_METHOD_NOT_EXISTS);
        } else {
            try {
                $executionData = call_user_func_array($callable, $args);

                $hookResult->setData($executionData);
                $hookResult->setSuccess();
            } catch ( Exception $e ) {
                $hookResult->setFailed();
                $hookResult->setErrorCode(HookResult::ERROR_METHOD_CALL_FAILED);
            }
        }

        return $hookResult;
    }

    public function setSign(string $sign): void
    {
        $this->sign = $sign;
    }

    public function getSign(): string
    {
        return $this->sign;
    }

    public function getHook(): string
    {
        return $this->hook;
    }
}
