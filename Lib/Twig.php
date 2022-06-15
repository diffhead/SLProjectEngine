<?php namespace Lib;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extension\AbstractExtension;

class Twig
{
    private Environment $twig;

    public function __construct(string $templatesPath, string $cachePath = _APP_BASE_DIR_ . 'cache/twig')
    {
        $loader = new FilesystemLoader($templatesPath);
        $environment = new Environment($loader, [ 
            'cache' => $cachePath,
            'debug' => _DEV_MODE_
        ]);

        $environment->addExtension(new DebugExtension());

        $this->twig = $environment;
    }

    public function addExtension(AbstractExtension $extension): void
    {
        $this->twig->addExtension($extension);
    }

    public function render(string $template, array $params = []): string
    {
        return $this->twig->render($template, $params);
    }
}
