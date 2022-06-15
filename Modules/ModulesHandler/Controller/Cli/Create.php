<?php namespace Modules\ModulesHandler\Controller\Cli;

use Core\Context;
use Core\Validator;
use Core\Controller;

use Core\Hook\HookProvider;

use Models\Module;

use Views\StdOut;

class Create extends Controller
{
    private Module    $moduleModel;
    private Validator $formValidator;

    public function init(): void
    {
        $this->view = new StdOut();
        $this->formValidator = new Validator([
            'name' => [
                'required' => true,
                'pattern'  => '/\w{1,64}/m'
            ],
            'environment' => [
                'required' => true,
                'pattern'  => '/^(cli|web|any)$/m'
            ],
            'priority' => [
                'required' => true,
                'pattern'  => '/\d{1,10}/m'
            ],
            'enable' => [
                'required' => true,
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

        return $this->createModule($params['data']);
    }

    private function createModule(array $moduleData): bool
    {
        if ( $this->validateModuleData($moduleData) === false ) {
            return false;
        }

        if ( $this->createModuleModel($moduleData) === false ) {
            return false;
        }

        HookProvider::execute('flushCache');

        $this->view->setMessage('Successfully module creation. New module id ' . $this->moduleModel->id);

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

    private function createModuleModel(array $moduleData): bool
    {
        unset($moduleData['id']);

        $hookResultCollection = HookProvider::execute('createModule', [ $moduleData ]);
        /* HookResult::getData will return Models\Module or NULL */
        $hookResult = $hookResultCollection->getItemByIndex(0);
        $hookResultModule = $hookResult->getData();

        if ( $hookResultModule === null ) {
            $this->view->setCode(ErrorCode::ERR_HAVE_ERRORS);
            $this->view->setMessage('Module creation failed');

            return false;
        }

        $this->moduleModel = $hookResultModule;

        return true;
    }
}
