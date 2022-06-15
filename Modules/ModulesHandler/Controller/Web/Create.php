<?php namespace Modules\ModulesHandler\Controller\Web;

use Core\Context;
use Core\Validator;
use Core\Controller;

use Core\Hook\HookProvider;

use Models\Module;

use Views\Json as JsonView;

use Services\HttpService;

class Create extends Controller
{
    private Module    $moduleModel;
    private Validator $formValidator;

    public function init(): void
    {
        $this->view = new JsonView([ 'status' => false ]);
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
            HttpService::setResponseCode(401);

            $this->view->assign([
                'message' => 'You dont have permissions to do that'
            ]);

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

        $this->view->assign([
            'status'   => true,
            'moduleId' => $this->moduleModel->id
        ]);

        return true;
    }

    private function validateModuleData(array $moduleData): bool
    {
        $status = $this->formValidator->validate($moduleData);

        if ( $status === false ) {
            HttpService::setResponseCode(400);

            $this->view->assign([
                'message' => 'Fields validation error. See dev console',
                'errors'  => $this->formValidator->getErrors()
            ]);
        }

        return $status;
    }

    private function createModuleModel(array $moduleData): bool
    {
        unset($moduleData['id']);

        $hookResultCollection = HookProvider::execute('createModule', [ $moduleData ]);
        /* HookResult::getData will return Models\Module or NULL */
        $hookResult = $hookResultCollection->getItemByIndex(0);

        if ( $hookResult->getData() === null ) {
            HttpService::setResponseCode(500);

            $this->view->assign([
                'message' => 'Module creation failed'
            ]);

            return false;
        }

        $this->moduleModel = $hookResult->getData();

        return true;
    }
}
