<?php

namespace App\Console\Commands;

use App\Models\Kanji;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class TranslateKanjiEnglishToRussian extends Command
{
    protected $signature = 'kanji:translate-en-ru
        {--dry-run : Не сохранять изменения, только показать статистику}
        {--count-only : Только посчитать, сколько записей требует перевода}
        {--limit=0 : Ограничить количество обработанных кандзи (0 = без лимита)}
        {--only=both : translation|description|both}
        {--sleep=0 : Пауза между запросами (в миллисекундах)}';

    protected $description = 'Перевести английские translation_ru/description в таблице kanji на русский (через MyMemory) с кэшированием';

    public function handle(TranslationService $translator): int
    {
        $only = (string) $this->option('only');
        if (!in_array($only, ['translation', 'description', 'both'], true)) {
            $this->error('Опция --only должна быть: translation|description|both');
            return 1;
        }

        $limit = (int) $this->option('limit');
        $sleepMs = max(0, (int) $this->option('sleep'));

        $query = Kanji::query()->where('is_active', true);

        $total = (int) $query->count();
        $this->info("Активных кандзи: {$total}");

        $needs = 0;
        $processed = 0;
        $changed = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->orderBy('id')->chunkById(200, function ($rows) use ($translator, $only, $limit, $sleepMs, &$needs, &$processed, &$changed, $bar) {
            foreach ($rows as $kanji) {
                if ($limit > 0 && $processed >= $limit) {
                    $bar->finish();
                    return false;
                }

                $processed++;

                $translation = (string) ($kanji->translation_ru ?? '');
                $description = (string) ($kanji->description ?? '');

                $translationNeeds = ($only === 'translation' || $only === 'both') && $this->looksEnglish($translation);
                $descriptionNeeds = ($only === 'description' || $only === 'both') && $this->looksEnglish($description);

                if (!$translationNeeds && !$descriptionNeeds) {
                    $bar->advance();
                    continue;
                }

                $needs++;

                $newTranslation = $translation;
                $newDescription = $description;

                if ($translationNeeds) {
                    $newTranslation = $translator->translateEnToRu($translation);
                    if ($sleepMs > 0) usleep($sleepMs * 1000);
                }

                if ($descriptionNeeds) {
                    $newDescription = $this->translateExamples($translator, $description, $sleepMs);
                }

                $isChanged = ($newTranslation !== $translation) || ($newDescription !== $description);

                if ($isChanged) {
                    $changed++;

                    if (!$this->option('dry-run') && !$this->option('count-only')) {
                        $kanji->translation_ru = $newTranslation;
                        $kanji->description = $newDescription;
                        $kanji->save();
                    }
                }

                $bar->advance();
            }

            return true;
        });

        $bar->finish();
        $this->newLine();

        $this->info("Нужно перевести (обнаружено англ.): {$needs}");
        if ($this->option('count-only')) {
            $this->info('Режим count-only: изменений не вносили.');
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->info("Dry-run: потенциальных изменений: {$changed}");
            return 0;
        }

        $this->info("Изменено записей: {$changed}");
        return 0;
    }

    private function looksEnglish(string $text): bool
    {
        $text = trim($text);
        if ($text === '') return false;

        // Есть кириллица — не считаем англ.
        if (preg_match('/[А-Яа-яЁё]/u', $text)) return false;

        // Есть латиница — считаем англ.
        return (bool) preg_match('/[A-Za-z]/', $text);
    }

    /**
     * Переводит только английские части примеров вида:
     * "単語 (たんご) - word; 例 (...) - example"
     * Переводим то, что справа от " - ".
     */
    private function translateExamples(TranslationService $translator, string $description, int $sleepMs): string
    {
        $description = trim($description);
        if ($description === '') return $description;

        $parts = preg_split('/\s*;\s*/u', $description) ?: [$description];
        $out = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;

            // Ищем " - " разделитель
            $split = preg_split('/\s-\s/u', $part, 2);
            if (!$split || count($split) < 2) {
                // Если нет формата, переводим целиком как fallback
                $translated = $translator->translateEnToRu($part);
                if ($sleepMs > 0) usleep($sleepMs * 1000);
                $out[] = $translated;
                continue;
            }

            [$left, $right] = $split;
            $left = trim($left);
            $right = trim($right);

            if ($this->looksEnglish($right)) {
                $right = $translator->translateEnToRu($right);
                if ($sleepMs > 0) usleep($sleepMs * 1000);
            }

            $out[] = $left . ' - ' . $right;
        }

        return implode('; ', $out);
    }
}


