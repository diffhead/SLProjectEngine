<?php namespace Core;

class Controller
{
    protected View $view;

    public function init(): void
    {
        if ( isset($this->view) === false ) {
            $this->view = new View();
        }
    }

    public function execute(array $params = []): bool
    {
        return true;
    }

    public function getView(): View
    {
        return $this->view;
    }
}
