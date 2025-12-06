@extends('layouts.app')

@section('title', 'Рассказы - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-purple-400">📚 Рассказы</h1>
    
    <!-- Фильтр по уровням -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('stories.index') }}" 
           class="px-4 py-2 rounded-lg {{ !$level ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            Все
        </a>
        @foreach(['N5', 'N4', 'N3', 'N2', 'N1'] as $lvl)
            <a href="{{ route('stories.index', ['level' => $lvl]) }}" 
               class="px-4 py-2 rounded-lg {{ $level === $lvl ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
                {{ $lvl }}
            </a>
        @endforeach
    </div>
    
    @if($stories->count() > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($stories as $story)
                <a href="{{ route('stories.show', $story->id) }}" 
                   class="bg-gray-800 rounded-lg p-6 hover:bg-gray-700 transition shadow-lg relative">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="inline-block bg-purple-600 text-white px-3 py-1 rounded text-sm font-semibold">
                            {{ $story->level }}
                        </span>
                        @if(in_array($story->id, $readStoryIds))
                            <span class="inline-block bg-green-600 text-white px-3 py-1 rounded text-sm font-semibold">
                                ✓ Прочитано
                            </span>
                        @endif
                    </div>
                    <h2 class="text-xl font-bold mb-2 text-purple-400">{{ $story->title }}</h2>
                    @if($story->description)
                        <p class="text-gray-400 text-sm line-clamp-3">{{ $story->description }}</p>
                    @endif
                </a>
            @endforeach
        </div>
        
        <div class="mt-6">
            {{ $stories->links() }}
        </div>
    @else
        <div class="bg-gray-800 rounded-lg shadow-lg p-8 text-center">
            <p class="text-xl text-gray-400">Рассказы не найдены</p>
        </div>
    @endif
</div>
@endsection
