<?php

require __DIR__ . '/vendor/autoload.php';

$query = $argv[1];
$workflow = new Alfred\Workflows\Workflow;

try {
    $client = new GuzzleHttp\Client(['base_uri' => 'https://learnku.com']);
    $options = [
        'timeout' => 3,
        'json' => [
            'q' => $query
        ]
    ];
    $response = $client->request('GET', '/books/api_search/179', $options);
    $results = json_decode($response->getBody())->results;
} catch (\Exception $exception) {
    $results = null;
}

if (empty($results)) {
    $google = sprintf('https://www.google.com/search?q=%s', rawurlencode("laravel {$query}"));

    $workflow->result()
        ->title('Search Google')
        ->icon('google.png')
        ->subtitle(sprintf('No match found. Search Google for: "%s"', $query))
        ->arg($google)
        ->quicklookurl($google)
        ->valid(true);

    $workflow->result()
        ->title('Open CN Docs')
        ->icon('icon.png')
        ->subtitle('No match found. Open laravel.com/docs...')
        ->arg('https://learnku.com/docs/laravel')
        ->quicklookurl('https://learnku.com/docs/laravel')
        ->valid(true);

    echo $workflow->output();
    exit;
}

foreach ($results as $category => $hit) {
    foreach ($hit->results as $item) {
        $title = $category . ' - ' . $item->title;
        $workflow->result()
            ->uid($title)
            ->title($title)
            ->autocomplete($title)
            ->subtitle($item->description)
            ->arg($item->url)
            ->quicklookurl($item->url)
            ->valid(true);
    }
}

echo $workflow->output();
