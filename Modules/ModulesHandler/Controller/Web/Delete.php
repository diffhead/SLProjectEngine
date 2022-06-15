<?php namespace Modules\ModulesHandler\Controller\Web;

use Core\Context;
use Core\Controller;

use Core\Hook\HookProvider;

use Views\Json as JsonView;

use Models\Module;

use Services\HttpService;
use Services\StringService;

class Delete extends Controller
{
    public function init(): void
    {
        $this->view = new JsonView([ 'status' => false ]);
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

        if ( isset($params['data']['id']) === false || StringService::isMatch('/^\d{1,10}$/m', $params['data']['id']) === false ) {
            HttpService::setResponseCode(400);

            $this->view->assign([
                'message' => 'You must to send correct module id'
            ]);

            return false;
        }

        return $this->deleteModule((int)$params['data']['id']);
    }

    private function deleteModule(int $moduleId): bool
    {
        $hookResultCollection = HookProvider::execute('deleteModule', [ $moduleId ]);
        $hookResult = $hookResultCollection->getItemByIndex(0);

        if ( $hookResult->getData() === false ) {
            HttpService::setResponseCode(500);

            $this->view->assign([
                'message' => 'Module removing operation failed'
            ]);

            return false;
        }

        HookProvider::execute('flushCache');

        $this->view->assign([ 
            'status'   => true
        ]);

        return true;
    }
}
