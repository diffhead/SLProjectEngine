<?php namespace Core;

use Services\StringService;

class Validator
{
    private array $definitions = [];
    private array $validationErrors = [];
    private bool  $lastValidationStatus = true;

    public function __construct(array $definitions = [])
    {
        $this->definitions = $definitions;
    }

    public function validate(array $form): bool
    {
        $this->validationErrors = [];
        $this->lastValidationStatus = true;

        foreach ( $this->definitions as $field => $params ) {
            if ( 
                isset($form[$field]) === false && 
                isset($params['required']) && $params['required']
            ) {
                $this->lastValidationStatus = false;
                $this->validationErrors[$field] = 'Required field not found';

                continue;
            }

            if ( isset($form[$field]) ) {
                if ( 
                    isset($params['pattern']) &&
                    StringService::isMatch($params['pattern'], $form[$field]) === false
                ) {
                    $this->lastValidationStatus = false;
                    $this->validationErrors[$field] = 'Field pattern test failed';

                    continue;
                }

                if (
                    isset($params['length']) && isset($params['length']['from']) && 
                    StringService::strLength($form[$field]) < (int)$params['length']['from']
                ) {
                    $this->lastValidationStatus = false;
                    $this->validationErrors[$field] = "Field length must to be more than {$params['length']['from']}";

                    continue;
                }

                if (
                    isset($params['length']) && isset($params['length']['to']) && 
                    StringService::strLength($form[$field]) > (int)$params['length']['to']
                ) {
                    $this->lastValidationStatus = false;
                    $this->validationErrors[$field] = "Field length must to be lower than {$params['length']['to']}";

                    continue;
                }
            }
        }

        return $this->lastValidationStatus;
    }

    public function getErrors(): array
    {
        return $this->validationErrors;
    }
}
