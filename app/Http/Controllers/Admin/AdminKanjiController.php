<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kanji;
use Illuminate\Http\Request;

class AdminKanjiController extends Controller
{
    public function index(Request $request)
    {
        $query = Kanji::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kanji', 'like', "%{$search}%")
                  ->orWhere('translation_ru', 'like', "%{$search}%");
            });
        }

        $kanji = $query->orderBy('created_at', 'desc')->paginate(30);

        return view('admin.kanji.index', compact('kanji'));
    }

    public function create()
    {
        return view('admin.kanji.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kanji' => ['required', 'string', 'max:10', 'unique:kanji,kanji'],
            'translation_ru' => ['required', 'string', 'max:255'],
            'jlpt_level' => ['nullable', 'integer', 'in:1,2,3,4,5'],
            'description' => ['nullable', 'string'],
            'stroke_count' => ['nullable', 'integer', 'min:1', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Kanji::create($validated);

        return redirect()->route('admin.kanji.index')->with('success', 'Кандзи успешно добавлен');
    }

    public function edit($id)
    {
        $kanji = Kanji::findOrFail($id);
        return view('admin.kanji.edit', compact('kanji'));
    }

    public function update(Request $request, $id)
    {
        $kanji = Kanji::findOrFail($id);

        $validated = $request->validate([
            'kanji' => ['required', 'string', 'max:10', 'unique:kanji,kanji,' . $id],
            'translation_ru' => ['required', 'string', 'max:255'],
            'jlpt_level' => ['nullable', 'integer', 'in:1,2,3,4,5'],
            'description' => ['nullable', 'string'],
            'stroke_count' => ['nullable', 'integer', 'min:1', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $kanji->update($validated);

        return redirect()->route('admin.kanji.index')->with('success', 'Кандзи успешно обновлен');
    }

    public function destroy($id)
    {
        $kanji = Kanji::findOrFail($id);
        $kanji->delete();

        return redirect()->route('admin.kanji.index')->with('success', 'Кандзи успешно удален');
    }
}
