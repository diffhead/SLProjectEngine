<?php namespace Modules\ModulesHandler\Views;

use Core\View;

class ModuleTemplate extends View
{
    protected string $template = 'Modules/ModulesHandler/templates/module-template.tpl';
    protected bool   $templateIsFile = true;

    public function __construct(string $moduleName)
    {
        parent::__construct();

        $this->assign([
            'moduleName' => $moduleName
        ]);
    }
}
