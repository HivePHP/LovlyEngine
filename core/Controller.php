<?php
declare(strict_types=1);

namespace HivePHP;

use Twig\Environment;

abstract class Controller
{
    protected Environment $view;
    protected AssetManager $assets;
    protected mixed $userModel;

    public function __construct(protected Container $container)
    {
        $this->view       = $container->get('view');
        $this->assets     = $container->get('assets');
        $this->userModel  = $container->get('user_model');
    }
}