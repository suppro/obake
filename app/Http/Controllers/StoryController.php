<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $level = $request->get('level');
        $query = Story::where('is_active', true);

        if ($level) {
            $query->where('level', $level);
        }

        $stories = $query->orderBy('level')->orderBy('created_at', 'desc')->paginate(12);

        return view('stories.index', compact('stories', 'level'));
    }

    public function show($id)
    {
        $story = Story::with('words')->findOrFail($id);
        $user = Auth::user();
        
        // Получаем слова из словаря пользователя
        $userWordIds = $user->dictionary()->pluck('id')->toArray();
        
        // Получаем все слова с переводами
        $words = $story->words()->get()->keyBy('id');

        return view('stories.show', compact('story', 'userWordIds', 'words'));
    }
}
