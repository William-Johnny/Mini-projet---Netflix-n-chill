<?php
opcache_reset();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

const ES_URL = 'https://student:student@es.h91.co';

$context = stream_context_create([
    'http' => [
        'method' => 'DELETE',
    ]
]);
$response = file_get_contents(ES_URL . '/netflix_data', false, $context);


$template = <<<JSON
{
    "settings": {
        "number_of_replicas": 0
    },
    "mappings": {
        "properties": {
            "profile_name": { "type": "keyword" },
            "start_date": { "type": "date" },
            "start_time": { "type": "keyword" },
            "duration": {"type": "integer"},
            "title": { "type": "keyword" },
            "season_number": { "type": "keyword" },
            "episode_title": { "type": "keyword" },
            "episode_number": { "type": "keyword" },
            "device_type": { "type": "keyword" }
        }
    }
}
JSON;

$context = stream_context_create([
    'http' => [
        'method' => 'PUT',
        'header' => 'Content-Type: application/json',
        'content' => $template
    ]
]);
$response = file_get_contents(ES_URL . '/netflix_data', false, $context);

function convertToSeconds($time) {
    list($hours, $minutes, $seconds) = explode(':', $time);
    return ($hours * 3600) + ($minutes * 60) + $seconds;
}

$file = fopen('output.csv', 'r');
$columns = fgetcsv($file);
$columns = array_map(function ($column) {
    return strtolower(str_replace(' ', '_', $column));
}, $columns);
$colsize = count($columns);

$bulk = '';
$counter = 0;
$previousLine = [];
while ($line = fgetcsv($file)) {

    foreach ($columns as $index => $column) {
        if ($column == 'duration') {
            // Convert duration from "HH:mm:ss" to total seconds
            $doc[$column] = convertToSeconds($line[$index]);
        } else {
            $doc[$column] = $line[$index];
        }
    }
    
    $bulk .= json_encode(['index' => ['_index' => 'netflix_data', '_id' => $doc['id']]]) . "\n";
    $bulk .= json_encode($doc) . "\n";
    if (strlen($bulk) > 1000000) {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $bulk
            ]
        ]);
        $response = file_get_contents(ES_URL . '/netflix_data/_bulk', false, $context);

        $bulk = '';
    }
}

if (!empty($bulk)) {
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $bulk
        ]
    ]);
    $response = file_get_contents(ES_URL . '/netflix_data/_bulk', false, $context);
}

?>