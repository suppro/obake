<?php

namespace Database\Seeders;

use App\Models\GlobalDictionary;
use App\Models\Story;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем словарь (японские слова)
        $words = [
            // Базовые слова N5
            [
                'japanese_word' => 'こんにちは',
                'reading' => 'こんにちは',
                'translation_ru' => 'Здравствуйте (добрый день)',
                'translation_en' => 'Hello (good afternoon)',
                'word_type' => 'Приветствие',
                'example_ru' => 'こんにちは、元気ですか？ - Здравствуйте, как дела?',
                'example_jp' => 'こんにちは、元気ですか？',
            ],
            [
                'japanese_word' => 'ありがとう',
                'reading' => 'ありがとう',
                'translation_ru' => 'Спасибо',
                'translation_en' => 'Thank you',
                'word_type' => 'Выражение благодарности',
                'example_ru' => 'ありがとうございます - Большое спасибо',
                'example_jp' => 'ありがとうございます',
            ],
            [
                'japanese_word' => '私',
                'reading' => 'わたし',
                'translation_ru' => 'Я',
                'translation_en' => 'I, me',
                'word_type' => 'Местоимение',
                'example_ru' => '私は学生です - Я студент',
                'example_jp' => '私は学生です',
            ],
            [
                'japanese_word' => '本',
                'reading' => 'ほん',
                'translation_ru' => 'Книга',
                'translation_en' => 'Book',
                'word_type' => 'Существительное',
                'example_ru' => 'この本は面白いです - Эта книга интересная',
                'example_jp' => 'この本は面白いです',
            ],
            [
                'japanese_word' => '読む',
                'reading' => 'よむ',
                'translation_ru' => 'Читать',
                'translation_en' => 'To read',
                'word_type' => 'Глагол (う-глагол)',
                'example_ru' => '本を読む - Читать книгу',
                'example_jp' => '本を読む',
            ],
            [
                'japanese_word' => '学生',
                'reading' => 'がくせい',
                'translation_ru' => 'Студент',
                'translation_en' => 'Student',
                'word_type' => 'Существительное',
                'example_ru' => '私は学生です - Я студент',
                'example_jp' => '私は学生です',
            ],
            [
                'japanese_word' => '学校',
                'reading' => 'がっこう',
                'translation_ru' => 'Школа',
                'translation_en' => 'School',
                'word_type' => 'Существительное',
                'example_ru' => '学校に行く - Идти в школу',
                'example_jp' => '学校に行く',
            ],
            [
                'japanese_word' => '行く',
                'reading' => 'いく',
                'translation_ru' => 'Идти, ехать',
                'translation_en' => 'To go',
                'word_type' => 'Глагол',
                'example_ru' => '学校に行く - Идти в школу',
                'example_jp' => '学校に行く',
            ],
            [
                'japanese_word' => '今日',
                'reading' => 'きょう',
                'translation_ru' => 'Сегодня',
                'translation_en' => 'Today',
                'word_type' => 'Наречие',
                'example_ru' => '今日はいい天気です - Сегодня хорошая погода',
                'example_jp' => '今日はいい天気です',
            ],
            [
                'japanese_word' => '元気',
                'reading' => 'げんき',
                'translation_ru' => 'Здоровый, бодрый',
                'translation_en' => 'Healthy, energetic',
                'word_type' => 'な-прилагательное',
                'example_ru' => '元気ですか？ - Как дела? (Как здоровье?)',
                'example_jp' => '元気ですか？',
            ],
            [
                'japanese_word' => '猫',
                'reading' => 'ねこ',
                'translation_ru' => 'Кошка',
                'translation_en' => 'Cat',
                'word_type' => 'Существительное',
                'example_ru' => '猫が好きです - Я люблю кошек',
                'example_jp' => '猫が好きです',
            ],
            [
                'japanese_word' => '好き',
                'reading' => 'すき',
                'translation_ru' => 'Нравиться, любить',
                'translation_en' => 'To like, to love',
                'word_type' => 'な-прилагательное',
                'example_ru' => '猫が好きです - Я люблю кошек',
                'example_jp' => '猫が好きです',
            ],
            [
                'japanese_word' => '食べる',
                'reading' => 'たべる',
                'translation_ru' => 'Есть, кушать',
                'translation_en' => 'To eat',
                'word_type' => 'Глагол',
                'example_ru' => 'ご飯を食べる - Есть рис',
                'example_jp' => 'ご飯を食べる',
            ],
            [
                'japanese_word' => '水',
                'reading' => 'みず',
                'translation_ru' => 'Вода',
                'translation_en' => 'Water',
                'word_type' => 'Существительное',
                'example_ru' => '水を飲む - Пить воду',
                'example_jp' => '水を飲む',
            ],
            [
                'japanese_word' => '飲む',
                'reading' => 'のむ',
                'translation_ru' => 'Пить',
                'translation_en' => 'To drink',
                'word_type' => 'Глагол',
                'example_ru' => '水を飲む - Пить воду',
                'example_jp' => '水を飲む',
            ],
            // Слова N4
            [
                'japanese_word' => '勉強',
                'reading' => 'べんきょう',
                'translation_ru' => 'Учеба, изучение',
                'translation_en' => 'Study, learning',
                'word_type' => 'Существительное',
                'example_ru' => '日本語を勉強する - Изучать японский язык',
                'example_jp' => '日本語を勉強する',
            ],
            [
                'japanese_word' => '日本語',
                'reading' => 'にほんご',
                'translation_ru' => 'Японский язык',
                'translation_en' => 'Japanese language',
                'word_type' => 'Существительное',
                'example_ru' => '日本語を勉強する - Изучать японский язык',
                'example_jp' => '日本語を勉強する',
            ],
            [
                'japanese_word' => '映画',
                'reading' => 'えいが',
                'translation_ru' => 'Фильм, кино',
                'translation_en' => 'Movie, film',
                'word_type' => 'Существительное',
                'example_ru' => '映画を見る - Смотреть фильм',
                'example_jp' => '映画を見る',
            ],
            [
                'japanese_word' => '見る',
                'reading' => 'みる',
                'translation_ru' => 'Смотреть, видеть',
                'translation_en' => 'To see, to watch',
                'word_type' => 'Глагол',
                'example_ru' => '映画を見る - Смотреть фильм',
                'example_jp' => '映画を見る',
            ],
            [
                'japanese_word' => '友達',
                'reading' => 'ともだち',
                'translation_ru' => 'Друг',
                'translation_en' => 'Friend',
                'word_type' => 'Существительное',
                'example_ru' => '友達と遊ぶ - Играть с другом',
                'example_jp' => '友達と遊ぶ',
            ],
            [
                'japanese_word' => '可愛い',
                'reading' => 'かわいい',
                'translation_ru' => 'Милый, симпатичный',
                'translation_en' => 'Cute, adorable',
                'word_type' => 'い-прилагательное',
                'example_ru' => '可愛い猫 - Милая кошка',
                'example_jp' => '可愛い猫',
            ],
            [
                'japanese_word' => '楽しい',
                'reading' => 'たのしい',
                'translation_ru' => 'Веселый, приятный',
                'translation_en' => 'Fun, enjoyable',
                'word_type' => 'い-прилагательное',
                'example_ru' => '楽しい時間 - Веселое время',
                'example_jp' => '楽しい時間',
            ],
        ];

        $wordIds = [];
        $wordMap = []; // Маппинг для связи слов с их ID
        
        foreach ($words as $index => $wordData) {
            // Используем firstOrCreate, чтобы избежать дублирования
            $word = GlobalDictionary::firstOrCreate(
                ['japanese_word' => $wordData['japanese_word']],
                $wordData
            );
            $wordIds[] = $word->id;
            $wordMap[$index + 1] = $word->id; // Индекс + 1 соответствует порядковому номеру слова
        }

        // Создаем маппинг японских слов на их ID
        $wordIdMap = [];
        foreach ($wordIds as $index => $wordId) {
            $word = GlobalDictionary::find($wordId);
            if ($word) {
                $wordIdMap[$word->japanese_word] = $wordId;
            }
        }
        
        // Создаем рассказы
        $stories = [
            [
                'title' => '私の一日',
                'content' => 'こんにちは。私は学生です。今日は学校に行きました。学校で日本語を勉強しました。午後、友達と映画を見ました。とても楽しかったです。',
                'level' => 'N5',
                'description' => 'Простой рассказ о дне студента',
                'is_active' => true,
                'word_names' => ['私', '学生', '学校', '行く', '今日', '勉強', '日本語', '見る', '友達', '楽しい'],
            ],
            [
                'title' => '猫と本',
                'content' => '私は猫が好きです。今日、本を読みました。猫も一緒にいました。猫は本を見ていました。とても可愛かったです。',
                'level' => 'N5',
                'description' => 'Короткий рассказ о кошке и книге',
                'is_active' => true,
                'word_names' => ['私', '本', '読む', '今日', '猫', '好き', '見る', '可愛い'],
            ],
            [
                'title' => '朝ごはん',
                'content' => '今日の朝、私はご飯を食べました。水も飲みました。とても美味しかったです。その後、学校に行きました。',
                'level' => 'N5',
                'description' => 'Рассказ о завтраке',
                'is_active' => true,
                'word_names' => ['私', '今日', '食べる', '水', '飲む', '学校', '行く'],
            ],
            [
                'title' => '勉強の時間',
                'content' => '私は毎日日本語を勉強します。今日も勉強しました。友達と一緒に勉強しました。とても楽しかったです。',
                'level' => 'N4',
                'description' => 'Рассказ об изучении японского языка',
                'is_active' => true,
                'word_names' => ['私', '今日', '勉強', '日本語', '友達', '楽しい'],
            ],
        ];

        foreach ($stories as $storyData) {
            $wordNames = $storyData['word_names'];
            unset($storyData['word_names']);
            
            // Преобразуем имена слов в реальные ID
            $realWordIds = [];
            foreach ($wordNames as $wordName) {
                if (isset($wordIdMap[$wordName])) {
                    $realWordIds[] = $wordIdMap[$wordName];
                }
            }
            
            // Используем firstOrCreate для рассказов, чтобы избежать дублирования
            $story = Story::firstOrCreate(
                ['title' => $storyData['title']],
                $storyData
            );
            
            // Синхронизируем связи (удаляем старые и добавляем новые)
            $story->words()->sync($realWordIds);
        }

        $this->command->info('Тестовые данные успешно созданы!');
        $this->command->info('Создано слов: ' . GlobalDictionary::count());
        $this->command->info('Создано рассказов: ' . Story::count());
    }
}

