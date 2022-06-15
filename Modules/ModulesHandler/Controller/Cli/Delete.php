<?php namespace Modules\ModulesHandler\Controller\Cli;

use Core\Context;
use Core\Controller;

use Core\Hook\HookProvider;

use Views\StdOut;
use Cli\ErrorCode;
use Services\StringService;

class Delete extends Controller
{
    public function init(): void
    {
        $this->view = new StdOut();
    }

    public function execute(array $params = []): bool
    {
        $user = Context::getInstance()->user;

        if ( $user->isAdmin() === false ) {
            $this->view->setCode(ErrorCode::ERR_ACCESS_DENIED);
            $this->view->setMessage('You dont have permissions to do that');

            return false;
        }

        if ( isset($params['data']['id']) === false || StringService::isMatch('/^\d{1,10}$/m', $params['data']['id']) === false ) {
            $this->view->setCode(ErrorCode::ERR_BAD_REQUEST);
            $this->view->setMessage('You must to send correct module id');

            return false;
        }

        return $this->deleteModule((int)$params['data']['id']);
    }

    private function deleteModule(int $moduleId): bool
    {
        $hookResultCollection = HookProvider::execute('deleteModule', [ $moduleId ]);
        $hookResult = $hookResultCollection->getItemByIndex(0);

        if ( $hookResult->getData() === false ) {
            $this->view->setCode(ErrorCode::ERR_HAVE_ERRORS);
            $this->view->setMessage('Module removing operation failed');

            return false;
        }

        HookProvider::execute('flushCache');

        $this->view->setMessage('Successfully module removing');

        return true;
    }
}
