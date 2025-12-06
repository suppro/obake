<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalDictionary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDictionaryController extends Controller
{
    public function index(Request $request)
    {
        $query = GlobalDictionary::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('japanese_word', 'like', "%{$search}%")
                  ->orWhere('translation_ru', 'like', "%{$search}%")
                  ->orWhere('translation_en', 'like', "%{$search}%");
            });
        }

        $words = $query->orderBy('created_at', 'desc')->paginate(30);

        return view('admin.dictionary.index', compact('words'));
    }

    public function create()
    {
        return view('admin.dictionary.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'japanese_word' => ['required', 'string', 'max:255'],
            'reading' => ['nullable', 'string', 'max:255'],
            'translation_ru' => ['required', 'string'],
            'translation_en' => ['required', 'string'],
            'word_type' => ['nullable', 'string', 'max:255'],
            'example_ru' => ['nullable', 'string'],
            'example_jp' => ['nullable', 'string'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3', 'max:5120'], // Максимум 5MB
        ]);

        $word = GlobalDictionary::create($validated);

        // Обрабатываем загрузку аудио файла
        if ($request->hasFile('audio_file')) {
            $audioFile = $request->file('audio_file');
            $audioPath = 'audio/words/' . $word->id . '.mp3';
            Storage::disk('public')->putFileAs('audio/words', $audioFile, $word->id . '.mp3');
            $word->audio_path = $audioPath;
            $word->save();
        }

        return redirect()->route('admin.dictionary.index')->with('success', 'Слово успешно добавлено');
    }

    public function edit($id)
    {
        $word = GlobalDictionary::findOrFail($id);
        return view('admin.dictionary.edit', compact('word'));
    }

    public function update(Request $request, $id)
    {
        $word = GlobalDictionary::findOrFail($id);

        $validated = $request->validate([
            'japanese_word' => ['required', 'string', 'max:255'],
            'reading' => ['nullable', 'string', 'max:255'],
            'translation_ru' => ['required', 'string'],
            'translation_en' => ['required', 'string'],
            'word_type' => ['nullable', 'string', 'max:255'],
            'example_ru' => ['nullable', 'string'],
            'example_jp' => ['nullable', 'string'],
        ]);

        $word->update($validated);

        return redirect()->route('admin.dictionary.index')->with('success', 'Слово успешно обновлено');
    }

    public function destroy($id)
    {
        $word = GlobalDictionary::findOrFail($id);
        $word->delete();

        return redirect()->route('admin.dictionary.index')->with('success', 'Слово успешно удалено');
    }
}
