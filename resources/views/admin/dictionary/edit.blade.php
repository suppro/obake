@extends('layouts.app')

@section('title', 'Редактировать слово - Obake')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-purple-400">Редактировать слово</h1>
    
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">
        <form method="POST" action="{{ route('admin.dictionary.update', $word->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label for="japanese_word" class="block text-gray-300 mb-2">Японское слово</label>
                <input type="text" id="japanese_word" name="japanese_word" value="{{ old('japanese_word', $word->japanese_word) }}" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500 japanese-font text-lg">
                @error('japanese_word')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="reading" class="block text-gray-300 mb-2">
                    Чтение (фуригана) 
                    <span class="text-yellow-400 text-sm">*рекомендуется</span>
                </label>
                <input type="text" id="reading" name="reading" value="{{ old('reading', $word->reading) }}" placeholder="例: よむ (для 読む)"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                <p class="text-gray-400 text-sm mt-1">Необходимо для отображения фуриганы над словами в рассказах</p>
                @error('reading')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="translation_ru" class="block text-gray-300 mb-2">Перевод на русский</label>
                <textarea id="translation_ru" name="translation_ru" rows="3" required
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">{{ old('translation_ru', $word->translation_ru) }}</textarea>
                @error('translation_ru')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="translation_en" class="block text-gray-300 mb-2">Перевод на английский</label>
                <textarea id="translation_en" name="translation_en" rows="3" required
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">{{ old('translation_en', $word->translation_en) }}</textarea>
                @error('translation_en')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="word_type" class="block text-gray-300 mb-2">
                    Тип слова 
                    <span class="text-yellow-400 text-sm">*для спряжений</span>
                </label>
                <select id="word_type" name="word_type" 
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                    <option value="">-- Выберите тип (опционально) --</option>
                    <option value="Глагол" {{ old('word_type', $word->word_type) == 'Глагол' ? 'selected' : '' }}>Глагол</option>
                    <option value="い-прилагательное" {{ old('word_type', $word->word_type) == 'い-прилагательное' ? 'selected' : '' }}>い-прилагательное (например: 楽しい, 可愛い)</option>
                    <option value="な-прилагательное" {{ old('word_type', $word->word_type) == 'な-прилагательное' ? 'selected' : '' }}>な-прилагательное (например: 好き, きれい)</option>
                    <option value="Существительное" {{ old('word_type', $word->word_type) == 'Существительное' ? 'selected' : '' }}>Существительное</option>
                    <option value="Другое" {{ old('word_type', $word->word_type) == 'Другое' ? 'selected' : '' }}>Другое</option>
                </select>
                <p class="text-gray-400 text-sm mt-1">
                    Для глаголов и прилагательных система автоматически определит все спряженные формы (読む → 読みました, 楽しい → 楽しかった и т.д.)
                </p>
                @error('word_type')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="example_jp" class="block text-gray-300 mb-2">Пример на японском (опционально)</label>
                <textarea id="example_jp" name="example_jp" rows="2"
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500 japanese-font">{{ old('example_jp', $word->example_jp) }}</textarea>
            </div>
            
            <div class="mb-4">
                <label for="example_ru" class="block text-gray-300 mb-2">Пример на русском (опционально)</label>
                <textarea id="example_ru" name="example_ru" rows="2"
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">{{ old('example_ru', $word->example_ru) }}</textarea>
            </div>
            
            <div class="mb-6">
                <label for="audio_file" class="block text-gray-300 mb-2">
                    Аудио файл (MP3)
                    <span class="text-yellow-400 text-sm">*опционально</span>
                </label>
                @if($word->audio_path)
                    <div class="mb-2 p-3 bg-gray-700 rounded-lg">
                        <p class="text-gray-300 text-sm mb-2">Текущий аудио файл:</p>
                        <audio controls class="w-full">
                            <source src="{{ Storage::disk('public')->url($word->audio_path) }}" type="audio/mpeg">
                            Ваш браузер не поддерживает аудио элемент.
                        </audio>
                        <label class="flex items-center mt-2">
                            <input type="checkbox" name="remove_audio" value="1"
                                   class="rounded bg-gray-700 border-gray-600 text-red-600">
                            <span class="ml-2 text-red-400 text-sm">Удалить текущий аудио файл</span>
                        </label>
                    </div>
                @endif
                <input type="file" id="audio_file" name="audio_file" accept="audio/mpeg,audio/mp3"
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                <p class="text-gray-400 text-sm mt-1">
                    Формат: MP3. Максимальный размер: 5MB. 
                    Файл будет сохранен в: <code class="text-purple-400">storage/app/public/audio/words/</code>
                    @if($word->audio_path)
                        <br>Загрузка нового файла заменит существующий.
                    @endif
                </p>
                @error('audio_file')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                    Сохранить
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
