<?php
namespace App\Routes;

use App\Utils\LogParser;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Dyndns
{
    public function handle(): void
    {
        $logPath = '/var/www/clients/client0/web18/private/dyndns.log';
        $updates = LogParser::parseUpdates($logPath, 10);
        $latest  = $updates[0] ?? null;

        // IP-Adressen maskieren
        foreach ($updates as &$entry) {
            $entry['ipv4'] = LogParser::maskIpv4($entry['ipv4'] ?? '');
            $entry['ipv6'] = LogParser::maskIpv6($entry['ipv6'] ?? '');
        }
        unset($entry);

        if ($latest) {
            $latest['ipv4'] = LogParser::maskIpv4($latest['ipv4'] ?? '');
            $latest['ipv6'] = LogParser::maskIpv6($latest['ipv6'] ?? '');
        }

        // Statusanzeige vorbereiten
        $latestStatus = 'Kein erfolgreicher dynv6-Eintrag gefunden.';
        $latestClass  = 'error';

        if ($latest && str_contains(strtolower($latest['response']), 'updated')) {
            $latestStatus = 'DynDNS-Update erfolgreich';
            $latestClass  = 'success';
        } elseif ($latest) {
            $latestStatus = 'Letztes Update fehlgeschlagen: ' . $latest['response'];
            $latestClass  = 'warn';
        }

        // Twig rendern
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $twig = new Environment($loader);

        echo $twig->render('dyndns.twig', [
            'updates'      => $updates,
            'latest'       => $latest,
            'latestStatus' => $latestStatus,
            'latestClass'  => $latestClass,
        ]);
    }
}
