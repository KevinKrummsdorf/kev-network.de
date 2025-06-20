<?php
namespace App\Routes;

use App\Utils\LogParser;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Dyndns
{
public function handle(): void
{
    $updates = LogParser::parseUpdates('/var/www/clients/client0/web18/private/dyndns.log', 10);
    $latest  = $updates[0] ?? null;

    // IPs maskieren
    foreach ($updates as &$entry) {
        $entry['ipv4'] = LogParser::maskIpv4($entry['ipv4'] ?? '');
        $entry['ipv6'] = LogParser::maskIpv6($entry['ipv6'] ?? '');
    }
    unset($entry);

    $latestStatus = '❌ Kein erfolgreicher dynv6-Eintrag gefunden.';
    $latestClass  = 'error';

    if ($latest && str_contains(strtolower($latest['response']), 'updated')) {
        $latestStatus = '✅ DynDNS-Update erfolgreich';
        $latestClass  = 'success';
    } elseif ($latest) {
        $latestStatus = '⚠️ Letztes Update fehlgeschlagen: ' . $latest['response'];
        $latestClass  = 'warn';
    }

    $dailyCounts = [];

    foreach ($updates as $entry) {
        $timestamp = strtotime($entry['date'] ?? '');
        $day = date('Y-m-d', $timestamp); // Gruppierung nach Tag

        if (!isset($dailyCounts[$day])) {
            $dailyCounts[$day] = 0;
        }
        $dailyCounts[$day]++;
    }

    setlocale(LC_TIME, 'de_DE.UTF-8');
    $labels = array_map(
        fn($d) => strftime('%a %d.%m.', strtotime($d)),
        array_keys($dailyCounts)
    );
    $values = array_values($dailyCounts);
          


    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../templates');
    $twig = new \Twig\Environment($loader);
    echo $twig->render('dyndns.twig', [
        'updates'      => $updates,
        'latest'       => $latest,
        'latestStatus' => $latestStatus,
        'latestClass'  => $latestClass,
        'chartLabels'  => $labels,
        'chartValues'  => $values,
    ]);
}

}
