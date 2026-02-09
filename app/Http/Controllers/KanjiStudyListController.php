<?php

namespace App\Http\Controllers;

use App\Models\KanjiStudyList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanjiStudyListController extends Controller
{
    /**
     * Получить все списки пользователя
     */
    public function index()
    {
        $user = Auth::user();
        
        $lists = $user->kanjiStudyLists()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($list) use ($user) {
                $kanjis = $list->getKanjis();
                
                // Получаем прогресс для всех кандзи в списке
                $progress = \App\Models\KanjiStudyProgress::where('user_id', $user->id)
                    ->whereIn('kanji', $kanjis)
                    ->get()
                    ->keyBy('kanji');
                
                // Получаем информацию о кандзи с прогрессом
                $kanjiWithProgress = collect($kanjis)->map(function ($kanji) use ($progress) {
                    $p = $progress->get($kanji);
                    $level = $p ? $p->level : 0;
                    $progressPercent = min(100, max(0, ($level / 10) * 100));
                    
                    return [
                        'kanji' => $kanji,
                        'level' => $level,
                        'progress_percent' => $progressPercent,
                        'is_completed' => $p ? (bool) $p->is_completed : false,
                    ];
                });
                
                return [
                    'id' => $list->id,
                    'name' => $list->name,
                    'description' => $list->description,
                    'kanji_count' => $list->kanji_count,
                    'repetitions_completed' => $list->repetitions_completed,
                    'kanji_in_list' => $kanjis,
                    'kanji_with_progress' => $kanjiWithProgress->toArray(),
                    // Aggregate progress for the whole list (average percent)
                    'progress_percent' => $kanjiWithProgress->count() ? (int) round($kanjiWithProgress->avg('progress_percent')) : 0,
                    'completed_count' => $kanjiWithProgress->filter(fn($k) => $k['is_completed'])->count(),
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
        $exists = $user->kanjiStudyLists()->where('name', $validated['name'])->exists();
        if ($exists) {
            return response()->json(['error' => 'Список с таким названием уже существует'], 400);
        }

        $list = $user->kanjiStudyLists()->create($validated);

        return response()->json(['success' => true, 'list' => $list]);
    }

    /**
     * Обновить список
     */
    public function update(Request $request, KanjiStudyList $list)
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
            ->kanjiStudyLists()
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
    public function destroy(KanjiStudyList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $list->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Добавить или удалить кандзи из списка
     */
    public function toggleKanji(Request $request, KanjiStudyList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $validated = $request->validate([
            'kanji' => ['required', 'string', 'size:1'],
        ]);

        $kanji = $validated['kanji'];

        if ($list->hasKanji($kanji)) {
            $list->removeKanji($kanji);
            $added = false;
        } else {
            $list->addKanji($kanji);
            $added = true;
        }

        return response()->json([
            'success' => true,
            'added' => $added,
            'kanji_count' => $list->kanji_count,
        ]);
    }

    /**
     * Получить кандзи в списке
     */
    public function getKanjis(KanjiStudyList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $kanjis = $list->getKanjis();

        return response()->json(['success' => true, 'kanjis' => $kanjis]);
    }

    /**
     * Увеличить счётчик представления списка (при успешном завершении квиза списка)
     */
    public function completeRepetition(Request $request, KanjiStudyList $list)
    {
        if ($list->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $list->increment('repetitions_completed');

        return response()->json(['success' => true, 'repetitions_completed' => $list->repetitions_completed]);
    }
}
