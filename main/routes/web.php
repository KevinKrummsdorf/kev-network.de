<?php
use Slim\App;
use Slim\Views\Twig;

return function (App $app) {
    $app->get('/', function ($request, $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'home.twig', [
            'jellyfin' => $_ENV['JELLYFIN_URL'] ?? '#',
            'status'   => $_ENV['STATUS_URL'] ?? '#',
            'webmail'  => $_ENV['WEBMAIL_URL'] ?? '#',
            'panel'    => $_ENV['PANEL_URL'] ?? '#'
        ]);
    });
};