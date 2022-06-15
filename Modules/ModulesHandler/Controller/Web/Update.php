<?php namespace Modules\ModulesHandler\Controller\Web;

use Core\Context;
use Core\Validator;
use Core\Controller;

use Core\Hook\HookProvider;

use Models\Module;

use Views\Json as JsonView;

use Services\HttpService;

class Update extends Controller
{
    private Module    $moduleModel;
    private Validator $formValidator;

    public function init(): void
    {
        $this->view = new JsonView([ 'status' => false ]);
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
            HttpService::setResponseCode(401);

            $this->view->assign([
                'message' => 'You dont have permissions to do that'
            ]);

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

        $this->view->assign([ 'status'   => true ]);

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

    private function isModuleExists(int $moduleId): bool
    {
        $module = new Module($moduleId);
        $moduleIsValid = $module->isValidModel();

        if ( $moduleIsValid === false ) {
            HttpService::setResponseCode(400);

            $this->view->assign([
                'message' => 'Module with id ' . $moduleId . ' doesnt exist'
            ]);
        }

        return $moduleIsValid;
    }

    private function updateModuleDataInDb(array $moduleData): bool
    {
        $hookResultCollection = HookProvider::execute('updateModule', [ $moduleData ]);
        $hookResult = $hookResultCollection->getItemByIndex(0);

        if ( $hookResult->getData() === false ) {
            HttpService::setResponseCode(500);

            $this->view->assign([
                'message' => 'Module updating failed'
            ]);
        }

        return $hookResult->getData();
    }
}
