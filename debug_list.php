<?php
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Инициализируем Eloquent вручную
$app = app();
$app['db']->connection();

$list = \App\Models\ReadingQuizList::find(1);
if (!$list) {
    echo "Список 1 не найден!\n";
    exit(1);
}

echo "List 1:\n";
echo "- ID: " . $list->id . "\n";
echo "- Name: " . $list->name . "\n";
echo "- Word count (field): " . $list->word_count . "\n";
echo "\nItems in list:\n";
$items = $list->items()->get();
echo "Total items: " . count($items) . "\n";
foreach ($items as $item) {
    echo "- Item ID: $item->id, word_id: $item->word_id, word_type: '" . ($item->word_type ?? 'NULL') . "'\n";
}
echo "\ngetWords() result: " . json_encode($list->getWords()) . "\n";
