<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TranslationService
{
    /**
     * Перевод EN -> RU через MyMemory (без ключа).
     * Важно: сервис публичный и может ограничивать частоту — поэтому кэшируем.
     */
    public function translateEnToRu(string $text, int $timeoutSeconds = 10): string
    {
        $text = trim($text);
        if ($text === '') return $text;

        // Уже есть кириллица — считаем, что перевод не нужен
        if (preg_match('/[А-Яа-яЁё]/u', $text)) {
            return $text;
        }

        // Нет латиницы — тоже не переводим (например, только японские символы/цифры)
        if (!preg_match('/[A-Za-z]/', $text)) {
            return $text;
        }

        $cacheKey = 'translate_en_ru_' . md5($text);

        return Cache::remember($cacheKey, now()->addDays(180), function () use ($text, $timeoutSeconds) {
            try {
                $resp = Http::timeout($timeoutSeconds)->get('https://api.mymemory.translated.net/get', [
                    'q' => $text,
                    'langpair' => 'en|ru',
                ]);

                if (!$resp->successful()) {
                    return $text;
                }

                $data = $resp->json();
                $translated = $data['responseData']['translatedText'] ?? null;
                if (!is_string($translated) || trim($translated) === '') {
                    return $text;
                }

                // MyMemory иногда возвращает HTML entities
                $translated = html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $translated = trim($translated);

                return $translated !== '' ? $translated : $text;
            } catch (\Throwable $e) {
                return $text;
            }
        });
    }
}


