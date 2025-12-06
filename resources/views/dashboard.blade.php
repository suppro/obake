@extends('layouts.app')

@section('title', 'Главная - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-purple-400">Добро пожаловать, {{ auth()->user()->name }}! 👻</h1>
    
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        <a href="{{ route('stories.index') }}" class="bg-gray-800 rounded-lg p-6 hover:bg-gray-700 transition shadow-lg">
            <h2 class="text-2xl font-bold mb-2 text-purple-400">📚 Рассказы</h2>
            <p class="text-gray-400">Читайте рассказы на японском языке разных уровней сложности</p>
        </a>
        
        <a href="{{ route('dictionary.index') }}" class="bg-gray-800 rounded-lg p-6 hover:bg-gray-700 transition shadow-lg">
            <h2 class="text-2xl font-bold mb-2 text-purple-400">📖 Мой словарь</h2>
            <p class="text-gray-400">Просматривайте и изучайте слова из вашего личного словаря</p>
        </a>
    </div>
    
    <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
        <h2 class="text-2xl font-bold mb-4">Ваша статистика</h2>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-400">{{ auth()->user()->dictionary()->count() }}</div>
                <div class="text-gray-400">Слов в словаре</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-400">{{ auth()->user()->readStories()->count() }}</div>
                <div class="text-gray-400">Прочитано рассказов</div>
            </div>
            <div class="text-center">
                @php
                    $readStories = auth()->user()->readStories()->get();
                    $levels = $readStories->pluck('level')->unique()->sort();
                    $currentLevel = $levels->isNotEmpty() ? $levels->last() : 'N5';
                @endphp
                <div class="text-3xl font-bold text-purple-400">{{ $currentLevel }}</div>
                <div class="text-gray-400">Текущий уровень</div>
            </div>
        </div>
    </div>
</div>
@endsection
