<?php

namespace App\Http\Controllers;

use App\Models\GlobalDictionary;
use App\Models\WordStudyProgress;
use App\Services\JapaneseDictionaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DictionaryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'all'); // all, studying, completed, not_started
        
        $query = $user->dictionary();
        
        // Применяем фильтры
        if ($filter === 'studying') {
            // Слова в процессе изучения (не завершены)
            $studyingWordIds = WordStudyProgress::where('user_id', $user->id)
                ->where('is_completed', false)
                ->pluck('word_id')
                ->toArray();
            $query->whereIn('global_dictionary.id', $studyingWordIds);
        } elseif ($filter === 'completed') {
            // Изученные слова
            $completedWordIds = WordStudyProgress::where('user_id', $user->id)
                ->where('is_completed', true)
                ->pluck('word_id')
                ->toArray();
            $query->whereIn('global_dictionary.id', $completedWordIds);
        } elseif ($filter === 'not_started') {
            // Слова, которые еще не начали изучать
            $studyingWordIds = WordStudyProgress::where('user_id', $user->id)
                ->pluck('word_id')
                ->toArray();
            $query->whereNotIn('global_dictionary.id', $studyingWordIds);
        }
        // 'all' - показываем все слова
        
        $words = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Получаем информацию о прогрессе для каждого слова
        $wordProgresses = WordStudyProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('word_id');
        
        return view('dictionary.index', compact('words', 'filter', 'wordProgresses'));
    }

    public function addWord(Request $request)
    {
        $validated = $request->validate([
            'word_id' => ['nullable', 'exists:global_dictionary,id'],
            'japanese_word' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $wordId = null;
        $word = null;
        
        // Если передан word_id, используем его
        if (isset($validated['word_id'])) {
            $wordId = $validated['word_id'];
            $word = GlobalDictionary::find($wordId);
        } 
        // Если передан japanese_word, ищем или создаем слово
        elseif (isset($validated['japanese_word'])) {
            $japaneseWord = trim($validated['japanese_word']);
            
            // Ищем слово в глобальном словаре
            $word = GlobalDictionary::where('japanese_word', $japaneseWord)
                ->orWhere('reading', $japaneseWord)
                ->first();
            
            // Если слова нет, создаем его из внешнего API
            if (!$word) {
                $dictionaryService = new JapaneseDictionaryService();
                $wordData = $dictionaryService->lookupWordCached($japaneseWord);
                
                if ($wordData) {
                    // Используем слово, которое выбрал пользователь, а не то, что вернул API
                    // API может вернуть другое слово (например, только часть фразы)
                    $word = GlobalDictionary::create([
                        'japanese_word' => $japaneseWord, // Используем слово пользователя
                        'reading' => $wordData['reading'] ?? '',
                        'translation_ru' => $wordData['translation_ru'] ?? '',
                        'translation_en' => $wordData['translation_en'] ?? '',
                        'word_type' => $wordData['word_type'] ?? '',
                        'example_jp' => $wordData['example_jp'] ?? '',
                        'example_ru' => $wordData['example_ru'] ?? '',
                    ]);
                } else {
                    // Если не удалось получить из API, создаем базовую запись
                    $word = GlobalDictionary::create([
                        'japanese_word' => $japaneseWord,
                        'reading' => '',
                        'translation_ru' => '',
                        'translation_en' => '',
                        'word_type' => '',
                        'example_jp' => '',
                        'example_ru' => '',
                    ]);
                }
            }
            
            $wordId = $word->id;
        } else {
            return response()->json(['error' => 'Необходимо указать word_id или japanese_word'], 400);
        }
        
        // Проверяем, что слово еще не добавлено
        $wordExists = $user->dictionary()->get()->pluck('id')->contains($wordId);
        
        if (!$wordExists) {
            $user->dictionary()->attach($wordId);
            
            // Автоматически добавляем слово в изучение
            WordStudyProgress::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'word_id' => $wordId,
                ],
                [
                    'started_at' => Carbon::today(),
                    'days_studied' => 0,
                    'is_completed' => false,
                ]
            );
        }

        // Получаем информацию о слове для ответа, если еще не получена
        if (!$word && $wordId) {
            $word = GlobalDictionary::find($wordId);
        }
        
        $japaneseWordResponse = $word ? $word->japanese_word : ($validated['japanese_word'] ?? null);
        
        return response()->json([
            'success' => true, 
            'word_id' => $wordId,
            'japanese_word' => $japaneseWordResponse
        ]);
    }
    
    /**
     * Получить информацию о слове из внешнего API
     */
    public function lookupWord(Request $request)
    {
        $validated = $request->validate([
            'word' => ['required', 'string'],
        ]);

        $dictionaryService = new JapaneseDictionaryService();
        $wordData = $dictionaryService->lookupWordCached($validated['word']);
        
        if (!$wordData) {
            return response()->json(['error' => 'Слово не найдено'], 404);
        }
        
        return response()->json($wordData);
    }

    public function removeWord(Request $request, $wordId)
    {
        $user = Auth::user();
        $user->dictionary()->detach($wordId);

        return redirect()->route('kanji.index', ['tab' => 'words'])->with('success', 'Слово удалено из словаря');
    }

    public function markAsCompleted(Request $request)
    {
        $validated = $request->validate([
            'word_id' => ['required', 'exists:global_dictionary,id'],
        ]);

        $user = Auth::user();
        
        // Проверяем, что слово в словаре пользователя
        if (!$user->dictionary()->where('global_dictionary.id', $validated['word_id'])->exists()) {
            return response()->json(['error' => 'Слово не найдено в вашем словаре'], 404);
        }

        // Создаем или обновляем прогресс изучения
        $progress = WordStudyProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'word_id' => $validated['word_id'],
            ],
            [
                'started_at' => Carbon::today(),
                'days_studied' => 0,
                'is_completed' => false,
            ]
        );

        // Отмечаем как изученное
        $progress->is_completed = true;
        $progress->completed_at = Carbon::today();
        $progress->days_studied = 10; // Устанавливаем максимальное количество дней
        $progress->last_reviewed_at = Carbon::today();
        $progress->save();

        return response()->json(['success' => true, 'message' => 'Слово отмечено как изученное']);
    }
    
    /**
     * Получить данные слова для модального редактирования (JSON)
     */
    public function getWordData($wordId)
    {
        $user = Auth::user();
        $word = $user->dictionary()->where('global_dictionary.id', $wordId)->firstOrFail();
        return response()->json([
            'id' => $word->id,
            'japanese_word' => $word->japanese_word,
            'reading' => $word->reading ?? '',
            'translation_ru' => $word->translation_ru,
            'translation_en' => $word->translation_en ?? '',
            'word_type' => $word->word_type ?? '',
            'example_jp' => $word->example_jp ?? '',
            'example_ru' => $word->example_ru ?? '',
        ]);
    }

    /**
     * Показать форму редактирования слова (редирект на страницу слов)
     */
    public function edit($wordId)
    {
        return redirect()->route('kanji.index', ['tab' => 'words'])->with('edit_word_id', $wordId);
    }
    
    /**
     * Обновить слово
     */
    public function update(Request $request, $wordId)
    {
        $user = Auth::user();
        
        // Проверяем, что слово в словаре пользователя
        $word = $user->dictionary()->where('global_dictionary.id', $wordId)->firstOrFail();
        
        $validated = $request->validate([
            'japanese_word' => ['required', 'string', 'max:255'],
            'reading' => ['nullable', 'string', 'max:255'],
            'translation_ru' => ['required', 'string'],
            'translation_en' => ['required', 'string'],
            'word_type' => ['nullable', 'string', 'max:255'],
            'example_jp' => ['nullable', 'string'],
            'example_ru' => ['nullable', 'string'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3', 'max:5120'], // Максимум 5MB
            'remove_audio' => ['nullable', 'boolean'],
        ]);
        
        // Обновляем слово
        $word->update([
            'japanese_word' => $validated['japanese_word'],
            'reading' => $validated['reading'] ?? '',
            'translation_ru' => $validated['translation_ru'],
            'translation_en' => $validated['translation_en'],
            'word_type' => $validated['word_type'] ?? '',
            'example_jp' => $validated['example_jp'] ?? '',
            'example_ru' => $validated['example_ru'] ?? '',
        ]);
        
        // Удаляем аудио, если запрошено
        if ($request->has('remove_audio') && $request->remove_audio) {
            if ($word->audio_path && Storage::disk('public')->exists($word->audio_path)) {
                Storage::disk('public')->delete($word->audio_path);
            }
            $word->audio_path = null;
            $word->save();
        }
        
        // Обрабатываем загрузку нового аудио файла
        if ($request->hasFile('audio_file')) {
            // Удаляем старое аудио, если есть
            if ($word->audio_path && Storage::disk('public')->exists($word->audio_path)) {
                Storage::disk('public')->delete($word->audio_path);
            }
            
            $audioFile = $request->file('audio_file');
            $audioPath = 'audio/words/' . $word->id . '.mp3';
            Storage::disk('public')->putFileAs('audio/words', $audioFile, $word->id . '.mp3');
            $word->audio_path = $audioPath;
            $word->save();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Слово успешно обновлено']);
        }
        return redirect()->route('kanji.index', ['tab' => 'words'])->with('success', 'Слово успешно обновлено');
    }
}
