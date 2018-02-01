<?php

require __DIR__ . '/vendor/autoload.php';

/**
 * classical,rock,rap,jazz,reggae 10
 */

$token = $argv[1];
$genres = explode(',', $argv[2]);
$limit = $argv[3];

$tracks = [];
$client = new GuzzleHttp\Client();

foreach ($genres as $genre) {
    $response = $client->request('GET', "https://api.spotify.com/v1/search?q=genre%3A$genre&type=track&limit=$limit&market=FR", [
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

        $tracks[] = [
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

        sleep(1);
    }

    sleep(1);
}

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

foreach ($tracks as $track) {
    foreach ($track as $i => $value) {
        if (is_float($value)) {
            $track[$i] = sprintf('%f', $value);
        }
    }

    fputcsv($handle, $track);
}

fclose($handle);

