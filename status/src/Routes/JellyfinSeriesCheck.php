<?php
declare(strict_types=1);

namespace App\Routes;

use GuzzleHttp\Client;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class JellyfinSeriesCheck
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => rtrim($_ENV['JELLYFIN_URL'], '/') . '/',
            'timeout'  => 5.0,
            'headers'  => [
                'X-Emby-Token' => $_ENV['JELLYFIN_API_KEY'],
                'Accept'       => 'application/json',
            ],
        ]);
    }

    /* ---------- Routing ---------- */

    public function handle(): void
    {
        $seriesId = $_GET['series'] ?? null;

        $seriesList = $seriesId
            ? [$this->getItem($seriesId)]
            : $this->getAllSeries();

        $issues = [];

        foreach ($seriesList as $show) {
            if (empty($show['Id'])) {
                continue;
            }

            foreach ($this->getEpisodes($show['Id']) as $ep) {
                $problem = $this->detectProblem($ep);

                if ($problem) {
                    $issues[] = [
                        'series'   => $show['Name']           ?? 'unbekannt',
                        'season'   => $ep['ParentIndexNumber'] ?? '?',
                        'episode'  => $ep['IndexNumber']       ?? '?',
                        'name'     => $ep['Name']              ?? '',
                        'reason'   => $problem,
                    ];
                }
            }
        }

        /* ---------- Ausgabe ---------- */

        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
        echo $twig->render('jellyfin_series_check.twig', [
            'issues'       => $issues,
            'seriesCount'  => \count($seriesList),
            'issueCount'   => \count($issues),
        ]);
    }

    /* ---------- Jellyfin-Abfragen ---------- */

    private function getAllSeries(): array
    {
        $res  = $this->http->get('/Items', [
            'query' => [
                'IncludeItemTypes' => 'Series',
                'Recursive'        => 'true',
                'Fields'           => 'BasicSyncInfo',
            ],
        ]);
        $json = \json_decode($res->getBody()->getContents(), true);
        return $json['Items'] ?? [];
    }

    private function getItem(string $id): array
    {
        $res = $this->http->get("/Items/{$id}");
        return \json_decode($res->getBody()->getContents(), true);
    }

    private function getEpisodes(string $seriesId): array
    {
        $res  = $this->http->get('/Items', [
            'query' => [
                'ParentId'         => $seriesId,
                'IncludeItemTypes' => 'Episode',
                'Fields'           => 'Path,MediaSources,ParentIndexNumber,IndexNumber,IsPlayable,PlaybackErrorCode',
                'Recursive'        => 'true',
            ],
        ]);
        $json = \json_decode($res->getBody()->getContents(), true);
        return $json['Items'] ?? [];
    }

    /* ---------- Problem-Erkennung ---------- */

    private function detectProblem(array $ep): ?string
    {
        // keine Quelle (noch nicht gematcht oder gelöscht)
        if (empty($ep['MediaSources'])) {
            return 'Keine MediaSource – nicht erkannt';
        }

        // von Jellyfin als nicht abspielbar markiert
        if (($ep['IsPlayable'] ?? true) === false) {
            return 'Episode laut Jellyfin nicht abspielbar';
        }

        // Jellyfin meldet expliziten Fehlercode
        if (!empty($ep['PlaybackErrorCode'])) {
            return 'Jellyfin-Fehlercode: ' . $ep['PlaybackErrorCode'];
        }

        // ok
        return null;
    }
}
