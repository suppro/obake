@extends('layouts.app')

@section('title', 'Добавить кандзи - Obake')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-purple-400">➕ Добавить кандзи</h1>
    </div>
    
    <div class="bg-gray-800 rounded-lg shadow-lg p-6">
        <form action="{{ route('admin.kanji.store') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="kanji" class="block text-gray-300 mb-2">Кандзи <span class="text-red-400">*</span></label>
                <input type="text" name="kanji" id="kanji" value="{{ old('kanji') }}" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-2xl japanese-font focus:outline-none focus:border-purple-500"
                       placeholder="今" maxlength="10">
                <p class="mt-1 text-sm text-gray-400">Введите символ кандзи (один или несколько символов)</p>
                @error('kanji')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label for="translation_ru" class="block text-gray-300 mb-2">Перевод на русский <span class="text-red-400">*</span></label>
                <input type="text" name="translation_ru" id="translation_ru" value="{{ old('translation_ru') }}" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500"
                       placeholder="сейчас">
                @error('translation_ru')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label for="alternative_translations" class="block text-gray-300 mb-2">Альтернативные переводы (синонимы)</label>
                <textarea name="alternative_translations" id="alternative_translations" rows="2"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500"
                       placeholder="теперь, в данный момент, ныне (через запятую, точку с запятой или с новой строки)">{{ old('alternative_translations') }}</textarea>
                <p class="mt-1 text-sm text-gray-400">Укажите синонимы или альтернативные переводы, которые также будут приниматься как правильные ответы в квизе.</p>
                @error('alternative_translations')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label for="jlpt_level" class="block text-gray-300 mb-2">Уровень JLPT</label>
                <select name="jlpt_level" id="jlpt_level"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                    <option value="">-- Без уровня --</option>
                    <option value="5" {{ old('jlpt_level') == '5' ? 'selected' : '' }}>N5 (Начальный)</option>
                    <option value="4" {{ old('jlpt_level') == '4' ? 'selected' : '' }}>N4 (Базовый)</option>
                    <option value="3" {{ old('jlpt_level') == '3' ? 'selected' : '' }}>N3 (Средний)</option>
                    <option value="2" {{ old('jlpt_level') == '2' ? 'selected' : '' }}>N2 (Выше среднего)</option>
                    <option value="1" {{ old('jlpt_level') == '1' ? 'selected' : '' }}>N1 (Продвинутый)</option>
                </select>
                @error('jlpt_level')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label for="description" class="block text-gray-300 mb-2">Описание</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500"
                          placeholder="Дополнительная информация о кандзи (опционально)">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label for="stroke_count" class="block text-gray-300 mb-2">Количество черт</label>
                <input type="number" name="stroke_count" id="stroke_count" value="{{ old('stroke_count') }}" min="1" max="50"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500"
                       placeholder="4">
                @error('stroke_count')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500">
                    <span class="ml-2 text-gray-300">Активен (будет доступен для изучения)</span>
                </label>
            </div>
            
            <div class="flex gap-4">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                    Сохранить
                </button>
                <a href="{{ route('admin.kanji.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

