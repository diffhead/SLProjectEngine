<?php namespace Cli;

use Interfaces\ApplicationRequest;

class Request implements ApplicationRequest
{
    private string $action = '';
    private array  $requestData = [];

    public function __construct(string $action, array $data = [])
    {
        $this->setRequestData([
            'action' => $action,
            'data'   => $data
        ]);
    }

    public function setRequestData(array $requestData): bool
    {
        $this->action = $requestData['action'];
        $this->requestData = $requestData['data'];

        return true;
    }

    public function getRequestData(): array
    {
        return [
            'action' => $this->action,
            'data'   => $this->requestData
        ];
    }
}
