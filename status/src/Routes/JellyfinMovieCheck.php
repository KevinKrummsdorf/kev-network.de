<?php
declare(strict_types=1);

namespace App\Routes;

use GuzzleHttp\Client;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class JellyfinMovieCheck
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

    public function handle(): void
    {
        $movies = $this->getAllMovies();
        $issues = [];

        foreach ($movies as $movie) {
            if (empty($movie['Id'])) {
                continue;
            }

            if (isset($movie['IsPlayable']) && $movie['IsPlayable'] === false) {
                $issues[] = [
                    'title' => $movie['Name'] ?? 'Unbekannt',
                    'year'  => $movie['ProductionYear'] ?? '',
                    'reason' => 'Film nicht abspielbar laut API',
                ];
            }
        }

        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
        echo $twig->render('jellyfin_movies_check.twig', [
            'issues' => $issues,
            'movieCount' => count($movies),
            'issueCount' => count($issues),
        ]);
    }

    private function getAllMovies(): array
    {
        $res = $this->http->get('/Items', [
            'query' => [
                'IncludeItemTypes' => 'Movie',
                'Recursive' => 'true',
                'Fields' => 'IsPlayable,ProductionYear',
            ],
        ]);
        $json = json_decode($res->getBody()->getContents(), true);
        return $json['Items'] ?? [];
    }
}
