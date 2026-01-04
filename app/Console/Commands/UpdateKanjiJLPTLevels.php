<?php

namespace App\Console\Commands;

use App\Models\Kanji;
use Illuminate\Console\Command;

class UpdateKanjiJLPTLevels extends Command
{
    protected $signature = 'kanji:update-jlpt {file}';
    protected $description = 'Обновить уровни JLPT для кандзи из CSV файла';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("Файл не найден: {$filePath}");
            return 1;
        }

        $this->info("Начинаю обновление уровней JLPT из файла: {$filePath}");

        // Определяем разделитель
        $firstLine = file($filePath)[0];
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        $this->info("Используется разделитель: " . ($delimiter === ';' ? 'точка с запятой (;)' : 'запятая (,)'));
        
        // Открываем файл
        $file = fopen($filePath, 'r');
        if (!$file) {
            $this->error("Не удалось открыть файл: {$filePath}");
            return 1;
        }

        // Читаем заголовки
        $headers = fgetcsv($file, 0, $delimiter);
        if (!$headers) {
            $this->error("Не удалось прочитать заголовки из файла");
            fclose($file);
            return 1;
        }

        // Нормализуем заголовки (убираем BOM и пробелы, приводим к нижнему регистру)
        $headers = array_map(function($header) {
            // Убираем BOM (UTF-8 BOM: \xEF\xBB\xBF)
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
            return trim(mb_strtolower($header));
        }, $headers);

        $this->info('Заголовки: ' . implode(', ', $headers));

        // Находим индексы столбцов
        $kanjiIndex = $this->findColumnIndex($headers, ['kanji', 'кандзи', 'символ']);
        $levelIndex = $this->findColumnIndex($headers, ['level', 'уровень', 'jlpt', 'jlpt_level']);

        if ($kanjiIndex === false) {
            $this->error("Не найден столбец с кандзи. Доступные столбцы: " . implode(', ', $headers));
            fclose($file);
            return 1;
        }

        if ($levelIndex === false) {
            $this->error("Не найден столбец с уровнем. Доступные столбцы: " . implode(', ', $headers));
            fclose($file);
            return 1;
        }

        $updated = 0;
        $notFound = 0;
        $errors = 0;
        $lineNumber = 1;

        $progressBar = $this->output->createProgressBar();
        $progressBar->start();

        // Читаем данные
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            $lineNumber++;
            
            if (count($row) < max($kanjiIndex, $levelIndex) + 1) {
                continue;
            }

            try {
                $kanji = trim($row[$kanjiIndex] ?? '');
                $levelStr = trim($row[$levelIndex] ?? '');

                if (empty($kanji) || empty($levelStr)) {
                    continue;
                }

                // Преобразуем уровень из формата "N5" в число 5
                $level = $this->parseJLPTLevel($levelStr);
                if ($level === null) {
                    $this->warn("Строка {$lineNumber}: неверный формат уровня '{$levelStr}', пропущена");
                    $errors++;
                    continue;
                }

                // Находим кандзи в базе данных
                $kanjiRecord = Kanji::where('kanji', $kanji)->first();
                
                if ($kanjiRecord) {
                    $kanjiRecord->jlpt_level = $level;
                    $kanjiRecord->save();
                    $updated++;
                } else {
                    $notFound++;
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("Строка {$lineNumber}: ошибка - " . $e->getMessage());
                $errors++;
            }
        }

        fclose($file);
        $progressBar->finish();
        $this->newLine();

        $this->info("\nОбновление завершено!");
        $this->info("Обновлено: {$updated} кандзи");
        $this->info("Не найдено в базе: {$notFound} кандзи");
        if ($errors > 0) {
            $this->warn("Ошибок: {$errors}");
        }

        // Показываем статистику по уровням
        $stats = Kanji::selectRaw('jlpt_level, COUNT(*) as count')
            ->whereNotNull('jlpt_level')
            ->groupBy('jlpt_level')
            ->orderBy('jlpt_level')
            ->get();

        $this->info("\nСтатистика по уровням JLPT:");
        foreach ($stats as $stat) {
            $level = "N{$stat->jlpt_level}";
            $this->info("  {$level}: {$stat->count} кандзи");
        }

        return 0;
    }

    /**
     * Преобразует строку уровня JLPT в число
     * "N5" -> 5, "N4" -> 4, и т.д.
     */
    private function parseJLPTLevel(string $levelStr): ?int
    {
        $levelStr = trim($levelStr);
        
        // Убираем префикс "N" если есть
        if (preg_match('/^N?(\d+)$/i', $levelStr, $matches)) {
            $level = (int)$matches[1];
            if ($level >= 1 && $level <= 5) {
                return $level;
            }
        }
        
        return null;
    }

    /**
     * Helper to find column index by multiple possible headers
     */
    private function findColumnIndex(array $headers, array $possibleNames): false|int
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

