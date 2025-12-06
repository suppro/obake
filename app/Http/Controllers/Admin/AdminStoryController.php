<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminStoryController extends Controller
{
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
            'audio_file' => ['nullable', 'file', 'mimes:mp3', 'max:10240'], // Максимум 10MB
        ]);

        $story = Story::create($validated);

        // Обрабатываем загрузку аудио файла
        if ($request->hasFile('audio_file')) {
            $audioFile = $request->file('audio_file');
            $audioPath = 'audio/stories/' . $story->id . '.mp3';
            Storage::disk('public')->putFileAs('audio/stories', $audioFile, $story->id . '.mp3');
            $story->audio_path = $audioPath;
            $story->save();
        }

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
            'audio_file' => ['nullable', 'file', 'mimes:mp3', 'max:10240'], // Максимум 10MB
            'remove_audio' => ['nullable', 'boolean'],
        ]);

        $story->update($validated);

        // Удаляем аудио, если запрошено
        if ($request->has('remove_audio') && $request->remove_audio) {
            if ($story->audio_path && Storage::disk('public')->exists($story->audio_path)) {
                Storage::disk('public')->delete($story->audio_path);
            }
            $story->audio_path = null;
            $story->save();
        }

        // Обрабатываем загрузку нового аудио файла
        if ($request->hasFile('audio_file')) {
            // Удаляем старое аудио, если есть
            if ($story->audio_path && Storage::disk('public')->exists($story->audio_path)) {
                Storage::disk('public')->delete($story->audio_path);
            }
            
            $audioFile = $request->file('audio_file');
            $audioPath = 'audio/stories/' . $story->id . '.mp3';
            Storage::disk('public')->putFileAs('audio/stories', $audioFile, $story->id . '.mp3');
            $story->audio_path = $audioPath;
            $story->save();
        }

        return redirect()->route('admin.stories.index')->with('success', 'Рассказ успешно обновлен');
    }

    public function destroy($id)
    {
        $story = Story::findOrFail($id);
        $story->delete();

        return redirect()->route('admin.stories.index')->with('success', 'Рассказ успешно удален');
    }
}
