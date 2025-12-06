@extends('layouts.app')

@section('title', 'Редактировать рассказ - Obake')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-purple-400">Редактировать рассказ</h1>
    
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">
        <form method="POST" action="{{ route('admin.stories.update', $story->id) }}">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="title" class="block text-gray-300 mb-2">Название</label>
                <input type="text" id="title" name="title" value="{{ old('title', $story->title) }}" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                @error('title')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="level" class="block text-gray-300 mb-2">Уровень</label>
                <select id="level" name="level" required
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                    <option value="N5" {{ old('level', $story->level) == 'N5' ? 'selected' : '' }}>N5</option>
                    <option value="N4" {{ old('level', $story->level) == 'N4' ? 'selected' : '' }}>N4</option>
                    <option value="N3" {{ old('level', $story->level) == 'N3' ? 'selected' : '' }}>N3</option>
                    <option value="N2" {{ old('level', $story->level) == 'N2' ? 'selected' : '' }}>N2</option>
                    <option value="N1" {{ old('level', $story->level) == 'N1' ? 'selected' : '' }}>N1</option>
                </select>
                @error('level')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-gray-300 mb-2">Описание</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">{{ old('description', $story->description) }}</textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="content" class="block text-gray-300 mb-2">Содержание (японский текст)</label>
                <textarea id="content" name="content" rows="15" required
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500"
                          style="font-family: 'Noto Sans JP', sans-serif;">{{ old('content', $story->content) }}</textarea>
                @error('content')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $story->is_active) ? 'checked' : '' }}
                           class="rounded bg-gray-700 border-gray-600 text-purple-600">
                    <span class="ml-2 text-gray-300">Активен</span>
                </label>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                    Сохранить
                </button>
                <a href="{{ route('admin.stories.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
