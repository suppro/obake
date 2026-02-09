<?php

namespace App\Http\Controllers;

use App\Models\WordStudyList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WordStudyListController extends Controller
{
    /**
     * Получить все списки пользователя
     */
    public function index()
    {
        $user = Auth::user();
        
        $lists = $user->wordStudyLists()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($list) use ($user) {
                $wordIds = $list->getWords();
                
                // Получаем прогресс для всех слов в списке
                $progress = \App\Models\WordStudyProgress::where('user_id', $user->id)
                    ->whereIn('word_id', $wordIds)
                    ->get()
                    ->keyBy('word_id');
                
                // Получаем информацию о словах с прогрессом
                $wordsWithProgress = collect($wordIds)->map(function ($wordId) use ($progress) {
                    $p = $progress->get($wordId);
                    $level = $p ? $p->level : 0;
                    $progressPercent = min(100, max(0, ($level / 10) * 100));
                    
                    return [
                        'word_id' => $wordId,
                        'level' => $level,
                        'progress_percent' => $progressPercent,
                        'is_completed' => $p ? (bool) $p->is_completed : false,
                    ];
                });
                
                return [
                    'id' => $list->id,
                    'name' => $list->name,
                    'description' => $list->description,
                    'word_count' => $list->word_count,
                    'repetitions_completed' => $list->repetitions_completed,
                    'word_ids_in_list' => $wordIds,
                    'words_with_progress' => $wordsWithProgress->toArray(),
                    // Aggregate progress for the whole list (average percent)
                    'progress_percent' => $wordsWithProgress->count() ? (int) round($wordsWithProgress->avg('progress_percent')) : 0,
                    'completed_count' => $wordsWithProgress->filter(fn($w) => $w['is_completed'])->count(),
                ];
            })
            ->values();
        
        return response()->json(['success' => true, 'lists' => $lists->toArray()]);
    }

    /**
     * Создать новый список
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        
        // Проверяем что у пользователя нет списка с таким названием
        $exists = $user->wordStudyLists()->where('name', $validated['name'])->exists();
        if ($exists) {
            return response()->json(['error' => 'Список с таким названием уже существует'], 400);
        }

        $list = $user->wordStudyLists()->create($validated);

        return response()->json(['success' => true, 'list' => $list]);
    }

    /**
     * Обновить список
     */
    public function update(Request $request, WordStudyList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        // Проверяем что нет другого списка с таким названием
        $exists = Auth::user()
            ->wordStudyLists()
            ->where('name', $validated['name'])
            ->where('id', '!=', $list->id)
            ->exists();
        
        if ($exists) {
            return response()->json(['error' => 'Список с таким названием уже существует'], 400);
        }

        $list->update($validated);

        return response()->json(['success' => true, 'list' => $list]);
    }

    /**
     * Удалить список
     */
    public function destroy(WordStudyList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $list->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Добавить или удалить слово из списка
     */
    public function toggleWord(Request $request, WordStudyList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $validated = $request->validate([
            'word_id' => ['required', 'integer', 'exists:global_dictionary,id'],
        ]);

        $wordId = $validated['word_id'];

        if ($list->hasWord($wordId)) {
            $list->removeWord($wordId);
            $added = false;
        } else {
            $list->addWord($wordId);
            $added = true;
        }

        return response()->json([
            'success' => true,
            'added' => $added,
            'word_count' => $list->word_count,
        ]);
    }

    /**
     * Получить слова в списке
     */
    public function getWords(WordStudyList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $words = $list->getWords();

        return response()->json(['success' => true, 'words' => $words]);
    }

    /**
     * Увеличить счётчик повтора списка (при успешном завершении квиза списка)
     */
    public function completeRepetition(Request $request, WordStudyList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $list->increment('repetitions_completed');

        return response()->json(['success' => true, 'repetitions_completed' => $list->repetitions_completed]);
    }
}
