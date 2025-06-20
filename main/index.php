<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

// .env laden
$dotenv = Dotenv::createImmutable(__DIR__ . '/../private');
$dotenv->load();

$app = AppFactory::create();

// Twig Middleware
$twig = Twig::create(__DIR__ . '/templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// Routing
(require __DIR__ . '/routes/web.php')($app);

$app->run();
