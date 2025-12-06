<?php

namespace App\Http\Controllers;

use App\Models\GlobalDictionary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DictionaryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $words = $user->dictionary()->orderBy('created_at', 'desc')->paginate(20);
        
        return view('dictionary.index', compact('words'));
    }

    public function addWord(Request $request)
    {
        $validated = $request->validate([
            'word_id' => ['required', 'exists:global_dictionary,id'],
        ]);

        $user = Auth::user();
        
        // Проверяем, что слово еще не добавлено
        $wordExists = $user->dictionary()->get()->pluck('id')->contains($validated['word_id']);
        
        if (!$wordExists) {
            $user->dictionary()->attach($validated['word_id']);
        }

        return response()->json(['success' => true]);
    }

    public function removeWord(Request $request, $wordId)
    {
        $user = Auth::user();
        $user->dictionary()->detach($wordId);

        return redirect()->back()->with('success', 'Слово удалено из словаря');
    }
}
