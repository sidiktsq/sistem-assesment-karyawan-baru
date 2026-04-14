<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    Illuminate\Http\Request::capture()
);

$apiKey = env('GEMINI_API_KEY');

if (!$apiKey) {
    echo "GEMINI_API_KEY not found in .env";
    exit;
}

echo "Checking models for API Key: " . substr($apiKey, 0, 5) . "...\n\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['error'])) {
    echo "Error: " . $data['error']['message'] . "\n";
} else {
    foreach ($data['models'] as $model) {
        echo "- " . $model['name'] . " (" . $model['displayName'] . ")\n";
    }
}
