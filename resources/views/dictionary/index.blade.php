@extends('layouts.app')

@section('title', 'Мой словарь - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-red-400">📖 Мой словарь</h1>
    
    @if($words->count() > 0)
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="grid gap-4">
                @foreach($words as $word)
                    <div class="bg-gray-700 rounded-lg p-4 flex justify-between items-center">
                        <div>
                            <div class="text-2xl font-bold japanese-font mb-1">{{ $word->japanese_word }}</div>
                            @if($word->reading)
                                <div class="text-gray-400 text-sm mb-1">{{ $word->reading }}</div>
                            @endif
                            <div class="text-gray-300">{{ $word->translation_ru }}</div>
                            @if($word->translation_en)
                                <div class="text-gray-400 text-sm">{{ $word->translation_en }}</div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('dictionary.remove', $word->id) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                                Удалить
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $words->links() }}
            </div>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg shadow-lg p-8 text-center">
            <p class="text-xl text-gray-400 mb-4">Ваш словарь пуст</p>
            <p class="text-gray-500">Начните читать рассказы и добавляйте слова в свой словарь!</p>
            <a href="{{ route('stories.index') }}" class="inline-block mt-4 bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                Перейти к рассказам
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
