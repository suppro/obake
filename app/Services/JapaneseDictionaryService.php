<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JapaneseDictionaryService
{
    /**
     * Получить информацию о слове из Jisho API
     * 
     * @param string $word Японское слово
     * @return array|null Массив с информацией о слове или null
     */
    public function lookupWord(string $word): ?array
    {
        try {
            // Jisho API endpoint
            $url = 'https://jisho.org/api/v1/search/words';
            
            // Делаем запрос к API
            $response = Http::timeout(5)->get($url, [
                'keyword' => $word
            ]);
            
            if (!$response->successful()) {
                Log::warning('Jisho API request failed', [
                    'word' => $word,
                    'status' => $response->status()
                ]);
                return null;
            }
            
            $data = $response->json();
            
            // Проверяем, есть ли результаты
            if (!isset($data['data']) || empty($data['data'])) {
                return null;
            }
            
            // Берем первый результат (обычно это базовая форма)
            $result = $data['data'][0];
            
            // Извлекаем основную информацию
            $japanese = $result['japanese'][0]['word'] ?? $result['japanese'][0]['reading'] ?? $word;
            $reading = $result['japanese'][0]['reading'] ?? '';
            
            // Если слово и чтение одинаковые, используем запрошенное слово
            if ($japanese === $reading) {
                $japanese = $word;
            }
            
            // Получаем переводы (только английские, русский перевод добавляется вручную)
            $senses = $result['senses'] ?? [];
            $translationsEn = [];
            
            // Ограничиваем до 2 первых переводов для экономии места
            $maxTranslations = 2;
            
            foreach ($senses as $sense) {
                if (count($translationsEn) >= $maxTranslations) {
                    break;
                }
                
                $englishMeanings = $sense['english_definitions'] ?? [];
                foreach ($englishMeanings as $meaning) {
                    if (count($translationsEn) >= $maxTranslations) {
                        break;
                    }
                    
                    // Пропускаем дубликаты
                    if (in_array($meaning, $translationsEn)) {
                        continue;
                    }
                    
                    $translationsEn[] = $meaning;
                }
            }
            
            // Объединяем переводы (максимум 2)
            $translationEn = !empty($translationsEn) ? implode(', ', array_slice($translationsEn, 0, $maxTranslations)) : '';
            $translationRu = ''; // Русский перевод добавляется вручную при редактировании
            
            // Определяем тип слова
            $partsOfSpeech = [];
            foreach ($senses as $sense) {
                $partsOfSpeech = array_merge($partsOfSpeech, $sense['parts_of_speech'] ?? []);
            }
            $wordType = !empty($partsOfSpeech) ? implode(', ', array_unique($partsOfSpeech)) : '';
            
            // Получаем примеры использования (если есть в API)
            $exampleJp = '';
            $exampleRu = '';
            
            // Jisho API не предоставляет примеры напрямую, но можно попробовать получить из других источников
            // Пока оставляем пустым - пользователь может добавить вручную
            
            return [
                'japanese_word' => $japanese,
                'reading' => $reading,
                'translation_ru' => $translationRu,
                'translation_en' => $translationEn,
                'word_type' => $wordType,
                'example_jp' => $exampleJp,
                'example_ru' => $exampleRu,
            ];
            
        } catch (\Exception $e) {
            Log::error('Error fetching word from Jisho API', [
                'word' => $word,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Получить информацию о слове с кешированием
     * 
     * @param string $word Японское слово
     * @return array|null
     */
    public function lookupWordCached(string $word): ?array
    {
        $cacheKey = 'jisho_word_' . md5($word);
        
        return cache()->remember($cacheKey, now()->addDays(30), function () use ($word) {
            return $this->lookupWord($word);
        });
    }
}

