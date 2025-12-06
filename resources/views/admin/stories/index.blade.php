@extends('layouts.app')

@section('title', 'Управление рассказами - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold text-purple-400">📚 Управление рассказами</h1>
        <a href="{{ route('admin.stories.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
            + Создать рассказ
        </a>
    </div>
    
    @if($stories->count() > 0)
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Название</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Уровень</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($stories as $story)
                        <tr class="hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300">{{ $story->id }}</td>
                            <td class="px-6 py-4 text-gray-300">{{ $story->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-red-600 text-white px-2 py-1 rounded text-sm">{{ $story->level }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($story->is_active)
                                    <span class="bg-green-600 text-white px-2 py-1 rounded text-sm">Активен</span>
                                @else
                                    <span class="bg-gray-600 text-white px-2 py-1 rounded text-sm">Неактивен</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.stories.edit', $story->id) }}" class="text-blue-400 hover:text-blue-300 mr-3">Редактировать</a>
                                <form method="POST" action="{{ route('admin.stories.destroy', $story->id) }}" class="inline" onsubmit="return confirm('Вы уверены?')">
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
            {{ $stories->links() }}
        </div>
    @else
        <div class="bg-gray-800 rounded-lg shadow-lg p-8 text-center">
            <p class="text-xl text-gray-400 mb-4">Рассказы не найдены</p>
            <a href="{{ route('admin.stories.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                Создать первый рассказ
            </a>
        </div>
    @endif
</div>
@endsection
