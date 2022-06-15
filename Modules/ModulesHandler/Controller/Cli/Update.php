<?php namespace Modules\ModulesHandler\Controller\Cli;

use Core\Context;
use Core\Validator;
use Core\Controller;

use Core\Hook\HookProvider;

use Models\Module;
use Views\StdOut;
use Cli\ErrorCode;

class Update extends Controller
{
    private Module    $moduleModel;
    private Validator $formValidator;

    public function init(): void
    {
        $this->view = new StdOut();
        $this->formValidator = new Validator([
            'id'   => [
                'required' => true,
                'pattern'  => '/^\d{1,10}$/m'
            ],
            'name' => [
                'pattern'  => '/\w{1,64}/m'
            ],
            'environment' => [
                'pattern'  => '/^(cli|web|any)$/m'
            ],
            'priority' => [
                'pattern'  => '/\d{1,10}/m'
            ],
            'enable' => [
                'pattern'  => '/^(1|0)$/m'
            ]
        ]);
    }

    public function execute(array $params = []): bool
    {
        $user = Context::getInstance()->user;

        if ( $user->isAdmin() === false ) {
            $this->view->setCode(ErrorCode::ERR_ACCESS_DENIED);
            $this->view->setMessage('You dont have permissions to do that');

            return false;
        }

        return $this->updateModule($params['data']);
    }

    private function updateModule(array $moduleData): bool
    {
        if ( $this->validateModuleData($moduleData) === false ) {
            return false;
        }

        if ( $this->isModuleExists((int)$moduleData['id']) === false ) {
            return false;
        }

        if ( $this->updateModuleDataInDb($moduleData) === false ) {
            return false;
        }

        HookProvider::execute('flushCache');

        $this->view->setMessage('Successfully module data updating');

        return true;
    }

    private function validateModuleData(array $moduleData): bool
    {
        $status = $this->formValidator->validate($moduleData);

        if ( $status === false ) {
            $this->view->setCode(ErrorCode::ERR_BAD_REQUEST);
            $this->view->setMessage('Fields validation error');
        }

        return $status;
    }

    private function isModuleExists(int $moduleId): bool
    {
        $module = new Module($moduleId);
        $moduleIsValid = $module->isValidModel();

        if ( $moduleIsValid === false ) {
            $this->view->setCode(ErrorCode::ERR_BAD_REQUEST);
            $this->view->setMessage('Module with id ' . $moduleId . ' doesnt exist');
        }

        return $moduleIsValid;
    }

    private function updateModuleDataInDb(array $moduleData): bool
    {
        $hookResultCollection = HookProvider::execute('updateModule', [ $moduleData ]);
        $hookResult = $hookResultCollection->getItemByIndex(0);

        if ( $hookResult->getData() === false ) {
            $this->view->setCode(ErrorCode::ERR_HAVE_ERRORS);
            $this->view->setMessage('Module updating failed');
        }

        return $hookResult->getData();
    }
}
