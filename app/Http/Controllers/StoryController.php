<?php

namespace App\Http\Controllers;

use App\Models\GlobalDictionary;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $level = $request->get('level');
        $query = Story::where('is_active', true);

        if ($level) {
            $query->where('level', $level);
        }

        $stories = $query->orderBy('level')->orderBy('created_at', 'desc')->paginate(12);
        
        // Получаем ID прочитанных рассказов текущего пользователя
        $user = Auth::user();
        $readStoryIds = $user->readStories()->get()->pluck('id')->toArray();

        return view('stories.index', compact('stories', 'level', 'readStoryIds'));
    }

    public function show($id)
    {
        $story = Story::findOrFail($id);
        $user = Auth::user();
        
        // Получаем слова из словаря пользователя
        $userWordIds = $user->dictionary()->get()->pluck('id')->toArray();
        
        // Получаем ВСЕ слова из глобального словаря для автоматического определения тултипов
        // JavaScript автоматически найдет все слова, которые встречаются в тексте
        $words = GlobalDictionary::all()->keyBy('id');
        
        // Проверяем, прочитан ли рассказ
        $isRead = $user->readStories()->get()->contains('id', $story->id);

        return view('stories.show', compact('story', 'userWordIds', 'words', 'isRead'));
    }

    public function markAsRead($id)
    {
        $story = Story::findOrFail($id);
        $user = Auth::user();
        
        // Проверяем, не прочитан ли уже рассказ
        if (!$user->readStories()->get()->contains('id', $story->id)) {
            $user->readStories()->attach($story->id, ['read_at' => now()]);
        }
        
        return response()->json(['success' => true, 'message' => 'Рассказ отмечен как прочитанный']);
    }
}
