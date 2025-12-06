@extends('layouts.app')

@section('title', 'Изучение кандзи')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-purple-400 mb-2">📚 Изучение кандзи</h1>
        <p class="text-gray-400">Изучайте кандзи в формате квиза</p>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-purple-800/50 to-indigo-800/50 rounded-xl p-6 border border-purple-700/50">
            <div class="text-3xl font-bold text-purple-300 mb-1">{{ $totalKanji }}</div>
            <div class="text-gray-300">Всего кандзи</div>
        </div>
        <div class="bg-gradient-to-br from-blue-800/50 to-cyan-800/50 rounded-xl p-6 border border-blue-700/50">
            <div class="text-3xl font-bold text-blue-300 mb-1">{{ $studiedKanji }}</div>
            <div class="text-gray-300">Изучается</div>
        </div>
        <div class="bg-gradient-to-br from-green-800/50 to-emerald-800/50 rounded-xl p-6 border border-green-700/50">
            <div class="text-3xl font-bold text-green-300 mb-1">{{ $completedKanji }}</div>
            <div class="text-gray-300">Изучено (10/10)</div>
        </div>
    </div>

    <!-- Кнопка начала квиза -->
    <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <h3 class="text-xl font-bold text-purple-400 mb-4">Начать квиз</h3>
        <form action="{{ route('kanji.quiz') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-gray-300">Количество:</label>
                <input type="number" name="count" value="10" min="1" max="50" 
                       class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white w-24 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-gray-300">Уровень JLPT:</label>
                <select name="jlpt_level" 
                       class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="any">Любой</option>
                    <option value="5">N5 (Начальный)</option>
                    <option value="4">N4 (Базовый)</option>
                    <option value="3">N3 (Средний)</option>
                    <option value="2">N2 (Выше среднего)</option>
                    <option value="1">N1 (Продвинутый)</option>
                </select>
            </div>
            <button type="submit" 
                    class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-purple-500/50 transform hover:scale-105">
                Начать квиз 🎯
            </button>
        </form>
    </div>

    <!-- Список кандзи по уровням JLPT -->
    <div class="space-y-8">
        @foreach($kanjiByLevel as $level => $kanjiList)
            <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                <h2 class="text-2xl font-bold text-purple-400 mb-4 flex items-center gap-2">
                    @if($level === 'Без уровня')
                        📋 {{ $level }}
                    @else
                        🎓 {{ $level }}
                    @endif
                    <span class="text-sm font-normal text-gray-400">({{ $kanjiList->count() }} кандзи)</span>
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach($kanjiList as $item)
                        <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600 hover:border-purple-500 transition-all hover:shadow-lg hover:shadow-purple-500/20">
                            <div class="text-center mb-2">
                                <div class="text-4xl font-bold text-white mb-1">{{ $item['kanji'] }}</div>
                                <div class="text-sm text-gray-400">{{ $item['translation'] }}</div>
                            </div>
                            <div class="mt-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-gray-400">Уровень:</span>
                                    <span class="text-sm font-semibold text-purple-300">{{ $item['level'] }}/10</span>
                                </div>
                                <div class="w-full bg-gray-600 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-purple-500 to-indigo-500 h-2 rounded-full transition-all" 
                                         style="width: {{ ($item['level'] / 10) * 100 }}%"></div>
                                </div>
                                @if($item['last_reviewed_at'])
                                    <div class="text-xs text-gray-500 mt-2">
                                        Последний раз: {{ $item['last_reviewed_at']->format('d.m.Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
