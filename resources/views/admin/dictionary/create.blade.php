@extends('layouts.app')

@section('title', 'Добавить слово - Obake')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-purple-400">Добавить слово</h1>
    
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">
        <form method="POST" action="{{ route('admin.dictionary.store') }}">
            @csrf
            
            <div class="mb-4">
                <label for="japanese_word" class="block text-gray-300 mb-2">Японское слово</label>
                <input type="text" id="japanese_word" name="japanese_word" value="{{ old('japanese_word') }}" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500 japanese-font text-lg">
                @error('japanese_word')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="reading" class="block text-gray-300 mb-2">Чтение (фуригана)</label>
                <input type="text" id="reading" name="reading" value="{{ old('reading') }}"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                @error('reading')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="translation_ru" class="block text-gray-300 mb-2">Перевод на русский</label>
                <textarea id="translation_ru" name="translation_ru" rows="3" required
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">{{ old('translation_ru') }}</textarea>
                @error('translation_ru')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="translation_en" class="block text-gray-300 mb-2">Перевод на английский</label>
                <textarea id="translation_en" name="translation_en" rows="3" required
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">{{ old('translation_en') }}</textarea>
                @error('translation_en')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="word_type" class="block text-gray-300 mb-2">Тип слова (опционально)</label>
                <input type="text" id="word_type" name="word_type" value="{{ old('word_type') }}" placeholder="например: существительное, глагол"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                @error('word_type')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="example_jp" class="block text-gray-300 mb-2">Пример на японском (опционально)</label>
                <textarea id="example_jp" name="example_jp" rows="2"
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500 japanese-font">{{ old('example_jp') }}</textarea>
            </div>
            
            <div class="mb-6">
                <label for="example_ru" class="block text-gray-300 mb-2">Пример на русском (опционально)</label>
                <textarea id="example_ru" name="example_ru" rows="2"
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">{{ old('example_ru') }}</textarea>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                    Добавить
                </button>
                <a href="{{ route('admin.dictionary.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .japanese-font {
        font-family: 'Noto Sans JP', sans-serif;
    }
</style>
@endpush
@endsection
