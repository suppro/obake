<?php

namespace App\Http\Controllers;

use App\Models\GlobalDictionary;
use App\Models\Story;
use App\Models\WordStudyProgress;
use App\Services\JapaneseDictionaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $level = $request->get('level');
        $query = Story::where('is_active', true);

        if ($level) {
            $query->where('level', $level);
        }

        $stories = $query->orderBy('level')->orderBy('created_at', 'desc')->paginate(12);
        
        // Получаем ID прочитанных рассказов текущего пользователя
        $user = Auth::user();
        $readStoryIds = $user->readStories()->get()->pluck('id')->toArray();

        return view('stories.index', compact('stories', 'level', 'readStoryIds'));
    }

    public function show($id)
    {
        $story = Story::findOrFail($id);
        $user = Auth::user();
        
        // Получаем слова из словаря пользователя (только для подсветки)
        $userWordIds = $user->dictionary()->get()->pluck('id')->toArray();
        
        // Получаем только слова пользователя из глобального словаря для подсветки
        // Информация о словах при наведении будет браться из внешнего API
        $words = GlobalDictionary::whereIn('id', $userWordIds)->get()->keyBy('id');
        
        // Получаем информацию о прогрессе изучения слов
        $wordProgress = WordStudyProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('word_id')
            ->map(function($progress) {
                return [
                    'days_studied' => $progress->days_studied,
                    'is_completed' => $progress->is_completed,
                ];
            });
        
        // Проверяем, прочитан ли рассказ
        $isRead = $user->readStories()->get()->contains('id', $story->id);

        return view('stories.show', compact('story', 'userWordIds', 'words', 'isRead', 'wordProgress'));
    }

    public function markAsRead($id)
    {
        $story = Story::findOrFail($id);
        $user = Auth::user();
        
        // Проверяем, не прочитан ли уже рассказ
        if (!$user->readStories()->get()->contains('id', $story->id)) {
            $user->readStories()->attach($story->id, ['read_at' => now()]);
        }
        
        return response()->json(['success' => true, 'message' => 'Рассказ отмечен как прочитанный']);
    }

    /**
     * Получить чтение слова из Jisho API (для фуриганы)
     */
    public function getWordReading(Request $request, JapaneseDictionaryService $dictionaryService)
    {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:50'],
        ]);

        $word = $validated['word'];
        
        try {
            // Прямой запрос к Jisho API для получения всех вариантов
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://jisho.org/api/v1/search/words', [
                'keyword' => $word
            ]);
            
            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'reading' => null
                ]);
            }
            
            $data = $response->json();
            
            if (!isset($data['data']) || empty($data['data'])) {
                return response()->json([
                    'success' => false,
                    'reading' => null
                ]);
            }
            
            // Ищем наиболее подходящее чтение
            // Приоритет: точное совпадение слова, затем первое чтение
            foreach ($data['data'] as $result) {
                if (!isset($result['japanese']) || empty($result['japanese'])) {
                    continue;
                }
                
                foreach ($result['japanese'] as $japanese) {
                    $japaneseWord = $japanese['word'] ?? $japanese['reading'] ?? '';
                    $reading = $japanese['reading'] ?? '';
                    
                    // Если слово точно совпадает, используем это чтение
                    if ($japaneseWord === $word && !empty($reading)) {
                        return response()->json([
                            'success' => true,
                            'reading' => $reading
                        ]);
                    }
                }
            }
            
            // Если точного совпадения нет, берем первое чтение из первого результата
            $firstResult = $data['data'][0];
            if (isset($firstResult['japanese'][0]['reading']) && !empty($firstResult['japanese'][0]['reading'])) {
                return response()->json([
                    'success' => true,
                    'reading' => $firstResult['japanese'][0]['reading']
                ]);
            }
            
            return response()->json([
                'success' => false,
                'reading' => null
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching word reading from Jisho', [
                'word' => $word,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'reading' => null
            ]);
        }
    }

}
