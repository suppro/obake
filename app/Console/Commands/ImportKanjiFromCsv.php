<?php

namespace App\Console\Commands;

use App\Models\Kanji;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportKanjiFromCsv extends Command
{
    protected $signature = 'kanji:import-csv {file=kanji_lv.csv}';
    protected $description = 'Импорт кандзи из CSV файла с обновлением уровней JLPT';

    public function handle()
    {
        $filePath = base_path($this->argument('file'));
        
        if (!file_exists($filePath)) {
            $this->error("Файл не найден: {$filePath}");
            return 1;
        }

        $this->info("Начинаем импорт из {$filePath}...");
        
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file, 0, ';'); // Пропускаем заголовок
        
        $updated = 0;
        $created = 0;
        $errors = 0;
        
        while (($row = fgetcsv($file, 0, ';')) !== false) {
            if (count($row) < 2) continue;
            
            $kanjiChar = trim($row[0]);
            $jlptLevel = trim($row[1]);
            
            if (empty($kanjiChar)) continue;
            
            try {
                $existing = Kanji::where('kanji', $kanjiChar)->first();
                
                if ($existing) {
                    // Обновляем только JLPT уровень
                    $existing->jlpt_level = $jlptLevel;
                    $existing->save();
                    $updated++;
                    $this->line("✓ Обновлен: {$kanjiChar} → N{$jlptLevel}");
                } else {
                    // Создаем новый кандзи с данными из Jisho API
                    $kanjiData = $this->fetchKanjiData($kanjiChar);
                    
                    Kanji::create([
                        'kanji' => $kanjiChar,
                        'jlpt_level' => $jlptLevel,
                        'translation_ru' => $kanjiData['translation_ru'],
                        'reading' => $kanjiData['reading'],
                        'description' => $kanjiData['description'],
                        'is_active' => true,
                    ]);
                    
                    $created++;
                    $this->info("✓ Создан: {$kanjiChar} (N{$jlptLevel}) - {$kanjiData['translation_ru']}");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("✗ Ошибка для {$kanjiChar}: " . $e->getMessage());
            }
        }
        
        fclose($file);
        
        $this->newLine();
        $this->info("=== Импорт завершен ===");
        $this->info("Обновлено: {$updated}");
        $this->info("Создано: {$created}");
        if ($errors > 0) {
            $this->warn("Ошибок: {$errors}");
        }
        
        return 0;
    }

    /**
     * Получить данные о кандзи из Jisho API
     */
    private function fetchKanjiData(string $kanji): array
    {
        try {
            // Пробуем получить данные из Jisho API
            $response = Http::timeout(5)->get("https://jisho.org/api/v1/search/words", [
                'keyword' => $kanji
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['data'])) {
                    $firstResult = $data['data'][0];
                    
                    // Извлекаем чтения
                    $readings = [];
                    if (!empty($firstResult['japanese'])) {
                        foreach ($firstResult['japanese'] as $reading) {
                            if (!empty($reading['reading'])) {
                                $readings[] = $reading['reading'];
                            }
                        }
                    }
                    
                    // Извлекаем значения (переводим на русский базовые)
                    $meanings = [];
                    if (!empty($firstResult['senses'])) {
                        foreach ($firstResult['senses'] as $sense) {
                            if (!empty($sense['english_definitions'])) {
                                $meanings = array_merge($meanings, $sense['english_definitions']);
                            }
                        }
                    }
                    
                    // Базовый перевод на русский
                    $translationRu = $this->translateBasicMeaning($meanings);
                    
                    // Примеры слов с этим кандзи
                    $examples = $this->getExampleWords($kanji, $data['data']);
                    
                    return [
                        'reading' => implode(', ', array_slice(array_unique($readings), 0, 3)),
                        'translation_ru' => $translationRu,
                        'description' => $examples,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки API
        }
        
        // Возвращаем базовые данные если API не сработал
        return [
            'reading' => '',
            'translation_ru' => $kanji, // Используем сам кандзи как placeholder
            'description' => '',
        ];
    }

    /**
     * Перевести английские значения на русский (базовые)
     */
    private function translateBasicMeaning(array $meanings): string
    {
        if (empty($meanings)) {
            return '';
        }
        
        // Словарь часто встречающихся переводов
        $dictionary = [
            'one' => 'один',
            'two' => 'два',
            'three' => 'три',
            'four' => 'четыре',
            'five' => 'пять',
            'six' => 'шесть',
            'seven' => 'семь',
            'eight' => 'восемь',
            'nine' => 'девять',
            'ten' => 'десять',
            'hundred' => 'сто',
            'thousand' => 'тысяча',
            'ten thousand' => 'десять тысяч',
            'person' => 'человек',
            'day' => 'день',
            'month' => 'месяц',
            'year' => 'год',
            'time' => 'время',
            'now' => 'сейчас',
            'today' => 'сегодня',
            'book' => 'книга',
            'see' => 'видеть',
            'come' => 'приходить',
            'go' => 'идти',
            'say' => 'говорить',
            'hand' => 'рука',
            'foot' => 'нога',
            'water' => 'вода',
            'fire' => 'огонь',
            'wood' => 'дерево',
            'gold' => 'золото',
            'big' => 'большой',
            'small' => 'маленький',
            'up' => 'верх',
            'down' => 'низ',
            'middle' => 'середина',
            'mountain' => 'гора',
            'river' => 'река',
            'life' => 'жизнь',
            'student' => 'ученик',
            'school' => 'школа',
            'before' => 'до',
            'after' => 'после',
            'inside' => 'внутри',
            'outside' => 'снаружи',
            'left' => 'левый',
            'right' => 'правый',
            'north' => 'север',
            'south' => 'юг',
            'east' => 'восток',
            'west' => 'запад',
        ];
        
        // Берем первое значение и пытаемся перевести
        $firstMeaning = strtolower($meanings[0]);
        
        foreach ($dictionary as $en => $ru) {
            if (stripos($firstMeaning, $en) !== false) {
                return $ru;
            }
        }
        
        // Если перевода нет, возвращаем первое английское значение
        return $meanings[0];
    }

    /**
     * Получить примеры слов с этим кандзи
     */
    private function getExampleWords(string $kanji, array $data): string
    {
        $examples = [];
        
        foreach (array_slice($data, 0, 5) as $item) {
            if (!empty($item['japanese'])) {
                foreach ($item['japanese'] as $japanese) {
                    if (!empty($japanese['word']) && mb_strpos($japanese['word'], $kanji) !== false) {
                        $word = $japanese['word'];
                        $reading = $japanese['reading'] ?? '';
                        
                        // Берем первое английское значение
                        $meaning = '';
                        if (!empty($item['senses'][0]['english_definitions'])) {
                            $meaning = $item['senses'][0]['english_definitions'][0];
                        }
                        
                        if ($word && $meaning) {
                            $examples[] = "{$word} ({$reading}) - {$meaning}";
                        }
                        
                        break;
                    }
                }
            }
            
            if (count($examples) >= 3) break;
        }
        
        return implode('; ', $examples);
    }
}
