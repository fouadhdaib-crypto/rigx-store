<?php
$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/app/controllers/HomeController.php';

$controller = new HomeController($config);
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'about':
        $controller->about();
        break;
    default:
        $controller->home();
}
