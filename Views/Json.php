<?php namespace Views;

use Core\View;

use Services\JsonService;
use Services\StringService;

class Json extends View
{
    private bool $rebuildFlag = true;

    private function setRebuildFlag(bool $flag = true): void
    {
        $this->rebuildFlag = $flag;
    }

    public function __construct(array $params = [])
    {
        $this->assign($params);
        $this->render();
    }

    public function assign(array $params): void
    {
        parent::assign($params);

        $this->setRebuildFlag(true);
    }

    public function render(): string
    {
        if ( $this->rebuildFlag || StringService::isEmpty($this->template) ) {
            $this->template = JsonService::encode($this->params);

            $this->setRebuildFlag(false);
        }

        return $this->template;
    }
}
