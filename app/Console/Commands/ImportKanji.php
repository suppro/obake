<?php

namespace App\Console\Commands;

use App\Models\Kanji;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportKanji extends Command
{
    protected $signature = 'kanji:import {file : Путь к CSV файлу}';
    protected $description = 'Импортировать кандзи из CSV файла';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("Файл не найден: {$filePath}");
            return 1;
        }
        
        $this->info('Начинаю импорт кандзи...');
        
        // Удаляем все существующие кандзи
        $this->info('Удаляю существующие кандзи...');
        DB::table('kanji')->truncate();
        $this->info('Существующие кандзи удалены.');
        
        // Читаем CSV файл
        $file = fopen($filePath, 'r');
        if (!$file) {
            $this->error("Не удалось открыть файл: {$filePath}");
            return 1;
        }
        
        // Определяем разделитель (точка с запятой или запятая)
        $firstLine = fgets($file);
        rewind($file);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        $this->info("Используется разделитель: " . ($delimiter === ';' ? 'точка с запятой (;)' : 'запятая (,)'));
        
        // Читаем заголовки
        $headers = fgetcsv($file, 0, $delimiter);
        if (!$headers) {
            $this->error("Не удалось прочитать заголовки из файла");
            fclose($file);
            return 1;
        }
        
        // Нормализуем заголовки (убираем пробелы, приводим к нижнему регистру)
        $headers = array_map(function($header) {
            return trim(mb_strtolower($header));
        }, $headers);
        
        $this->info('Заголовки: ' . implode(', ', $headers));
        
        // Маппинг столбцов
        $kanjiIndex = $this->findColumnIndex($headers, ['кандзи', 'kanji', 'символ']);
        $readingIndex = $this->findColumnIndex($headers, ['чтение', 'reading', 'он-ёми', 'кун-ёми']);
        $translationIndex = $this->findColumnIndex($headers, ['перевод', 'translation', 'перевод на русский']);
        $examplesIndex = $this->findColumnIndex($headers, ['примеры слов', 'examples', 'примеры', 'слова']);
        $mnemonicIndex = $this->findColumnIndex($headers, ['мнемоническая подсказка', 'mnemonic', 'подсказка', 'мнемоника']);
        
        if ($kanjiIndex === false) {
            $this->error("Не найден столбец с кандзи. Доступные столбцы: " . implode(', ', $headers));
            fclose($file);
            return 1;
        }
        
        if ($translationIndex === false) {
            $this->error("Не найден столбец с переводом. Доступные столбцы: " . implode(', ', $headers));
            fclose($file);
            return 1;
        }
        
        $imported = 0;
        $errors = 0;
        $lineNumber = 1;
        
        // Читаем данные
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            $lineNumber++;
            
            // Пропускаем пустые строки
            if (empty(array_filter($row))) {
                continue;
            }
            
            try {
                $kanji = trim($row[$kanjiIndex] ?? '');
                $translation = trim($row[$translationIndex] ?? '');
                
                if (empty($kanji) || empty($translation)) {
                    $this->warn("Строка {$lineNumber}: пропущена (пустое кандзи или перевод)");
                    $errors++;
                    continue;
                }
                
                // Получаем чтение
                $reading = ($readingIndex !== false && !empty(trim($row[$readingIndex] ?? ''))) 
                    ? trim($row[$readingIndex]) 
                    : null;
                
                // Формируем описание из примеров
                $description = null;
                if ($examplesIndex !== false && !empty(trim($row[$examplesIndex] ?? ''))) {
                    $description = 'Примеры слов: ' . trim($row[$examplesIndex]);
                }
                
                $mnemonic = ($mnemonicIndex !== false && !empty(trim($row[$mnemonicIndex] ?? ''))) 
                    ? trim($row[$mnemonicIndex]) 
                    : null;
                
                // Создаем или обновляем запись (если кандзи уже существует, обновляем)
                Kanji::updateOrCreate(
                    ['kanji' => $kanji], // Условие поиска
                    [
                        'reading' => $reading,
                        'translation_ru' => $translation,
                        'description' => $description,
                        'mnemonic' => $mnemonic,
                        'is_active' => true,
                    ]
                );
                
                $imported++;
                
                if ($imported % 100 === 0) {
                    $this->info("Импортировано: {$imported} кандзи...");
                }
            } catch (\Exception $e) {
                $this->error("Строка {$lineNumber}: ошибка - " . $e->getMessage());
                $errors++;
            }
        }
        
        fclose($file);
        
        $this->info("\nИмпорт завершен!");
        $this->info("Успешно импортировано: {$imported} кандзи");
        if ($errors > 0) {
            $this->warn("Ошибок: {$errors}");
        }
        
        return 0;
    }
    
    private function findColumnIndex(array $headers, array $possibleNames): ?int
    {
        foreach ($possibleNames as $name) {
            $index = array_search($name, $headers);
            if ($index !== false) {
                return $index;
            }
        }
        return false;
    }
}

