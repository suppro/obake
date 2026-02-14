<?php

namespace App\Http\Controllers;

use App\Models\ReadingQuizWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingQuizWordController extends Controller
{
    /**
     * Получить все слова для чтения пользователя
     */
    public function index()
    {
        $user = Auth::user();
        $words = ReadingQuizWord::where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $words]);
    }

    /**
     * Создать новое слово для чтения
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'japanese_word' => ['required', 'string', 'max:255'],
            'reading' => ['required', 'string', 'max:255'],
            'translation_ru' => ['required', 'string', 'max:1000'],
            'translation_en' => ['nullable', 'string', 'max:1000'],
            'word_type' => ['nullable', 'string', 'max:100'],
            'example_ru' => ['nullable', 'string', 'max:1000'],
            'example_jp' => ['nullable', 'string', 'max:1000'],
            'audio_path' => ['nullable', 'string', 'max:255'],
        ]);

        $word = Auth::user()->readingQuizWords()->create($validated);

        return response()->json(['success' => true, 'word' => $word]);
    }

    /**
     * Обновить слово для чтения
     */
    public function update(Request $request, ReadingQuizWord $word)
    {
        if ($word->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        $validated = $request->validate([
            'japanese_word' => ['required', 'string', 'max:255'],
            'reading' => ['required', 'string', 'max:255'],
            'translation_ru' => ['required', 'string', 'max:1000'],
            'translation_en' => ['nullable', 'string', 'max:1000'],
            'word_type' => ['nullable', 'string', 'max:100'],
            'example_ru' => ['nullable', 'string', 'max:1000'],
            'example_jp' => ['nullable', 'string', 'max:1000'],
            'audio_path' => ['nullable', 'string', 'max:255'],
        ]);

        $word->update($validated);

        return response()->json(['success' => true, 'word' => $word]);
    }

    /**
     * Удалить слово для чтения
     */
    public function destroy(ReadingQuizWord $word)
    {
        if ($word->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }

        // Удаляем слово из всех списков
        $word->lists()->detach();
        
        // Удаляем само слово
        $word->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Импортировать слова для чтения из CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $csv = array_map('str_getcsv', file($path));
        
        $user = Auth::user();
        $imported = 0;

        // Пропускаем первую строку (заголовки)
        foreach (array_slice($csv, 1) as $row) {
            if (count($row) < 3) continue;

            ReadingQuizWord::create([
                'user_id' => $user->id,
                'japanese_word' => $row[0] ?? '',
                'reading' => $row[1] ?? '',
                'translation_ru' => $row[2] ?? '',
                'translation_en' => $row[3] ?? null,
                'word_type' => $row[4] ?? null,
                'example_ru' => $row[5] ?? null,
                'example_jp' => $row[6] ?? null,
                'audio_path' => $row[7] ?? null,
            ]);
            $imported++;
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
        ]);
    }
}
