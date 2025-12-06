<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalDictionary;
use Illuminate\Http\Request;

class AdminDictionaryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

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
        ]);

        GlobalDictionary::create($validated);

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
