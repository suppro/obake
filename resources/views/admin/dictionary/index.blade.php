@extends('layouts.app')

@section('title', 'Управление словарем - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-purple-400">📖 Управление словарем</h1>
        <a href="{{ route('admin.dictionary.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
            + Добавить слово
        </a>
    </div>
    
    <!-- Поиск -->
    <div class="mb-6">
        <form method="GET" action="{{ route('admin.dictionary.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск по слову, переводу..."
                   class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                Найти
            </button>
            @if(request('search'))
                <a href="{{ route('admin.dictionary.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                    Сбросить
                </a>
            @endif
        </form>
    </div>
    
    @if($words->count() > 0)
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Японское слово</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Чтение</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Перевод (RU)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Перевод (EN)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($words as $word)
                        <tr class="hover:bg-gray-700">
                            <td class="px-6 py-4 text-gray-300 japanese-font text-lg">{{ $word->japanese_word }}</td>
                            <td class="px-6 py-4 text-gray-400">{{ $word->reading }}</td>
                            <td class="px-6 py-4 text-gray-300">{{ Str::limit($word->translation_ru, 50) }}</td>
                            <td class="px-6 py-4 text-gray-400">{{ Str::limit($word->translation_en, 50) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.dictionary.edit', $word->id) }}" class="text-blue-400 hover:text-blue-300 mr-3">Редактировать</a>
                                <form method="POST" action="{{ route('admin.dictionary.destroy', $word->id) }}" class="inline" onsubmit="return confirm('Вы уверены?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            {{ $words->links() }}
        </div>
    @else
        <div class="bg-gray-800 rounded-lg shadow-lg p-8 text-center">
            <p class="text-xl text-gray-400 mb-4">Слова не найдены</p>
            <a href="{{ route('admin.dictionary.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                Добавить первое слово
            </a>
        </div>
    @endif
</div>

@push('styles')
<style>
    .japanese-font {
        font-family: 'Noto Sans JP', sans-serif;
    }
</style>
@endpush
@endsection
