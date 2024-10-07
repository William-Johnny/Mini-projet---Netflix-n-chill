<?php
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
            "start_date": { "type": "keyword" },
            "start_time": { "type": "keyword" },
            "duration": { "type": "keyword" },
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
        $doc[$column] = $line[$index];
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
// After the loop
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