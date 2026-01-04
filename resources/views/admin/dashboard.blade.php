@extends('layouts.app')

@section('title', 'Админ панель - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-purple-400">👑 Админ панель</h1>
    
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="text-3xl font-bold text-purple-400">{{ $stats['users_count'] }}</div>
            <div class="text-gray-400">Пользователей</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="text-3xl font-bold text-purple-400">{{ $stats['stories_count'] }}</div>
            <div class="text-gray-400">Рассказов</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="text-3xl font-bold text-purple-400">{{ $stats['words_count'] }}</div>
            <div class="text-gray-400">Слов в словаре</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="text-3xl font-bold text-purple-400">{{ $stats['active_stories'] }}</div>
            <div class="text-gray-400">Активных рассказов</div>
        </div>
    </div>
    
    <div class="grid md:grid-cols-2 gap-6">
        <a href="{{ route('admin.stories.index') }}" class="bg-gray-800 rounded-lg p-6 hover:bg-gray-700 transition shadow-lg">
            <h2 class="text-2xl font-bold mb-2 text-purple-400">📚 Управление рассказами</h2>
            <p class="text-gray-400">Создавайте, редактируйте и удаляйте рассказы</p>
        </a>
        
        <a href="{{ route('admin.kanji.index') }}" class="bg-gray-800 rounded-lg p-6 hover:bg-gray-700 transition shadow-lg">
            <h2 class="text-2xl font-bold mb-2 text-purple-400">🈳 Управление кандзи</h2>
            <p class="text-gray-400">Добавляйте и редактируйте кандзи для изучения</p>
        </a>
    </div>
</div>
@endsection
