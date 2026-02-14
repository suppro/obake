<?php
// Simulate Laravel request to test API
require __DIR__ . '/vendor/autoload.php';

// Boot Laravel
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a request to the API endpoint
$request = Illuminate\Http\Request::create('/reading-quiz-words', 'GET', [], [], [], [
    'HTTP_ACCEPT' => 'application/json'
]);

// Set authenticated user (user ID 1)
$user = \App\Models\User::find(1);
if (!$user) {
    echo "User 1 not found\n";
    exit(1);
}

auth()->setUser($user);

// Make request
$response = $kernel->handle($request);

echo "Status: " . $response->status() . "\n";
echo "Content-Type: " . $response->headers->get('content-type') . "\n";
echo "Body:\n";
echo $response->getContent() . "\n";
