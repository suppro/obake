<?php

namespace App\Http\Controllers;

use App\Models\GlobalDictionary;
use App\Models\WordStudyProgress;
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
            'word_id' => ['required', 'exists:global_dictionary,id'],
        ]);

        $user = Auth::user();
        
        // Проверяем, что слово еще не добавлено
        $wordExists = $user->dictionary()->get()->pluck('id')->contains($validated['word_id']);
        
        if (!$wordExists) {
            $user->dictionary()->attach($validated['word_id']);
        }

        return response()->json(['success' => true]);
    }

    public function removeWord(Request $request, $wordId)
    {
        $user = Auth::user();
        $user->dictionary()->detach($wordId);

        return redirect()->back()->with('success', 'Слово удалено из словаря');
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
    
}
