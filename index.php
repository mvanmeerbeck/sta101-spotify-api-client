<?php

require __DIR__ . '/vendor/autoload.php';

/**
 * classical,rock,rap,jazz,reggae 50 2
 */

$token = $argv[1];
$genres = explode(',', $argv[2]);
$limit = $argv[3];
$pages = $argv[4];

$client = new GuzzleHttp\Client();

$handle = fopen('tracks.csv', 'w');

fputcsv($handle, [
    'id',
    'name',
    'artist',
    'popularity',
    'genre',
    'danceability',
    'energy',
    'key',
    'loudness',
    'mode',
    'speechiness',
    'acousticness',
    'instrumentalness',
    'liveness',
    'valence',
    'tempo',
    'duration_ms',
    'time_signature',
]);

foreach ($genres as $genre) {
    echo $genre . PHP_EOL;
    for ($i = 0; $i < $pages; $i++) {
        echo 'page ' . $i . PHP_EOL;
        $offset = $i * $limit;

        $response = $client->request('GET', "https://api.spotify.com/v1/search?q=genre%3A$genre&type=track&offset=$offset&limit=$limit&market=FR", [
            'verify' => false,
            'headers' => [
                'Authorization' => "Bearer $token",
            ]
        ]);

        $response = json_decode($response->getBody(), true);

        foreach ($response['tracks']['items'] as $item) {
            $audioFeatures = json_decode($client->request('GET', 'https://api.spotify.com/v1/audio-features/' . $item['id'], [
                'verify' => false,
                'headers' => [
                    'Authorization' => "Bearer $token",
                ]
            ])->getBody(), true);

            $track = [
                'id' => $item['id'],
                'name' => $item['name'],
                'artist' => $item['artists'][0]['name'],
                'popularity' => $item['popularity'],
                'genre' => $genre,
                'danceability' => $audioFeatures['danceability'],
                'energy' => $audioFeatures['energy'],
                'key' => $audioFeatures['key'],
                'loudness' => $audioFeatures['loudness'],
                'mode' => $audioFeatures['mode'],
                'speechiness' => $audioFeatures['speechiness'],
                'acousticness' => $audioFeatures['acousticness'],
                'instrumentalness' => $audioFeatures['instrumentalness'],
                'liveness' => $audioFeatures['liveness'],
                'valence' => $audioFeatures['valence'],
                'tempo' => $audioFeatures['tempo'],
                'duration_ms' => $audioFeatures['duration_ms'],
                'time_signature' => $audioFeatures['time_signature'],
            ];

            foreach ($track as $j => $value) {
                if (is_float($value)) {
                    $track[$j] = sprintf('%f', $value);
                }
            }

            fputcsv($handle, $track);

            sleep(1);
        }

        sleep(1);
    }
}

fclose($handle);

