<?php namespace Interfaces;

interface ApplicationRequest
{
    public function setRequestData(array $requestData): bool;
    public function getRequestData(): array;
}
