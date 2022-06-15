<?php namespace Modules\CacheHandler\Controller;

use Core\Controller;
use Core\Hook\HookProvider;

use Cli\ErrorCode;

use Views\StdOut;

class Flush extends Controller
{
    public function init(): void
    {
        $this->view = new StdOut();
    }

    public function execute(array $params = []): bool
    {
        $hookResCollection = HookProvider::execute('flushCache');
        $hookResult = $hookResCollection->getItemByIndex(0);

        $flush = $hookResult && $hookResult->getData();

        if ( $flush === false ) {
            $this->view->setCode(ErrorCode::ERR_HAVE_ERRORS);
            $this->view->setMessage('Failed cache flushing');

            return false;
        }

        $this->view->setCode(0);
        $this->view->setMessage('Successfully cache flushing');

        return true;
    }
}
