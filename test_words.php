<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ReadingQuizWord;

// Check existing words
$words = ReadingQuizWord::where('user_id', 1)->get();
echo "Existing words:\n";
foreach ($words as $w) {
    echo "ID: {$w->id}, JP: {$w->japanese_word}, RU: {$w->translation_ru}\n";
}

// Create a few more test words
$testWords = [
    ['japanese_word' => '学校', 'reading' => 'がっこう', 'translation_ru' => 'школа', 'translation_en' => 'school'],
    ['japanese_word' => '先生', 'reading' => 'せんせい', 'translation_ru' => 'учитель', 'translation_en' => 'teacher'],
    ['japanese_word' => '猫', 'reading' => 'ねこ', 'translation_ru' => 'кошка', 'translation_en' => 'cat'],
    ['japanese_word' => '犬', 'reading' => 'いぬ', 'translation_ru' => 'собака', 'translation_en' => 'dog'],
    ['japanese_word' => '本', 'reading' => 'ほん', 'translation_ru' => 'книга', 'translation_en' => 'book'],
];

echo "\nCreating test words:\n";
foreach ($testWords as $data) {
    $data['user_id'] = 1;
    $word = ReadingQuizWord::create($data);
    echo "Created: ID {$word->id}, {$data['japanese_word']}\n";
}

echo "\nTotal words for user 1: " . ReadingQuizWord::where('user_id', 1)->count() . "\n";
?>
