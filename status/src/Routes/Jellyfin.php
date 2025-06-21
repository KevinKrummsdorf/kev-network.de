<?php
namespace App\Routes;

use GuzzleHttp\Client;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Jellyfin
{
    public function handle(): void
    {
        $url    = rtrim($_ENV['JELLYFIN_URL'], '/');
        $apiKey = $_ENV['JELLYFIN_API_KEY'];

        $status = '❌ Jellyfin nicht erreichbar';
        $class  = 'error';
        $systemInfo = [];
        $mediaStats = [];

        try {
            $client = new Client([
                'base_uri' => $url,
                'timeout'  => 3.0,
            ]);

            // Status-Test
            $ping = $client->get('/System/Info/Public', [
                'headers' => ['X-Emby-Token' => $apiKey],
            ]);
            if ($ping->getStatusCode() === 200) {
                $status = '✅ Jellyfin ist erreichbar';
                $class = 'success';
            }

            // Systeminfo
            $sysRes = $client->get('/System/Info/Public', [
                'headers' => ['X-Emby-Token' => $apiKey],
            ]);
            $sysData = json_decode((string) $sysRes->getBody(), true);

            $systemInfo = [
                'version'     => $sysData['Version'] ?? '',
                'deviceName'  => $sysData['ServerName'] ?? '',
                'upToDate'    => isset($sysData['HasUpdateAvailable']) ? !$sysData['HasUpdateAvailable'] : null,
            ];

            // Medienstatistik
            $itemsRes = $client->get('/Items/Counts', [
                'headers' => ['X-Emby-Token' => $apiKey],
            ]);
            $items = json_decode((string) $itemsRes->getBody(), true);

            $mediaStats = [
                'Filme'     => $items['MovieCount'] ?? 0,
                'Serien'    => $items['SeriesCount'] ?? 0,
                'Episoden'  => $items['EpisodeCount'] ?? 0,
            ];

        } catch (\Throwable $e) {
            $status = '❌ Fehler: ' . $e->getMessage();
            $class = 'error';
        }

        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $twig   = new Environment($loader);

        echo $twig->render('jellyfin.twig', [
            'statusText'   => $status,
            'statusClass'  => $class,
            'systemInfo'   => $systemInfo,
            'mediaStats'   => $mediaStats,
        ]);
    }
}
