<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Routes\Dyndns;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../private');
$dotenv->safeLoad();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch (true) {
    case $path === '/' || $path === '/index':
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        $twig = new \Twig\Environment($loader);
        echo $twig->render('index.twig');
        break;

    case str_starts_with($path, '/dyndns'):
        (new Dyndns())->handle();
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Seite nicht gefunden</h1>";
        break;
}
