@extends('layouts.app')

@section('title', 'Управление кандзи - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-purple-400">🈳 Управление кандзи</h1>
        <a href="{{ route('admin.kanji.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
            + Добавить кандзи
        </a>
    </div>
    
    <!-- Поиск -->
    <div class="mb-6">
        <form method="GET" action="{{ route('admin.kanji.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Поиск по кандзи, переводу..."
                   class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                Найти
            </button>
            @if(request('search'))
                <a href="{{ route('admin.kanji.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                    Сбросить
                </a>
            @endif
        </form>
    </div>
    
    @if($kanji->count() > 0)
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Кандзи</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Перевод</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">JLPT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Черты</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($kanji as $item)
                        <tr class="hover:bg-gray-700">
                            <td class="px-6 py-4">
                                <div class="text-4xl font-bold text-white japanese-font">{{ $item->kanji }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ $item->translation_ru }}</td>
                            <td class="px-6 py-4">
                                @if($item->jlpt_level)
                                    <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded font-semibold">N{{ $item->jlpt_level }}</span>
                                @else
                                    <span class="text-gray-500 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-400">
                                @if($item->stroke_count)
                                    {{ $item->stroke_count }}
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($item->is_active)
                                    <span class="px-2 py-1 bg-green-600 text-white text-xs rounded">Активен</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-600 text-white text-xs rounded">Неактивен</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.kanji.edit', $item->id) }}" class="text-blue-400 hover:text-blue-300 mr-3">Редактировать</a>
                                <form action="{{ route('admin.kanji.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить этот кандзи?');">
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
            {{ $kanji->links() }}
        </div>
    @else
        <div class="bg-gray-800 rounded-lg p-8 text-center">
            <p class="text-gray-400 text-lg">Кандзи не найдены</p>
            <a href="{{ route('admin.kanji.create') }}" class="inline-block mt-4 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                Добавить первый кандзи
            </a>
        </div>
    @endif
</div>
@endsection

