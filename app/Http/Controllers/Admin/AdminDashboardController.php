<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalDictionary;
use App\Models\Story;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users_count' => User::count(),
            'stories_count' => Story::count(),
            'words_count' => GlobalDictionary::count(),
            'active_stories' => Story::where('is_active', true)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
