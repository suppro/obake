<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;

class AdminStoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $stories = Story::orderBy('level')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.stories.index', compact('stories'));
    }

    public function create()
    {
        return view('admin.stories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'level' => ['required', 'in:N5,N4,N3,N2,N1'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $story = Story::create($validated);

        return redirect()->route('admin.stories.index')->with('success', 'Рассказ успешно создан');
    }

    public function edit($id)
    {
        $story = Story::findOrFail($id);
        return view('admin.stories.edit', compact('story'));
    }

    public function update(Request $request, $id)
    {
        $story = Story::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'level' => ['required', 'in:N5,N4,N3,N2,N1'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $story->update($validated);

        return redirect()->route('admin.stories.index')->with('success', 'Рассказ успешно обновлен');
    }

    public function destroy($id)
    {
        $story = Story::findOrFail($id);
        $story->delete();

        return redirect()->route('admin.stories.index')->with('success', 'Рассказ успешно удален');
    }
}
