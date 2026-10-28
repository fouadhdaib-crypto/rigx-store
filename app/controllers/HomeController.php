<?php
class HomeController
{
    public function __construct(private array $config)
    {
    }

    public function home(): void
    {
        $appName = $this->config['app_name'];
        require __DIR__ . '/../views/home.php';
    }

    public function about(): void
    {
        $appName = $this->config['app_name'];
        require __DIR__ . '/../views/about.php';
    }
}
