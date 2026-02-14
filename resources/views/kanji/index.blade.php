@extends('layouts.app')

@section('title', 'Изучение кандзи')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-bold text-purple-400 mb-2">📚 Изучение кандзи и слов</h1>
            <p class="text-gray-400">Изучайте кандзи и слова из вашего словаря в формате квиза</p>
        </div>
        <!-- Вкладки: Кандзи / Слова -->
        <div class="flex rounded-xl bg-gray-800/50 border border-gray-700 p-1">
            <a href="{{ route('kanji.index', array_filter(['jlpt_level' => $jlptLevel ?? null, 'search' => $search ?? null])) }}" class="px-5 py-2.5 rounded-lg font-semibold transition {{ request('tab') !== 'words' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white' }}">
                Кандзи
            </a>
            <a href="{{ route('kanji.index', array_merge(request()->only(['jlpt_level', 'search']), ['tab' => 'words', 'word_search' => $wordSearch ?? '', 'word_type' => $wordTypeFilter ?? ''])) }}" class="px-5 py-2.5 rounded-lg font-semibold transition {{ request('tab') === 'words' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white' }}">
                Слова
            </a>
        </div>
    </div>

    <!-- Блок: Кандзи -->
    <div id="panel-kanji" class="tab-panel {{ request('tab') !== 'words' ? '' : 'hidden' }}">
    <!-- Фильтр по JLPT -->
    <div class="mb-6 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <h3 class="text-xl font-bold text-purple-400 mb-4">Уровень JLPT</h3>
        @php
            $currentJlpt = $jlptLevel ?? '5';
            $searchParam = $search ?? null;
        @endphp
        <div class="flex flex-wrap gap-2">
            @foreach([5,4,3,2,1] as $lvl)
                <a href="{{ route('kanji.index', array_filter(['jlpt_level' => (string)$lvl, 'search' => $searchParam])) }}"
                   class="px-4 py-2 rounded-lg font-semibold border transition-all
                          {{ (string)$currentJlpt === (string)$lvl ? 'bg-purple-600 border-purple-500 text-white' : 'bg-gray-700 border-gray-600 text-gray-200 hover:bg-gray-600' }}">
                    N{{ $lvl }}
                </a>
            @endforeach
            <a href="{{ route('kanji.index', array_filter(['jlpt_level' => 'any', 'search' => $searchParam])) }}"
               class="px-4 py-2 rounded-lg font-semibold border transition-all
                      {{ (string)$currentJlpt === 'any' ? 'bg-purple-600 border-purple-500 text-white' : 'bg-gray-700 border-gray-600 text-gray-200 hover:bg-gray-600' }}">
                Любой
            </a>
        </div>
        <p class="text-gray-400 text-sm mt-2">По умолчанию показан уровень N5. Переключайся кнопками.</p>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
        <div class="bg-gradient-to-br from-yellow-800/50 to-amber-800/50 rounded-xl p-6 border border-yellow-700/50">
            <div class="text-3xl font-bold text-yellow-300 mb-1">{{ $dueKanji ?? 0 }}</div>
            <div class="text-gray-300">Пора повторить</div>
        </div>
    </div>

    <!-- Поиск -->
    <div class="mb-6 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <h3 class="text-xl font-bold text-purple-400 mb-4">Поиск кандзи</h3>
        <div class="flex items-center gap-4">
            <input type="text" 
                   id="kanji-search" 
                   placeholder="Введите перевод или чтение (например: замок, しろ, じょう)" 
                   value=""
                   class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <button type="button" 
                    id="kanji-search-clear"
                    class="bg-gray-600 hover:bg-gray-500 text-white font-semibold px-6 py-3 rounded-lg transition-all hidden"
                    onclick="document.getElementById('kanji-search').value = ''; document.getElementById('kanji-search').dispatchEvent(new Event('input'));">Очистить</button>
        </div>
        <p class="text-gray-400 text-sm mt-2">Поиск работает по переводу на русский и чтению (хирагана/ромадзи). Результаты выводятся в реальном времени при вводе.</p>
        
        <!-- Контейнер для результатов поиска -->
        <div id="kanji-search-results" class="hidden mt-4 bg-gray-900/50 rounded-lg p-4 border border-gray-600">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <tbody id="kanji-search-results-tbody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Кнопка начала квиза -->
    <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <h3 class="text-xl font-bold text-purple-400 mb-4">Начать квиз</h3>
        <form action="{{ route('kanji.quiz') }}" method="GET" class="space-y-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-gray-300">Список кандзи:</label>
                    <select name="list_id" 
                           id="quiz-list-select"
                           class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Все кандзи</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-gray-300">Количество:</label>
                    <input type="number" name="count" value="10" min="1" max="50" 
                           class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white w-24 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-gray-300">Уровень JLPT:</label>
                    <select name="jlpt_level" 
                           class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="5" {{ (string)($jlptLevel ?? '5') === '5' ? 'selected' : '' }}>N5 (Начальный)</option>
                        <option value="4" {{ (string)($jlptLevel ?? '5') === '4' ? 'selected' : '' }}>N4 (Базовый)</option>
                        <option value="3" {{ (string)($jlptLevel ?? '5') === '3' ? 'selected' : '' }}>N3 (Средний)</option>
                        <option value="2" {{ (string)($jlptLevel ?? '5') === '2' ? 'selected' : '' }}>N2 (Выше среднего)</option>
                        <option value="1" {{ (string)($jlptLevel ?? '5') === '1' ? 'selected' : '' }}>N1 (Продвинутый)</option>
                        <option value="any" {{ (string)($jlptLevel ?? '5') === 'any' ? 'selected' : '' }}>Любой</option>
                    </select>
                </div>
            </div>
            <!-- Настройка: включить выбор кандзи для квиза -->
            <div class="flex items-center gap-3">
                <label class="flex items-center cursor-pointer select-none">
                    <input type="checkbox"
                           id="toggle-selection-mode"
                           class="w-5 h-5 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500 focus:ring-2"
                           {{ ($useKanjiSelection ?? false) ? 'checked' : '' }}>
                    <span class="ml-2 text-gray-300">✅ Включить выбор конкретных кандзи для квиза</span>
                </label>
                <span class="text-gray-500 text-sm">Появятся галочки в таблице</span>
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="force_input_mode" value="1" 
                           class="w-5 h-5 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500 focus:ring-2">
                    <span class="ml-2 text-gray-300">✍️ Только ручной ввод (без вариантов ответа)</span>
                </label>
            </div>
            <div>
                <button type="submit" 
                        class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-purple-500/50 transform hover:scale-105">
                    Начать квиз 🎯
                </button>
            </div>
        </form>
    </div>

    <!-- Управление списками кандзи -->
    <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-purple-400">📋 Мои списки кандзи</h3>
            <button id="btn-create-list" 
                    class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-semibold px-4 py-2 rounded-lg transition-all">
                + Создать список
            </button>
        </div>
        
        <div id="kanji-lists-container">
            <p class="text-gray-400 text-sm">Загрузка...</p>
        </div>
        <script>
            // Lightweight kanji lists loader (runs before main script)
            (function(){
                try {
                    fetch('{{ route("kanji-lists.index") }}', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        const container = document.getElementById('kanji-lists-container');
                        if (!container) return;
                        if (!data.lists || data.lists.length === 0) {
                            container.innerHTML = '<p class="text-gray-400">Нет списков. Создайте первый список!</p>';
                            return;
                        }
                        let html = '<div class="space-y-6">';
                        data.lists.forEach(list => {
                            html += `
                                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-semibold text-white text-lg">${list.name}</h4>
                                                ${list.multiple_choice_only ? '<span class="bg-green-900/30 text-green-400 text-xs px-2 py-1 rounded border border-green-700/50">🎯 Только выбор</span>' : ''}
                                            </div>
                                            <p class="text-gray-400 text-sm">${list.description || 'Без описания'}</p>
                                            <p class="text-gray-500 text-xs mt-1">${list.kanji_count} кандзи</p>
                                        </div>
                                        <div class="flex gap-2 flex-shrink-0">
                                            <a href="{{ route('kanji.quiz') }}?list_id=${list.id}" class="bg-purple-600 hover:bg-purple-500 px-3 py-2 rounded text-sm text-white">▶️ Квиз</a>
                                        </div>
                                    </div>
                                </div>`;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                    })
                    .catch(() => {
                        const container = document.getElementById('kanji-lists-container');
                        if (container) container.innerHTML = '<p class="text-red-400">Ошибка загрузки списков</p>';
                    });
                } catch (e) { console.error(e); }
            })();
        </script>
    </div>

    <!-- Список кандзи по уровням JLPT -->
    <div class="space-y-8" id="kanji-list">
        @foreach($sortedKanjiByLevel as $level => $kanjiList)
            <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                <h2 class="text-2xl font-bold text-purple-400 mb-4 flex items-center gap-2">
                    @if($level === 'Без уровня')
                        📋 {{ $level }}
                    @else
                        🎓 {{ $level }}
                    @endif
                    <span class="text-sm font-normal text-gray-400">({{ $kanjiList->count() }} кандзи)</span>
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <tbody>
                            @php
                                $chunkSize = 10; // Количество кандзи в строке
                                $chunks = $kanjiList->chunk($chunkSize);
                            @endphp
                            @foreach($chunks as $chunk)
                                <tr>
                                    @foreach($chunk as $item)
                                        <td class="kanji-item bg-gray-700/50 border border-gray-600 hover:border-purple-500 transition-all hover:shadow-lg hover:shadow-purple-500/20 cursor-pointer text-center align-middle"
                                            data-kanji="{{ $item['kanji'] }}"
                                            data-translation="{{ htmlspecialchars($item['translation'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                            data-reading="{{ htmlspecialchars($item['reading'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                            data-level="{{ $item['level'] }}"
                                            data-jlpt-level="{{ $item['jlpt_level'] ?? '' }}"
                                            data-last-reviewed="{{ $item['last_reviewed_at'] ? $item['last_reviewed_at']->format('d.m.Y') : '' }}"
                                            data-next-review="{{ $item['next_review_at'] ? $item['next_review_at']->format('d.m.Y H:i') : '' }}"
                                            data-is-completed="{{ $item['is_completed'] ? '1' : '0' }}"
                                            data-is-selected="{{ $item['is_selected_for_study'] ? '1' : '0' }}"
                                            data-image-path="{{ $item['image_path'] ?? '' }}"
                                            data-mnemonic="{{ htmlspecialchars($item['mnemonic'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                            data-description="{{ htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                            onclick="openKanjiModal(this)"
                                            style="width: 120px; height: 120px; padding: 1rem; vertical-align: middle; position: relative;">
                                            <!-- Чекбокс для выбора в изучение -->
                                            <div class="kanji-selection-overlay" style="position: absolute; top: 4px; left: 4px; z-index: 10; {{ ($useKanjiSelection ?? false) ? '' : 'display:none;' }}">
                                                <input type="checkbox" 
                                                       class="kanji-study-checkbox w-5 h-5 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500 focus:ring-2 cursor-pointer"
                                                       data-kanji="{{ $item['kanji'] }}"
                                                       {{ $item['is_selected_for_study'] ? 'checked' : '' }}
                                                       onclick="event.stopPropagation(); toggleKanjiStudySelection(this);"
                                                       title="Выбрать для изучения в квизе">
                                            </div>
                                            <div style="display: flex; flex-direction: column; height: 100%; justify-content: space-between; align-items: center;">
                                                <div class="text-6xl font-bold text-white" style="font-family: 'Noto Sans JP', sans-serif; line-height: 1.2; display: flex; align-items: center; justify-content: center; flex: 1;">{{ $item['kanji'] }}</div>
                                                <!-- Прогресс-бар -->
                                                @php
                                                    $level = (int)($item['level'] ?? 0);
                                                    $progressPercent = min(100, max(0, ($level / 10) * 100));
                                                @endphp
                                                <div style="width: 90%; height: 6px; background-color: rgba(75, 85, 99, 0.5); border-radius: 9999px; overflow: hidden; position: relative; margin-top: 0.5rem;">
                                                    <div style="height: 100%; width: {{ $progressPercent }}%; background: linear-gradient(90deg, #a855f7 0%, #6366f1 100%); border-radius: 9999px; transition: width 0.3s ease; box-shadow: 0 0 4px rgba(168, 85, 247, 0.4);"></div>
                                                </div>
                                            </div>
                                        </td>
                                    @endforeach
                                    @for($i = $chunk->count(); $i < $chunkSize; $i++)
                                        <td class="border border-transparent p-4"></td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
    </div>
    <!-- Конец блока Кандзи -->

    <!-- Блок: Слова -->
    <div id="panel-words" class="tab-panel {{ request('tab') === 'words' ? '' : 'hidden' }}">
        <div class="mb-6 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h3 class="text-xl font-bold text-purple-400 mb-4">Поиск слов</h3>
            <form method="GET" action="{{ route('kanji.index') }}" id="word-search-form">
                <input type="hidden" name="tab" value="words">
                <div class="flex flex-wrap items-center gap-4">
                    <input type="text"
                           name="word_search"
                           id="word-search-input"
                           placeholder="Слово, чтение или перевод..."
                           value="{{ $wordSearch ?? '' }}"
                           class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <select name="word_type" class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Все типы</option>
                        @foreach($wordTypes ?? [] as $wt)
                            <option value="{{ $wt }}" {{ ($wordTypeFilter ?? '') === $wt ? 'selected' : '' }}>{{ $wt }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-6 py-3 rounded-lg transition-all">
                        Найти
                    </button>
                    @if(($wordSearch ?? '') !== '' || ($wordTypeFilter ?? '') !== '')
                        <a href="{{ route('kanji.index', ['tab' => 'words']) }}" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold px-6 py-3 rounded-lg transition-all">
                            Очистить
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Управление списками слов -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-purple-400">Списки слов для изучения</h3>
                <button type="button" id="btn-create-word-list" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-5 py-2.5 rounded-lg transition-all">
                    ＋ Создать список
                </button>
            </div>
            
            <div id="word-lists-container">
                <p class="text-gray-400 text-sm">Загрузка...</p>
            </div>
            <script>
                // Lightweight word lists loader (runs before main script)
                (function(){
                    try {
                        fetch('{{ route("word-lists.index") }}', { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => {
                            const container = document.getElementById('word-lists-container');
                            if (!container) return;
                            if (!data.lists || data.lists.length === 0) {
                                container.innerHTML = '<p class="text-gray-400">Нет списков. Создайте первый список!</p>';
                                return;
                            }
                            let html = '<div class="space-y-6">';
                            data.lists.forEach(list => {
                                html += `
                                    <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                                        <div class="flex items-center justify-between mb-4">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h4 class="font-semibold text-white text-lg">${list.name}</h4>
                                                    ${list.multiple_choice_only ? '<span class="bg-green-900/30 text-green-400 text-xs px-2 py-1 rounded border border-green-700/50">🎯 Только выбор</span>' : ''}
                                                </div>
                                                <p class="text-gray-400 text-sm">${list.description || 'Без описания'}</p>
                                                <p class="text-gray-500 text-xs mt-1">${list.word_count} слов</p>
                                            </div>
                                            <div class="flex gap-2 flex-shrink-0">
                                                <a href="{{ route('kanji.word-quiz') }}?list_id=${list.id}" class="bg-purple-600 hover:bg-purple-500 px-3 py-2 rounded text-sm text-white">▶️ Квиз</a>
                                            </div>
                                        </div>
                                    </div>`;
                            });
                            html += '</div>';
                            container.innerHTML = html;
                        })
                        .catch(() => {
                            const container = document.getElementById('word-lists-container');
                            if (container) container.innerHTML = '<p class="text-red-400">Ошибка загрузки списков</p>';
                        });
                    } catch (e) { console.error(e); }
                })();
            </script>
        </div>

        <div class="mb-6 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h3 class="text-xl font-bold text-purple-400 mb-4">Начать квиз по словам</h3>
            <form action="{{ route('kanji.word-quiz') }}" method="GET" class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-gray-300">Количество:</label>
                    <input type="number" name="count" value="10" min="1" max="50" class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white w-24 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-gray-300">Тип слова:</label>
                    <select name="word_type" class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Все</option>
                        @foreach($wordTypes ?? [] as $wt)
                            <option value="{{ $wt }}">{{ $wt }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-6 py-3 rounded-lg transition-all">
                    Начать квиз по словам
                </button>
            </form>
        </div>

        <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <h3 class="text-xl font-bold text-purple-400">Список слов</h3>
                <button type="button" id="btn-add-word" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-5 py-2.5 rounded-lg transition-all">
                    ＋ Добавить слово
                </button>
            </div>
            @if(isset($wordsList) && $wordsList->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="words-list-body">
                    @foreach($wordsList as $w)
                        <div class="word-card bg-gray-700/50 border border-gray-600 rounded-xl p-4 hover:border-purple-500/50 transition-all flex flex-col" data-word-id="{{ $w['id'] }}" data-word="{{ $w['japanese_word'] }}" data-reading="{{ $w['reading'] }}" data-translation="{{ e($w['translation_ru']) }}">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="min-w-0 flex-1">
                                    <div class="text-2xl font-bold text-white japanese-font truncate" style="font-family: 'Noto Sans JP', sans-serif;">{{ $w['japanese_word'] }}</div>
                                    @if($w['reading'])
                                        <div class="text-sm text-gray-400 japanese-font">{{ $w['reading'] }}</div>
                                    @endif
                                </div>
                                <div class="flex gap-1 flex-shrink-0">
                                    <button type="button" class="word-edit-btn text-blue-400 hover:text-blue-300 p-1 rounded" title="Редактировать" data-word-id="{{ $w['id'] }}">✏️</button>
                                    @if($w['in_user_dictionary'])
                                    <form method="POST" action="{{ route('dictionary.remove', $w['id']) }}" class="inline word-remove-form" onsubmit="return confirm('Удалить слово из словаря?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 p-1 rounded" title="Удалить">🗑️</button>
                                    </form>
                                    @else
                                    <button type="button" class="word-add-btn text-green-400 hover:text-green-300 p-1 rounded" title="Добавить" data-word-id="{{ $w['id'] }}">＋</button>
                                    @endif
                                </div>
                            </div>
                            <div class="text-gray-300 text-sm mb-3 line-clamp-2 flex-1">{{ $w['translation_ru'] }}</div>
                            <div class="mt-auto">
                                <div style="width: 100%; height: 6px; background-color: rgba(75, 85, 99, 0.5); border-radius: 9999px; overflow: hidden; position: relative;">
                                    <div style="height: 100%; width: {{ $w['progress_percent'] }}%; background: linear-gradient(90deg, #a855f7 0%, #6366f1 100%); border-radius: 9999px; transition: width 0.3s ease; box-shadow: 0 0 4px rgba(168, 85, 247, 0.4);"></div>
                                </div>
                                <span class="text-xs text-gray-500 mt-0.5">{{ (int)$w['progress_percent'] }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400">В вашем словаре пока нет слов. Нажмите «Добавить слово», чтобы добавить первое.</p>
            @endif
        </div>
    </div>

    <!-- Модальное окно: добавить слово -->
    <div id="modal-add-word" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" aria-hidden="true">
        <div class="bg-gray-800 rounded-2xl shadow-2xl w-full border border-gray-700 mx-4 max-h-[90vh] overflow-y-auto" style="max-width:680px;">
            <div class="p-6 border-b border-gray-700 flex justify-between items-center sticky top-0 bg-gray-800 z-10">
                <h3 class="text-xl font-bold text-purple-400">Добавить слово</h3>
                <button type="button" id="modal-add-word-close" class="text-gray-400 hover:text-white text-2xl">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-gray-300 mb-1">Японское слово *</label>
                    <input type="text" id="add-japanese-word" placeholder="私 или わたし" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white japanese-font focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-gray-300 mb-1">Чтение</label>
                    <input type="text" id="add-reading" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-gray-300 mb-1">Перевод (RU)</label>
                    <input type="text" id="add-translation-ru" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-gray-300 mb-1">Перевод (EN)</label>
                    <input type="text" id="add-translation-en" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-gray-300 mb-1">Тип слова</label>
                    <select id="add-word-type" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">— не указан —</option>
                        @foreach($wordTypes ?? [] as $wt)
                            <option value="{{ $wt }}">{{ $wt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 mb-1">Пример (JP)</label>
                    <input type="text" id="add-example-jp" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-gray-300 mb-1">Пример (RU)</label>
                    <input type="text" id="add-example-ru" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="flex gap-3">
                    <button type="button" id="add-word-submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-5 py-2.5 rounded-lg transition">Добавить</button>
                    <button type="button" id="add-word-cancel" class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2.5 rounded-lg transition">Отмена</button>
                </div>
                <p id="add-word-message" class="mt-2 text-sm hidden"></p>
            </div>
        </div>
    </div>

    <!-- Модальное окно: редактировать слово -->
    <div id="modal-edit-word" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" aria-hidden="true">
        <div class="bg-gray-800 rounded-2xl shadow-2xl w-full border border-gray-700 mx-4" style="max-width:680px;">
            <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                <h3 class="text-xl font-bold text-purple-400">Редактировать слово</h3>
                <button type="button" id="modal-edit-word-close" class="text-gray-400 hover:text-white text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <input type="hidden" id="edit-word-id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-300 mb-1">Японское слово *</label>
                        <input type="text" id="edit-japanese-word" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white japanese-font focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-1">Чтение (фуригана)</label>
                        <input type="text" id="edit-reading" placeholder="わたし" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-1">Перевод на русский *</label>
                        <textarea id="edit-translation-ru" rows="2" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-1">Перевод на английский</label>
                        <textarea id="edit-translation-en" rows="2" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-1">Тип слова</label>
                        <select id="edit-word-type" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">— не указан —</option>
                            <option value="Глагол">Глагол</option>
                            <option value="い-прилагательное">い-прилагательное</option>
                            <option value="な-прилагательное">на-прилагательное</option>
                            <option value="Существительное">Существительное</option>
                            <option value="Другое">Другое</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-1">Пример (JP)</label>
                        <input type="text" id="edit-example-jp" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-1">Пример (RU)</label>
                        <input type="text" id="edit-example-ru" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" id="edit-word-submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-5 py-2.5 rounded-lg transition">Сохранить</button>
                    <button type="button" id="edit-word-cancel" class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2.5 rounded-lg transition">Отмена</button>
                </div>
                <p id="edit-word-message" class="mt-2 text-sm hidden"></p>
            </div>
        </div>
    </div>
    <!-- Конец блока Слова -->
</div>

<!-- Модальное окно с деталями кандзи -->
<div id="kanji-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div class="bg-gray-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-gray-700">
        <!-- Заголовок -->
        <div class="flex justify-between items-center p-6 border-b border-gray-700 sticky top-0 bg-gray-800 z-10">
            <h3 class="text-3xl font-bold text-purple-400" id="modal-kanji" style="font-family: 'Noto Sans JP', sans-serif;"></h3>
            <button id="close-modal" class="text-gray-400 hover:text-white text-3xl font-bold transition-colors w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-700">&times;</button>
        </div>
        
        <!-- Основной контент -->
        <div class="p-6">
            <div class="flex flex-row gap-6">
                <!-- Левая часть: Изображение или кнопка добавления -->
                <div class="flex-shrink-0 flex items-center justify-center flex-col" style="width: 320px; min-width: 320px;">
                    <div id="modal-image-container" class="hidden w-full">
                        <div class="relative">
                            <img src="" alt="Kanji image" id="modal-image-src" class="rounded-xl border-2 border-gray-600 shadow-lg" style="max-height: 500px; max-width: 100%; width: auto; height: auto; display: block; object-fit: contain;">
                            @if($isAdmin ?? false)
                            <div class="mt-3 flex gap-2 justify-center">
                                <button id="replace-image-btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition text-sm">
                                    Заменить изображение
                                </button>
                            </div>
                            <div class="text-gray-500 text-xs mt-1 text-center">или вставьте из буфера (Ctrl+V)</div>
                            @endif
                        </div>
                    </div>
                    <div id="modal-no-image-container" class="hidden">
                        @if($isAdmin ?? false)
                        <div class="border-2 border-dashed border-gray-600 rounded-xl p-8 text-center bg-gray-700/50">
                            <div class="text-gray-400 mb-3">
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400 text-sm mb-3">Изображение отсутствует</p>
                            <button id="add-image-btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition text-sm mb-2">
                                Выбрать файл
                            </button>
                            <div class="text-gray-500 text-xs mt-2">или вставьте из буфера (Ctrl+V)</div>
                        </div>
                        @else
                        <div class="border-2 border-dashed border-gray-600 rounded-xl p-8 text-center bg-gray-700/50">
                            <div class="text-gray-500 text-6xl mb-3">🖼️</div>
                            <p class="text-gray-400 text-sm">Изображение отсутствует</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Правая часть: Информация -->
                <div class="flex-1 space-y-4">
                    <!-- Перевод -->
                    <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600">
                        <div class="text-gray-400 text-sm mb-1">Перевод</div>
                        @if($isAdmin ?? false)
                            <div class="text-white text-lg font-semibold" id="modal-translation-view"></div>
                            <input type="text" id="modal-translation-edit" class="hidden w-full bg-gray-800 border border-gray-600 rounded-lg px-3 py-2 text-white text-lg font-semibold focus:outline-none focus:border-purple-500" placeholder="Перевод на русский">
                        @else
                            <div class="text-white text-lg font-semibold" id="modal-translation"></div>
                        @endif
                    </div>
                    
                    <!-- Чтение -->
                    <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600" id="modal-reading-container">
                        <div class="text-gray-400 text-sm mb-1">Чтение</div>
                        @if($isAdmin ?? false)
                            <div class="text-white text-lg font-semibold" id="modal-reading-view"></div>
                            <input type="text" id="modal-reading-edit" class="hidden w-full bg-gray-800 border border-gray-600 rounded-lg px-3 py-2 text-white text-lg font-semibold focus:outline-none focus:border-purple-500" placeholder="Чтение (хирагана/ромадзи)">
                        @else
                            <div class="text-white text-lg font-semibold" id="modal-reading"></div>
                        @endif
                    </div>
                    
                    <!-- Примеры слов -->
                    <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600" id="modal-description-container">
                        <div class="text-gray-400 text-sm mb-2">Примеры слов</div>
                        @if($isAdmin ?? false)
                            <div class="text-white text-sm whitespace-pre-wrap" id="modal-description-view"></div>
                            <textarea id="modal-description-edit" rows="3" class="hidden w-full bg-gray-800 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500" placeholder="Примеры слов с этим кандзи"></textarea>
                        @else
                            <div class="text-white text-sm" id="modal-description"></div>
                        @endif
                    </div>
                    
                    <!-- Уровень JLPT -->
                    <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600" id="modal-jlpt-container">
                        <div class="text-gray-400 text-sm mb-1">Уровень JLPT</div>
                        @if($isAdmin ?? false)
                            <div class="text-purple-300 text-lg font-semibold" id="modal-jlpt-view"></div>
                            <select id="modal-jlpt-edit" class="hidden w-full bg-gray-800 border border-gray-600 rounded-lg px-3 py-2 text-purple-300 text-lg font-semibold focus:outline-none focus:border-purple-500">
                                <option value="">-- Без уровня --</option>
                                <option value="5">N5</option>
                                <option value="4">N4</option>
                                <option value="3">N3</option>
                                <option value="2">N2</option>
                                <option value="1">N1</option>
                            </select>
                        @else
                            <div class="text-purple-300 text-lg font-semibold" id="modal-jlpt"></div>
                        @endif
                    </div>

                    <!-- Следующее повторение -->
                    <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600 hidden" id="modal-next-review-container">
                        <div class="text-gray-400 text-sm mb-1">Следующее повторение</div>
                        <div class="text-yellow-300 text-lg font-semibold" id="modal-next-review"></div>
                    </div>
                    
                    <!-- Мнемоническая подсказка -->
                    <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600" id="modal-mnemonic-container">
                        <div class="text-gray-400 text-sm mb-2">Мнемоническая подсказка</div>
                        @if($isAdmin ?? false)
                            <div class="text-gray-300 text-sm leading-relaxed whitespace-pre-wrap" id="modal-mnemonic-view"></div>
                            <textarea id="modal-mnemonic-edit" rows="4" class="hidden w-full bg-gray-800 border border-gray-600 rounded-lg px-3 py-2 text-gray-300 text-sm leading-relaxed focus:outline-none focus:border-purple-500" placeholder="Мнемоническая подсказка для запоминания"></textarea>
                        @else
                            <div class="text-gray-300 text-sm leading-relaxed" id="modal-mnemonic"></div>
                        @endif
                    </div>
                    
                    <!-- Добавить в список -->
                    <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600">
                        <div class="text-gray-400 text-sm mb-3">Добавить в список</div>
                        <div id="kanji-lists-dropdown">
                            <p class="text-gray-500 text-sm">Загрузка списков...</p>
                        </div>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="pt-2 space-y-2">
                        @if($isAdmin ?? false)
                            <button id="edit-kanji-btn" class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-blue-500/50">
                                ✏️ Редактировать
                            </button>
                            <button id="save-kanji-btn" class="hidden w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-purple-500/50">
                                💾 Сохранить изменения
                            </button>
                            <button id="cancel-edit-btn" class="hidden w-full bg-gray-600 hover:bg-gray-500 text-white font-semibold px-6 py-3 rounded-lg transition-all">
                                ❌ Отменить
                            </button>
                        @endif
                        <button id="mark-completed-btn" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-green-500/50">
                            Отметить как изученное
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Скрытая форма для загрузки изображения (только для админа) -->
@if($isAdmin ?? false)
<form id="image-upload-form" method="POST" action="{{ route('admin.kanji.update-image') }}" enctype="multipart/form-data" class="hidden">
    @csrf
    <input type="hidden" name="kanji" id="upload-kanji-input">
    <input type="file" name="image" id="image-file-input" accept="image/*">
</form>
@endif

@push('scripts')
<script>
let currentKanji = null;

// Функции переключения режимов (для админа) - должны быть определены ДО использования
@if($isAdmin ?? false)
window.enterEditMode = function() {
    // Скрываем все view элементы
    document.getElementById('modal-translation-view')?.classList.add('hidden');
    document.getElementById('modal-reading-view')?.classList.add('hidden');
    document.getElementById('modal-description-view')?.classList.add('hidden');
    document.getElementById('modal-jlpt-view')?.classList.add('hidden');
    document.getElementById('modal-mnemonic-view')?.classList.add('hidden');
    
    // Показываем все edit элементы
    document.getElementById('modal-translation-edit')?.classList.remove('hidden');
    document.getElementById('modal-reading-edit')?.classList.remove('hidden');
    document.getElementById('modal-description-edit')?.classList.remove('hidden');
    document.getElementById('modal-jlpt-edit')?.classList.remove('hidden');
    document.getElementById('modal-mnemonic-edit')?.classList.remove('hidden');
    
    // Переключаем кнопки
    document.getElementById('edit-kanji-btn')?.classList.add('hidden');
    document.getElementById('save-kanji-btn')?.classList.remove('hidden');
    document.getElementById('cancel-edit-btn')?.classList.remove('hidden');
};

window.exitEditMode = function() {
    // Показываем все view элементы
    document.getElementById('modal-translation-view')?.classList.remove('hidden');
    document.getElementById('modal-reading-view')?.classList.remove('hidden');
    document.getElementById('modal-description-view')?.classList.remove('hidden');
    document.getElementById('modal-jlpt-view')?.classList.remove('hidden');
    document.getElementById('modal-mnemonic-view')?.classList.remove('hidden');
    
    // Скрываем все edit элементы
    document.getElementById('modal-translation-edit')?.classList.add('hidden');
    document.getElementById('modal-reading-edit')?.classList.add('hidden');
    document.getElementById('modal-description-edit')?.classList.add('hidden');
    document.getElementById('modal-jlpt-edit')?.classList.add('hidden');
    document.getElementById('modal-mnemonic-edit')?.classList.add('hidden');
    
    // Переключаем кнопки
    document.getElementById('edit-kanji-btn')?.classList.remove('hidden');
    document.getElementById('save-kanji-btn')?.classList.add('hidden');
    document.getElementById('cancel-edit-btn')?.classList.add('hidden');
};

let originalData = {};
window.restoreOriginalData = function() {
    // Восстанавливаем исходные данные в поля редактирования
    if (originalData) {
        const translationEdit = document.getElementById('modal-translation-edit');
        const readingEdit = document.getElementById('modal-reading-edit');
        const descriptionEdit = document.getElementById('modal-description-edit');
        const mnemonicEdit = document.getElementById('modal-mnemonic-edit');
        const jlptEdit = document.getElementById('modal-jlpt-edit');
        
        if (translationEdit) translationEdit.value = originalData.translation || '';
        if (readingEdit) readingEdit.value = originalData.reading || '';
        if (descriptionEdit) descriptionEdit.value = originalData.description || '';
        if (mnemonicEdit) mnemonicEdit.value = originalData.mnemonic || '';
        if (jlptEdit) jlptEdit.value = originalData.jlptLevel || '';
    }
};
@endif

// Функция открытия модального окна (должна быть глобальной)
window.openKanjiModal = function(kanjiItem) {
    console.log('openKanjiModal вызвана', kanjiItem);
    const modal = document.getElementById('kanji-modal');
    const markCompletedBtn = document.getElementById('mark-completed-btn');
    
    if (!kanjiItem) {
        console.error('kanjiItem не передан');
        return;
    }
    
    if (!modal) {
        console.error('Модальное окно не найдено');
        return;
    }
    
    currentKanji = kanjiItem.getAttribute('data-kanji') || kanjiItem.dataset.kanji;
    if (!currentKanji) {
        console.error('Кандзи не найден');
        return;
    }
    
    console.log('Текущий кандзи:', currentKanji);
    
    // Получаем данные из атрибутов
    const translation = kanjiItem.getAttribute('data-translation') || kanjiItem.dataset.translation || '';
    const reading = kanjiItem.getAttribute('data-reading') || kanjiItem.dataset.reading || '';
    const level = parseInt(kanjiItem.getAttribute('data-level') || kanjiItem.dataset.level || '0');
    const jlptLevel = kanjiItem.getAttribute('data-jlpt-level') || kanjiItem.dataset.jlptLevel || '';
    const lastReviewed = kanjiItem.getAttribute('data-last-reviewed') || kanjiItem.dataset.lastReviewed || '';
    const nextReview = kanjiItem.getAttribute('data-next-review') || kanjiItem.dataset.nextReview || '';
    const imagePath = kanjiItem.getAttribute('data-image-path') || kanjiItem.dataset.imagePath || '';
    const mnemonic = kanjiItem.getAttribute('data-mnemonic') || kanjiItem.dataset.mnemonic || '';
    const description = kanjiItem.getAttribute('data-description') || kanjiItem.dataset.description || '';
    const isCompleted = kanjiItem.getAttribute('data-is-completed') || kanjiItem.dataset.isCompleted || '0';
    
    const isAdmin = {{ ($isAdmin ?? false) ? 'true' : 'false' }};
    
    // Сохраняем исходные данные для отмены редактирования
    @if($isAdmin ?? false)
    originalData = {
        translation: translation || '',
        reading: reading || '',
        description: description || '',
        mnemonic: mnemonic || '',
        jlptLevel: jlptLevel || '',
    };
    @endif
    
    // Заполняем основные поля
    const kanjiEl = document.getElementById('modal-kanji');
    if (kanjiEl) kanjiEl.textContent = currentKanji;
    
    // Перевод
    if (isAdmin) {
        const translationView = document.getElementById('modal-translation-view');
        const translationEdit = document.getElementById('modal-translation-edit');
        if (translationView) translationView.textContent = translation || 'Не указан';
        if (translationEdit) translationEdit.value = translation || '';
    } else {
        const translationEl = document.getElementById('modal-translation');
        if (translationEl) translationEl.textContent = translation || 'Не указан';
    }
    
    // Чтение - всегда показываем
    const readingContainer = document.getElementById('modal-reading-container');
    if (readingContainer) readingContainer.classList.remove('hidden');
    if (isAdmin) {
        const readingView = document.getElementById('modal-reading-view');
        const readingEdit = document.getElementById('modal-reading-edit');
        if (readingView) readingView.textContent = reading || 'Не указано';
        if (readingEdit) readingEdit.value = reading || '';
    } else {
        const readingEl = document.getElementById('modal-reading');
        if (readingEl) readingEl.textContent = reading || 'Не указано';
    }
    
    // Примеры слов - всегда показываем
    const descriptionContainer = document.getElementById('modal-description-container');
    if (descriptionContainer) descriptionContainer.classList.remove('hidden');
    if (isAdmin) {
        const descriptionView = document.getElementById('modal-description-view');
        const descriptionEdit = document.getElementById('modal-description-edit');
        if (descriptionView) descriptionView.textContent = description || 'Нет примеров';
        if (descriptionEdit) descriptionEdit.value = description || '';
    } else {
        const descriptionEl = document.getElementById('modal-description');
        if (descriptionEl) descriptionEl.textContent = description || 'Нет примеров';
    }
    
    // Уровень JLPT - всегда показываем
    const jlptContainer = document.getElementById('modal-jlpt-container');
    if (jlptContainer) jlptContainer.classList.remove('hidden');
    if (isAdmin) {
        const jlptView = document.getElementById('modal-jlpt-view');
        const jlptEdit = document.getElementById('modal-jlpt-edit');
        if (jlptView) jlptView.textContent = (jlptLevel && jlptLevel !== '') ? 'N' + jlptLevel : 'Не указан';
        if (jlptEdit) jlptEdit.value = jlptLevel || '';
    } else {
        const jlptEl = document.getElementById('modal-jlpt');
        if (jlptEl) jlptEl.textContent = (jlptLevel && jlptLevel !== '') ? 'N' + jlptLevel : 'Не указан';
    }

    // Следующее повторение
    const nextReviewEl = document.getElementById('modal-next-review');
    const nextReviewContainer = document.getElementById('modal-next-review-container');
    if (nextReview && nextReview.trim() !== '' && isCompleted !== '1') {
        if (nextReviewEl) nextReviewEl.textContent = nextReview;
        if (nextReviewContainer) nextReviewContainer.classList.remove('hidden');
    } else {
        if (nextReviewContainer) nextReviewContainer.classList.add('hidden');
    }
    
    // Мнемоника - всегда показываем
    const mnemonicContainer = document.getElementById('modal-mnemonic-container');
    if (mnemonicContainer) mnemonicContainer.classList.remove('hidden');
    if (isAdmin) {
        const mnemonicView = document.getElementById('modal-mnemonic-view');
        const mnemonicEdit = document.getElementById('modal-mnemonic-edit');
        if (mnemonicView) {
            if (mnemonic && mnemonic.trim() !== '') {
                mnemonicView.textContent = mnemonic;
                mnemonicView.classList.remove('text-gray-500', 'italic');
            } else {
                mnemonicView.textContent = 'Подсказка отсутствует';
                mnemonicView.classList.add('text-gray-500', 'italic');
            }
        }
        if (mnemonicEdit) mnemonicEdit.value = mnemonic || '';
    } else {
        const mnemonicEl = document.getElementById('modal-mnemonic');
        if (mnemonicEl) {
            if (mnemonic && mnemonic.trim() !== '') {
                mnemonicEl.textContent = mnemonic;
            } else {
                mnemonicEl.textContent = 'Подсказка отсутствует';
                mnemonicEl.classList.add('text-gray-500', 'italic');
            }
        }
    }
    
    // Изображение
    const imageContainer = document.getElementById('modal-image-container');
    const imageSrcEl = document.getElementById('modal-image-src');
    const noImageContainer = document.getElementById('modal-no-image-container');
    
    if (imagePath && imagePath.trim() !== '') {
        if (imageSrcEl) {
            // Формируем правильный путь к изображению
            let fullImagePath;
            if (imagePath.startsWith('/storage/') || imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
                // Если путь уже полный (с /storage/ или URL), используем как есть
                fullImagePath = imagePath;
            } else if (imagePath.startsWith('storage/')) {
                // Если путь начинается с storage/, добавляем начальный слеш
                fullImagePath = '/' + imagePath;
            } else {
                // Если путь относительный (kanji/田.png), добавляем /storage/
                fullImagePath = '{{ asset("storage") }}/' + imagePath;
            }
            imageSrcEl.src = fullImagePath;
            // Обработка загрузки изображения для правильного масштабирования
            imageSrcEl.onload = function() {
                this.style.maxHeight = '400px';
                this.style.width = 'auto';
                this.style.height = 'auto';
            };
            imageSrcEl.onerror = function() {
                console.error('Ошибка загрузки изображения:', fullImagePath);
                // Скрываем изображение при ошибке
                if (imageContainer) imageContainer.classList.add('hidden');
                if (noImageContainer) noImageContainer.classList.remove('hidden');
            };
        }
        if (imageContainer) imageContainer.classList.remove('hidden');
        if (noImageContainer) noImageContainer.classList.add('hidden');
    } else {
        if (imageContainer) imageContainer.classList.add('hidden');
        if (noImageContainer) noImageContainer.classList.remove('hidden');
    }
    
    // Кнопка отметки
    if (isCompleted === '1') {
        if (markCompletedBtn) {
            markCompletedBtn.textContent = 'Изучено ✓';
            markCompletedBtn.disabled = true;
            markCompletedBtn.classList.add('opacity-50', 'cursor-not-allowed');
            markCompletedBtn.classList.remove('from-green-600', 'to-emerald-600', 'hover:from-green-500', 'hover:to-emerald-500');
            markCompletedBtn.classList.add('from-gray-600', 'to-gray-700');
        }
    } else {
        if (markCompletedBtn) {
            markCompletedBtn.textContent = 'Отметить как изученное';
            markCompletedBtn.disabled = false;
            markCompletedBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            markCompletedBtn.classList.remove('from-gray-600', 'to-gray-700');
            markCompletedBtn.classList.add('from-green-600', 'to-emerald-600', 'hover:from-green-500', 'hover:to-emerald-500');
        }
    }
    
    // Показываем модальное окно
    console.log('Открываем модальное окно для кандзи:', currentKanji);
    modal.style.display = 'flex';
    modal.style.zIndex = '9999';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.right = '0';
    modal.style.bottom = '0';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.padding = '1rem';
    document.body.style.overflow = 'hidden';
    
    // Загружаем списки для этого кандзи
    loadKanjiListsInModal(currentKanji);
    
    // Делаем модальное окно focusable и устанавливаем фокус для обработки Ctrl+V
    modal.setAttribute('tabindex', '-1');
    setTimeout(() => {
        modal.focus();
        console.log('Фокус установлен на модальное окно');
        
        // Переключаем в режим просмотра после открытия (для админа)
        @if($isAdmin ?? false)
        if (typeof window.exitEditMode === 'function') {
            window.exitEditMode();
        }
        @endif
    }, 100);
};

/**
 * Открыть окно редактирования кандзи из личного списка
 * Ищет элемент в глобальном списке или загружает данные с сервера
 */
window.openKanjiModalFromListItem = function(kanjiChar) {
    // Сначала пытаемся найти элемент в глобальном списке
    const existingElement = document.querySelector(`[data-kanji="${kanjiChar}"]`);
    if (existingElement) {
        openKanjiModal(existingElement);
        return;
    }
    
    // Если элемента нет в DOM, загружаем данные с сервера
    fetch(`{{ route('kanji.get-kanji') }}?kanji=${encodeURIComponent(kanjiChar)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.error || 'Ошибка загрузки данных кандзи');
        }
        return data;
    })
    .then(data => {
        // Создаем элемент с загруженными данными
        const tempElement = document.createElement('div');
        tempElement.setAttribute('data-kanji', kanjiChar);
        tempElement.setAttribute('data-translation', data.translation || '');
        tempElement.setAttribute('data-reading', data.reading || '');
        tempElement.setAttribute('data-level', data.level || '0');
        tempElement.setAttribute('data-jlpt-level', data.jlpt_level || '');
        tempElement.setAttribute('data-last-reviewed', data.last_reviewed_at || '');
        tempElement.setAttribute('data-next-review', data.next_review_at || '');
        tempElement.setAttribute('data-is-completed', data.is_completed ? '1' : '0');
        tempElement.setAttribute('data-image-path', data.image_path || '');
        tempElement.setAttribute('data-mnemonic', data.mnemonic || '');
        tempElement.setAttribute('data-description', data.description || '');
        
        openKanjiModal(tempElement);
    })
    .catch(error => {
        console.error('Ошибка загрузки кандзи:', error);
        alert('Не удалось загрузить данные о кандзи');
    });
};


// Поиск кандзи
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('kanji-search');
    const searchResultsContainer = document.getElementById('kanji-search-results');
    const searchResultsTbody = document.getElementById('kanji-search-results-tbody');
    const clearBtn = document.getElementById('kanji-search-clear');
    const kanjiList = document.getElementById('kanji-list');
    
    if (searchInput) {
        // Поиск при вводе
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.trim();
                
                if (searchTerm === '') {
                    // Если поле пусто, скрываем результаты поиска и показываем основной список
                    searchResultsContainer.classList.add('hidden');
                    if (kanjiList) kanjiList.style.display = '';
                    clearBtn.classList.add('hidden');
                } else {
                    // Выполняем поиск
                    performSearch(searchTerm);
                    clearBtn.classList.remove('hidden');
                }
            }, 200);
        });
    }
    
    function performSearch(searchTerm) {
        if (!kanjiList) return;
        
        const searchLower = searchTerm.toLowerCase();
        const kanjiItems = kanjiList.querySelectorAll('.kanji-item');
        const results = [];
        
        // Собираем все совпадающие элементы
        kanjiItems.forEach(item => {
            const translation = (item.getAttribute('data-translation') || '').toLowerCase();
            const reading = (item.getAttribute('data-reading') || '').toLowerCase();
            const kanji = (item.getAttribute('data-kanji') || '');
            
            const matches = translation.includes(searchLower) || 
                           reading.includes(searchLower) ||
                           kanji.toLowerCase().includes(searchLower);
            
            if (matches) {
                results.push(item.cloneNode(true));
            }
        });
        
        // Скрываем основной список
        kanjiList.style.display = 'none';
        
        // Отображаем результаты
        if (results.length > 0) {
            searchResultsTbody.innerHTML = '';
            
            // Группируем результаты по 10 в строке
            const chunkSize = 10;
            for (let i = 0; i < results.length; i += chunkSize) {
                const chunk = results.slice(i, i + chunkSize);
                const row = document.createElement('tr');
                
                chunk.forEach(item => {
                    const td = document.createElement('td');
                    td.className = 'bg-gray-700/50 border border-gray-600 hover:border-purple-500 transition-all hover:shadow-lg hover:shadow-purple-500/20 cursor-pointer text-center align-middle';
                    td.style.cssText = 'width: 120px; height: 120px; padding: 1rem; vertical-align: middle; position: relative;';
                    td.innerHTML = item.innerHTML;
                    
                    // Копируем все data атрибуты
                    Array.from(item.attributes).forEach(attr => {
                        if (attr.name.startsWith('data-')) {
                            td.setAttribute(attr.name, attr.value);
                        }
                    });
                    
                    td.onclick = function() {
                        openKanjiModalFromListItem(this.getAttribute('data-kanji'));
                    };
                    
                    row.appendChild(td);
                });
                
                // Добавляем пустые ячейки если строка не полная
                for (let j = chunk.length; j < chunkSize; j++) {
                    const emptyTd = document.createElement('td');
                    emptyTd.style.cssText = 'width: 120px;';
                    row.appendChild(emptyTd);
                }
                
                searchResultsTbody.appendChild(row);
            }
            
            searchResultsContainer.classList.remove('hidden');
        } else {
            searchResultsTbody.innerHTML = '<tr><td colspan="10" class="text-center text-gray-400 py-4">Кандзи не найдено</td></tr>';
            searchResultsContainer.classList.remove('hidden');
        }
    }

    // Кнопка добавления изображения (для админа)
    const addImageBtn = document.getElementById('add-image-btn');
    if (addImageBtn) {
        addImageBtn.addEventListener('click', function() {
            const fileInput = document.getElementById('image-file-input');
            const kanjiInput = document.getElementById('upload-kanji-input');
            if (fileInput && kanjiInput && currentKanji) {
                kanjiInput.value = currentKanji;
                fileInput.click();
            }
        });
    }
    
    // Кнопка замены изображения (для админа)
    const replaceImageBtn = document.getElementById('replace-image-btn');
    if (replaceImageBtn) {
        replaceImageBtn.addEventListener('click', function() {
            const fileInput = document.getElementById('image-file-input');
            const kanjiInput = document.getElementById('upload-kanji-input');
            if (fileInput && kanjiInput && currentKanji) {
                kanjiInput.value = currentKanji;
                fileInput.click();
            }
        });
    }
    
    // Обработка загрузки изображения из файла
    const imageFileInput = document.getElementById('image-file-input');
    if (imageFileInput) {
        imageFileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                uploadImageFile(this.files[0]);
            }
        });
    }
    
    // Функция загрузки изображения
    function uploadImageFile(file) {
        if (!currentKanji) {
            alert('Сначала выберите кандзи');
            return;
        }
        
        const form = document.getElementById('image-upload-form');
        const kanjiInput = document.getElementById('upload-kanji-input');
        if (!form || !kanjiInput) return;
        
        kanjiInput.value = currentKanji;
        
        // Создаем FormData и отправляем
        const formData = new FormData();
        formData.append('kanji', currentKanji);
        formData.append('image', file);
        formData.append('_token', '{{ csrf_token() }}');
        
        // Показываем индикатор загрузки
        const addImageBtn = document.getElementById('add-image-btn');
        const replaceImageBtn = document.getElementById('replace-image-btn');
        const originalAddText = addImageBtn ? addImageBtn.textContent : '';
        const originalReplaceText = replaceImageBtn ? replaceImageBtn.textContent : '';
        
        if (addImageBtn) {
            addImageBtn.textContent = 'Загрузка...';
            addImageBtn.disabled = true;
        }
        if (replaceImageBtn) {
            replaceImageBtn.textContent = 'Загрузка...';
            replaceImageBtn.disabled = true;
        }
        
        fetch('{{ route("admin.kanji.update-image") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Ошибка загрузки');
        })
        .then(data => {
            if (data.success) {
                // Обновляем изображение в модальном окне без перезагрузки
                const imageSrcEl = document.getElementById('modal-image-src');
                const imageContainer = document.getElementById('modal-image-container');
                const noImageContainer = document.getElementById('modal-no-image-container');
                
                if (imageSrcEl && data.image_path) {
                    imageSrcEl.src = data.image_path;
                }
                if (imageContainer) imageContainer.classList.remove('hidden');
                if (noImageContainer) noImageContainer.classList.add('hidden');
                
                // Обновляем data-атрибут на текущем kanjiItem
                // Используем relative_path если он есть, иначе извлекаем из image_path
                const currentKanjiItem = document.querySelector(`.kanji-item[data-kanji="${currentKanji}"]`);
                if (currentKanjiItem && data.image_path) {
                    // Используем relative_path если он предоставлен сервером
                    let relativePath = data.relative_path;
                    if (!relativePath && data.image_path) {
                        // Извлекаем относительный путь из полного URL
                        let fullPath = data.image_path;
                        // Убираем полный URL если есть
                        if (fullPath.includes('://')) {
                            const urlParts = fullPath.split('/storage/');
                            if (urlParts.length > 1) {
                                relativePath = urlParts[1];
                            }
                        } else if (fullPath.startsWith('/storage/')) {
                            relativePath = fullPath.substring('/storage/'.length);
                        } else if (fullPath.startsWith('storage/')) {
                            relativePath = fullPath.substring('storage/'.length);
                        } else {
                            relativePath = fullPath;
                        }
                    }
                    if (relativePath) {
                        currentKanjiItem.dataset.imagePath = relativePath;
                        currentKanjiItem.setAttribute('data-image-path', relativePath);
                    }
                }
                
                alert('Изображение успешно загружено!');
            } else {
                throw new Error(data.error || 'Неизвестная ошибка');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Ошибка при загрузке изображения: ' + error.message);
        })
        .finally(() => {
            if (addImageBtn) {
                addImageBtn.textContent = originalAddText;
                addImageBtn.disabled = false;
            }
            if (replaceImageBtn) {
                replaceImageBtn.textContent = originalReplaceText;
                replaceImageBtn.disabled = false;
            }
        });
    }
    
    // Обработка вставки из буфера обмена
    @if($isAdmin ?? false)
    function handlePaste(e) {
        console.log('Событие paste получено');
        
        // Проверяем, открыто ли модальное окно
        const modal = document.getElementById('kanji-modal');
        if (!modal) {
            console.log('Модальное окно не найдено');
            return false;
        }
        
        // Проверяем видимость модального окна
        const computedStyle = window.getComputedStyle(modal);
        const isModalVisible = modal.style.display === 'flex' || 
                              computedStyle.display === 'flex';
        
        console.log('Модальное окно видимо:', isModalVisible, 'display:', computedStyle.display);
        
        if (!isModalVisible) {
            return false;
        }
        
        // Проверяем, что есть кандзи
        if (!currentKanji) {
            console.log('Нет текущего кандзи для вставки изображения');
            return false;
        }
        
        const items = e.clipboardData.items;
        if (!items || items.length === 0) {
            console.log('Нет элементов в буфере обмена');
            return false;
        }
        
        console.log('Элементы в буфере:', items.length);
        
        for (let i = 0; i < items.length; i++) {
            console.log('Тип элемента', i, ':', items[i].type);
            if (items[i].type.indexOf('image') !== -1) {
                e.preventDefault();
                e.stopPropagation();
                
                const blob = items[i].getAsFile();
                
                if (blob) {
                    console.log('Вставлено изображение из буфера для кандзи:', currentKanji, 'размер:', blob.size);
                    // Создаем File объект из Blob
                    const file = new File([blob], 'pasted-image.png', { type: blob.type || 'image/png' });
                    uploadImageFile(file);
                    return true;
                }
                break;
            }
        }
        console.log('Изображение не найдено в буфере обмена');
        return false;
    }
    
    // Обработчик на документе (глобальный)
    document.addEventListener('paste', handlePaste);
    
    // Также добавляем обработчик на само модальное окно для гарантии
    const modalForPaste = document.getElementById('kanji-modal');
    if (modalForPaste) {
        modalForPaste.addEventListener('paste', handlePaste);
        // Делаем модальное окно focusable для обработки событий
        modalForPaste.setAttribute('tabindex', '-1');
        console.log('Обработчик paste добавлен на модальное окно');
    }
    @endif
    
    // Кнопка редактирования
    @if($isAdmin ?? false)
    const editKanjiBtn = document.getElementById('edit-kanji-btn');
    if (editKanjiBtn) {
        editKanjiBtn.addEventListener('click', function() {
            if (typeof window.enterEditMode === 'function') {
                window.enterEditMode();
            }
        });
    }
    
    // Кнопка отмены редактирования
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            if (typeof window.restoreOriginalData === 'function') {
                window.restoreOriginalData();
            }
            if (typeof window.exitEditMode === 'function') {
                window.exitEditMode();
            }
        });
    }
    @endif
    
    // Кнопка сохранения изменений
    @if($isAdmin ?? false)
    const saveKanjiBtn = document.getElementById('save-kanji-btn');
    if (saveKanjiBtn) {
        saveKanjiBtn.addEventListener('click', function() {
            if (!currentKanji) return;
            
            const translation = document.getElementById('modal-translation-edit')?.value || '';
            const reading = document.getElementById('modal-reading-edit')?.value || '';
            const description = document.getElementById('modal-description-edit')?.value || '';
            const mnemonic = document.getElementById('modal-mnemonic-edit')?.value || '';
            const jlptLevel = document.getElementById('modal-jlpt-edit')?.value || '';
            
            this.disabled = true;
            this.textContent = 'Сохраняю...';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            
            fetch('{{ route("kanji.quick-update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    kanji: currentKanji,
                    translation_ru: translation,
                    reading: reading,
                    description: description,
                    mnemonic: mnemonic,
                    jlpt_level: jlptLevel ? parseInt(jlptLevel) : null,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем data-атрибуты в таблице и originalData для текущего кандзи
                    const kanjiItem = document.querySelector(`.kanji-item[data-kanji="${currentKanji}"]`);
                    if (kanjiItem && data.kanji) {
                        if (data.kanji.translation_ru) {
                            kanjiItem.setAttribute('data-translation', data.kanji.translation_ru);
                            @if($isAdmin ?? false)
                            if (typeof originalData !== 'undefined') {
                                originalData.translation = data.kanji.translation_ru;
                            }
                            @endif
                        }
                        if (data.kanji.reading !== undefined) {
                            kanjiItem.setAttribute('data-reading', data.kanji.reading || '');
                            @if($isAdmin ?? false)
                            if (typeof originalData !== 'undefined') {
                                originalData.reading = data.kanji.reading || '';
                            }
                            @endif
                        }
                        if (data.kanji.description !== undefined) {
                            kanjiItem.setAttribute('data-description', data.kanji.description || '');
                            @if($isAdmin ?? false)
                            if (typeof originalData !== 'undefined') {
                                originalData.description = data.kanji.description || '';
                            }
                            @endif
                        }
                        if (data.kanji.mnemonic !== undefined) {
                            kanjiItem.setAttribute('data-mnemonic', data.kanji.mnemonic || '');
                            @if($isAdmin ?? false)
                            if (typeof originalData !== 'undefined') {
                                originalData.mnemonic = data.kanji.mnemonic || '';
                            }
                            @endif
                        }
                        if (data.kanji.jlpt_level !== undefined) {
                            kanjiItem.setAttribute('data-jlpt-level', data.kanji.jlpt_level || '');
                            @if($isAdmin ?? false)
                            if (typeof originalData !== 'undefined') {
                                originalData.jlptLevel = data.kanji.jlpt_level || '';
                            }
                            @endif
                        }
                    }
                    
                    // Обновляем view элементы с новыми данными
                    const translationView = document.getElementById('modal-translation-view');
                    const readingView = document.getElementById('modal-reading-view');
                    const descriptionView = document.getElementById('modal-description-view');
                    const jlptView = document.getElementById('modal-jlpt-view');
                    const mnemonicView = document.getElementById('modal-mnemonic-view');
                    
                    if (translationView && data.kanji.translation_ru) {
                        translationView.textContent = data.kanji.translation_ru || 'Не указан';
                    }
                    if (readingView && data.kanji.reading !== undefined) {
                        readingView.textContent = data.kanji.reading || 'Не указано';
                    }
                    if (descriptionView && data.kanji.description !== undefined) {
                        descriptionView.textContent = data.kanji.description || 'Нет примеров';
                    }
                    if (jlptView && data.kanji.jlpt_level !== undefined) {
                        jlptView.textContent = (data.kanji.jlpt_level && data.kanji.jlpt_level !== '') ? 'N' + data.kanji.jlpt_level : 'Не указан';
                    }
                    if (mnemonicView && data.kanji.mnemonic !== undefined) {
                        if (data.kanji.mnemonic && data.kanji.mnemonic.trim() !== '') {
                            mnemonicView.textContent = data.kanji.mnemonic;
                            mnemonicView.classList.remove('text-gray-500', 'italic');
                        } else {
                            mnemonicView.textContent = 'Подсказка отсутствует';
                            mnemonicView.classList.add('text-gray-500', 'italic');
                        }
                    }
                    
                    // Переключаем обратно в режим просмотра
                    if (typeof window.exitEditMode === 'function') {
                        window.exitEditMode();
                    }
                    
                    alert('Кандзи успешно обновлен!');
                    this.disabled = false;
                } else {
                    alert(data.error || 'Ошибка при сохранении');
                    this.disabled = false;
                    this.textContent = '💾 Сохранить изменения';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при сохранении кандзи');
                this.disabled = false;
                this.textContent = '💾 Сохранить изменения';
            });
        });
    }
    @endif
    
    // Кнопка отметки как изученное
    const markCompletedBtn = document.getElementById('mark-completed-btn');
    if (markCompletedBtn) {
        markCompletedBtn.addEventListener('click', function() {
            if (!currentKanji) return;
            
            this.disabled = true;
            this.textContent = 'Отмечаю...';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            
            fetch('{{ route("kanji.mark-completed") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    kanji: currentKanji
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Ошибка при отметке кандзи');
                    this.disabled = false;
                    this.textContent = 'Отметить как изученное';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при отметке кандзи');
                this.disabled = false;
                this.textContent = 'Отметить как изученное';
            });
        });
    }
    
    // Кнопка закрытия модального окна кандзи
    const closeModalBtn = document.getElementById('close-modal');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            const modal = document.getElementById('kanji-modal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                // Выход из режима редактирования если был активен
                @if($isAdmin ?? false)
                if (typeof window.exitEditMode === 'function') {
                    window.exitEditMode();
                }
                @endif
            }
        });
    }
    
    // Backdrop clicks should not close the modal; close only via explicit controls (close button)
    const modal = document.getElementById('kanji-modal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            // Закрываем модаль только если клик был на самом backdrop (не на содержимом)
            if (event.target === this) {
                // Не закрываем модаль при клике на backdrop
                event.stopPropagation();
            }
        });
    }

    
    // Функция переключения выбора кандзи для изучения
    window.toggleKanjiStudySelection = function(checkbox) {
        const kanji = checkbox.getAttribute('data-kanji');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        
        // Сохраняем предыдущее состояние на случай ошибки
        const previousState = checkbox.checked;
        
        fetch('{{ route("kanji.toggle-study-selection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                kanji: kanji
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем data-атрибут у родительской ячейки
                const cell = checkbox.closest('.kanji-item');
                if (cell) {
                    cell.setAttribute('data-is-selected', data.is_selected ? '1' : '0');
                }
                console.log(data.message);
            } else {
                // Возвращаем предыдущее состояние при ошибке
                checkbox.checked = !previousState;
                alert(data.error || 'Ошибка при изменении статуса');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Возвращаем предыдущее состояние при ошибке
            checkbox.checked = !previousState;
            alert('Ошибка при изменении статуса');
        });
    };

    // Переключатель режима выбора кандзи
    const selectionToggle = document.getElementById('toggle-selection-mode');
    if (selectionToggle) {
        selectionToggle.addEventListener('change', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            const enabled = !!this.checked;

            fetch('{{ route("kanji.update-settings") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    use_kanji_selection: enabled ? 1 : 0
                })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    this.checked = !enabled;
                    alert(data.error || 'Ошибка сохранения настройки');
                    return;
                }

                // Показываем/скрываем галочки в таблице
                document.querySelectorAll('.kanji-selection-overlay').forEach(el => {
                    el.style.display = enabled ? '' : 'none';
                });
            })
            .catch(err => {
                console.error(err);
                this.checked = !enabled;
                alert('Ошибка сохранения настройки');
            });
        });
    }

    // --- Слова: модалки добавления и редактирования ---
    const modalAddWord = document.getElementById('modal-add-word');
    const modalEditWord = document.getElementById('modal-edit-word');
    if (modalAddWord) {
        function openAddWordModal() {
            modalAddWord.style.display = 'flex';
            modalAddWord.setAttribute('tabindex', '-1');
            document.body.style.overflow = 'hidden';
            setTimeout(() => { modalAddWord.focus(); }, 100);
        }
        function closeAddWordModal() {
            modalAddWord.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.getElementById('btn-add-word')?.addEventListener('click', function() {
            document.getElementById('add-japanese-word').value = '';
            document.getElementById('add-reading').value = '';
            document.getElementById('add-translation-ru').value = '';
            document.getElementById('add-translation-en').value = '';
            document.getElementById('add-word-type').value = '';
            document.getElementById('add-example-jp').value = '';
            document.getElementById('add-example-ru').value = '';
            document.getElementById('add-word-message').classList.add('hidden');
            openAddWordModal();
        });
        document.getElementById('modal-add-word-close')?.addEventListener('click', function() {
            closeAddWordModal();
        });
        document.getElementById('add-word-cancel')?.addEventListener('click', function() {
            closeAddWordModal();
        });
        // Backdrop clicks should not close the modal; close only via explicit controls
        document.getElementById('add-word-submit')?.addEventListener('click', function() {
            const japaneseWord = (document.getElementById('add-japanese-word')?.value || '').trim();
            if (!japaneseWord) {
                document.getElementById('add-word-message').textContent = 'Введите японское слово.';
                document.getElementById('add-word-message').classList.remove('hidden');
                return;
            }
            this.disabled = true;
            const msgEl = document.getElementById('add-word-message');
            const payload = {
                japanese_word: japaneseWord,
                reading: document.getElementById('add-reading')?.value || '',
                translation_ru: document.getElementById('add-translation-ru')?.value || '',
                translation_en: document.getElementById('add-translation-en')?.value || '',
                word_type: document.getElementById('add-word-type')?.value || '',
                example_jp: document.getElementById('add-example-jp')?.value || '',
                example_ru: document.getElementById('add-example-ru')?.value || '',
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            fetch('{{ route("dictionary.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': payload._token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    modalAddWord.style.display = 'none';
                    window.location.reload();
                } else {
                    msgEl.textContent = data.error || 'Ошибка добавления';
                    msgEl.classList.remove('hidden');
                }
            })
            .catch(() => {
                msgEl.textContent = 'Ошибка сети';
                msgEl.classList.remove('hidden');
            })
            .finally(() => { this.disabled = false; });
        });

        // Быстро добавить уже существующее глобальное слово в личный словарь
        document.querySelectorAll('.word-add-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const wordId = this.getAttribute('data-word-id');
                if (!wordId) return;
                this.disabled = true;
                fetch('{{ route("dictionary.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ word_id: wordId })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.error || 'Не удалось добавить слово');
                    }
                })
                .catch(() => alert('Ошибка сети'))
                .finally(() => { this.disabled = false; });
            });
        });
    }
    if (modalEditWord) {
        function openEditWordModal() {
            modalEditWord.style.display = 'flex';
            modalEditWord.setAttribute('tabindex', '-1');
            document.body.style.overflow = 'hidden';
            setTimeout(() => { modalEditWord.focus(); }, 100);
        }
        function closeEditWordModal() {
            modalEditWord.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.getElementById('modal-edit-word-close')?.addEventListener('click', function() {
            closeEditWordModal();
        });
        document.getElementById('edit-word-cancel')?.addEventListener('click', function() {
            closeEditWordModal();
        });
        // Backdrop clicks should not close the modal; close only via explicit controls
        document.querySelectorAll('.word-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const wordId = this.getAttribute('data-word-id');
                fetch('{{ url("/dictionary") }}/' + wordId + '/data', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('edit-word-id').value = data.id;
                    document.getElementById('edit-japanese-word').value = data.japanese_word || '';
                    document.getElementById('edit-reading').value = data.reading || '';
                    document.getElementById('edit-translation-ru').value = data.translation_ru || '';
                    document.getElementById('edit-translation-en').value = data.translation_en || '';
                    document.getElementById('edit-word-type').value = data.word_type || '';
                    document.getElementById('edit-example-jp').value = data.example_jp || '';
                    document.getElementById('edit-example-ru').value = data.example_ru || '';
                    document.getElementById('edit-word-message').classList.add('hidden');
                    openEditWordModal();
                })
                .catch(() => alert('Не удалось загрузить слово'));
            });
        });
        document.getElementById('edit-word-submit')?.addEventListener('click', function() {
            const wordId = document.getElementById('edit-word-id').value;
            const payload = {
                japanese_word: document.getElementById('edit-japanese-word').value,
                reading: document.getElementById('edit-reading').value,
                translation_ru: document.getElementById('edit-translation-ru').value,
                translation_en: document.getElementById('edit-translation-en').value,
                word_type: document.getElementById('edit-word-type').value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            };
            this.disabled = true;
            const msgEl = document.getElementById('edit-word-message');
            fetch('{{ url("/dictionary") }}/' + wordId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': payload._token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    japanese_word: payload.japanese_word,
                    reading: payload.reading,
                    translation_ru: payload.translation_ru,
                    translation_en: payload.translation_en,
                    word_type: payload.word_type,
                    example_jp: document.getElementById('edit-example-jp').value,
                    example_ru: document.getElementById('edit-example-ru').value
                })
            })
            .then(r => r.json().catch(() => ({})))
            .then(data => {
                if (data.success) {
                    modalEditWord.classList.add('hidden');
                    modalEditWord.style.display = 'none';
                    window.location.reload();
                } else {
                    msgEl.textContent = (data.errors && Object.values(data.errors).flat()[0]) || data.message || 'Ошибка сохранения';
                    msgEl.classList.remove('hidden');
                }
            })
            .catch(() => {
                msgEl.textContent = 'Ошибка сети';
                msgEl.classList.remove('hidden');
            })
            .finally(() => { this.disabled = false; });
        });
    }
});

// ========== Управление списками кандзи ==========
document.addEventListener('DOMContentLoaded', function() {
    loadKanjiLists();
    loadWordLists();
    
    // Обработчик кнопки создания списка кандзи
    document.getElementById('btn-create-list')?.addEventListener('click', function() {
        openCreateListModal();
    });
    
    // Обработчик кнопки создания списка слов
    document.getElementById('btn-create-word-list')?.addEventListener('click', function() {
        openCreateWordListModal();
    });
});

function loadKanjiLists() {
    fetch('{{ route("kanji-lists.index") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('kanji-lists-container');
        const select = document.getElementById('quiz-list-select');
        
        if (!data.lists || data.lists.length === 0) {
            container.innerHTML = '<p class="text-gray-400">Нет списков. Создайте первый список!</p>';
            return;
        }
        
        // Очищаем select от старых опций (кроме первой "Все кандзи")
        while (select.children.length > 1) {
            select.removeChild(select.children[1]);
        }
        
        let html = '<div class="space-y-6">';
        data.lists.forEach(list => {
            html += `
                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold text-white text-lg">${list.name}</h4>
                                ${list.multiple_choice_only ? '<span class="bg-green-900/30 text-green-400 text-xs px-2 py-1 rounded border border-green-700/50">🎯 Только выбор</span>' : ''}
                            </div>
                            <p class="text-gray-400 text-sm">${list.description || 'Без описания'}</p>
                            <p class="text-gray-500 text-xs mt-1">${list.kanji_count} кандзи</p>
                            <div style="width:220px; height:8px; background-color: rgba(75,85,99,0.35); border-radius:9999px; overflow:hidden; margin-top:8px;">
                                <div style="height:100%; width: ${list.progress_percent || 0}%; background: linear-gradient(90deg, #a855f7 0%, #6366f1 100%); border-radius:9999px; transition: width 0.3s ease; box-shadow: 0 0 6px rgba(168,85,247,0.35);"></div>
                            </div>
                            <p class="text-gray-400 text-xs mt-1">Прогресс: ${list.progress_percent || 0}% — ${list.completed_count || 0} завершено</p>
                            <p class="text-gray-400 text-xs mt-1">📚 Повторений: ${list.repetitions_completed || 0}</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button onclick="openEditListModal(${list.id})" class="bg-blue-600 hover:bg-blue-500 px-3 py-2 rounded text-sm text-white">✏️</button>
                            <button onclick="deleteList(${list.id})" class="bg-red-600 hover:bg-red-500 px-3 py-2 rounded text-sm text-white">🗑️</button>
                            <a href="{{ route('kanji.quiz') }}?list_id=${list.id}${list.progress_percent === 100 ? '&count=' + list.kanji_count : ''}" class="bg-purple-600 hover:bg-purple-500 px-3 py-2 rounded text-sm text-white">▶️ Квиз</a>
                        </div>
                    </div>
            `;
            
            // Добавляем таблицу с кандзи
            if (list.kanji_in_list && list.kanji_in_list.length > 0) {
                html += `<div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <tbody>
                `;
                
                // Группируем кандзи по 10 в строке
                const chunkSize = 10;
                const kanjiProgresses = list.kanji_with_progress || [];
                
                for (let i = 0; i < list.kanji_in_list.length; i += chunkSize) {
                    const chunk = list.kanji_in_list.slice(i, i + chunkSize);
                    html += '<tr>';
                    chunk.forEach(kanji => {
                        const kanjiProgress = kanjiProgresses.find(k => k.kanji === kanji);
                        const progressPercent = kanjiProgress ? kanjiProgress.progress_percent : 0;
                        
                        html += `
                            <td class="bg-gray-700/50 border border-gray-600 hover:border-purple-500 transition-all cursor-pointer text-center align-middle" 
                                style="width: 120px; height: 120px; padding: 1rem; vertical-align: middle; position: relative;"
                                onclick="event.stopPropagation(); openKanjiModalFromListItem('${kanji}');">
                                <div style="display: flex; flex-direction: column; height: 100%; justify-content: space-between; align-items: center;">
                                    <div class="text-6xl font-bold text-white" style="font-family: 'Noto Sans JP', sans-serif; line-height: 1.2; display: flex; align-items: center; justify-content: center; flex: 1;">${kanji}</div>
                                    <div style="width: 90%; height: 6px; background-color: rgba(75, 85, 99, 0.5); border-radius: 9999px; overflow: hidden; position: relative; margin-top: 0.5rem;">
                                        <div style="height: 100%; width: ${progressPercent}%; background: linear-gradient(90deg, #a855f7 0%, #6366f1 100%); border-radius: 9999px; transition: width 0.3s ease; box-shadow: 0 0 4px rgba(168, 85, 247, 0.4);"></div>
                                    </div>
                                </div>
                            </td>
                        `;
                    });
                    // Добавляем пустые ячейки если строка не полная
                    for (let j = chunk.length; j < chunkSize; j++) {
                        html += '<td style="width: 120px;"></td>';
                    }
                    html += '</tr>';
                }
                
                html += `
                        </tbody>
                    </table>
                </div>`;
            } else {
                html += '<p class="text-gray-500 text-sm">В списке нет кандзи</p>';
            }
            
            html += '</div>';
            
            // Добавляем опцию в select
            const option = document.createElement('option');
            option.value = list.id;
            option.textContent = `${list.name} (${list.kanji_count})`;
            select.appendChild(option);
        });
        html += '</div>';
        container.innerHTML = html;
    })
    .catch(err => {
        document.getElementById('kanji-lists-container').innerHTML = '<p class="text-red-400">Ошибка загрузки списков</p>';
        console.error(err);
    });
}

function openCreateListModal() {
    const modalHtml = `
        <div id="modal-create-list" style="position: fixed; inset: 0; display: flex; justify-content: center; align-items: center; background: rgba(0, 0, 0, 0.5); z-index: 50;" class="modal-backdrop">
            <div style="background: #2d3748; border-radius: 12px; padding: 2rem; width: 90%; max-width: 500px; border: 1px solid #4b5563;" class="modal-content">
                <h2 class="text-2xl font-bold text-purple-400 mb-4">Создать новый список</h2>
                <input type="text" id="list-name" placeholder="Название списка" 
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-3">
                <textarea id="list-description" placeholder="Описание (опционально)" 
                          class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-4 h-20"></textarea>
                <div class="flex gap-2 justify-end">
                    <button onclick="closeCreateListModal()" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded text-white">Отмена</button>
                    <button onclick="saveNewList()" class="bg-purple-600 hover:bg-purple-500 px-4 py-2 rounded text-white">Создать</button>
                </div>
                <p id="list-create-error" class="text-red-400 text-sm mt-3 hidden"></p>
            </div>
        </div>
    `;
    
    const existing = document.getElementById('modal-create-list');
    if (existing) existing.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    // Backdrop clicks should not close the modal; close only via explicit controls
}

function closeCreateListModal() {
    document.getElementById('modal-create-list')?.remove();
}

function saveNewList() {
    const name = document.getElementById('list-name').value.trim();
    const description = document.getElementById('list-description').value.trim();
    
    if (!name) {
        document.getElementById('list-create-error').textContent = 'Название не может быть пустым';
        document.getElementById('list-create-error').classList.remove('hidden');
        return;
    }
    
    fetch('{{ route("kanji-lists.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, description })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeCreateListModal();
            loadKanjiLists();
        } else {
            document.getElementById('list-create-error').textContent = data.message || 'Ошибка создания';
            document.getElementById('list-create-error').classList.remove('hidden');
        }
    })
    .catch(err => {
        document.getElementById('list-create-error').textContent = 'Ошибка сети';
        document.getElementById('list-create-error').classList.remove('hidden');
        console.error(err);
    });
}

function openEditListModal(listId) {
    // Загружаем все списки для поиска нужного списка
    fetch('{{ route("kanji-lists.index") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const currentList = data.lists.find(l => l.id === listId);
        if (!currentList) {
            alert('Список не найден');
            return;
        }
        
        // Загружаем все кандзи из таблицы для выбора
        const allKanjis = Array.from(document.querySelectorAll('[data-kanji]')).map(el => ({
            kanji: el.dataset.kanji,
            translation: el.dataset.translation,
            reading: el.dataset.reading
        }));
        
        displayEditListModal(listId, currentList, allKanjis);
    })
    .catch(err => {
        alert('Ошибка загрузки данных списка');
        console.error(err);
    });
}

function displayEditListModal(listId, currentList, allKanjis) {
    const currentKanjisSet = new Set(currentList.kanji_in_list || []);
    
    // Создаем HTML модального окна
    const modalHtml = `
        <div id="modal-edit-list-${listId}" style="position: fixed; inset: 0; display: flex; justify-content: center; align-items: center; background: rgba(0, 0, 0, 0.5); z-index: 50; overflow-y: auto;" class="modal-backdrop">
            <div style="background: #2d3748; border-radius: 12px; padding: 2rem; width: 90%; max-width: 700px; border: 1px solid #4b5563; margin: 2rem auto;" class="modal-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 class="text-2xl font-bold text-purple-400">Редактировать список</h2>
                    <button onclick="closeEditListModal(${listId})" style="background: none; border: none; color: #9ca3af; font-size: 1.5rem; cursor: pointer; hover: color: #fff;">×</button>
                </div>
                
                <div style="max-height: 600px; overflow-y: auto;">
                    <!-- Информация о списке -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="text-white text-sm block mb-2">Название списка</label>
                        <input type="text" id="edit-list-name-${listId}" placeholder="Название списка" 
                               value="${escapeHtml(currentList.name)}"
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-3">
                        
                        <label class="text-white text-sm block mb-2">Описание</label>
                        <textarea id="edit-list-description-${listId}" placeholder="Описание (опционально)" 
                                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 h-20">${escapeHtml(currentList.description || '')}</textarea>
                        
                        <!-- Опция: Только множественный выбор -->
                        <div style="margin-top: 1rem; padding: 0.75rem; background: #374151; border-radius: 6px; border-left: 3px solid #10b981;">
                            <label class="flex items-center cursor-pointer" style="gap: 0.75rem;">
                                <input type="checkbox" id="edit-multiple-choice-only-${listId}" 
                                       ${currentList.multiple_choice_only ? 'checked' : ''}
                                       class="w-4 h-4" style="cursor: pointer;">
                                <span class="text-white text-sm font-medium">🎯 Только множественный выбор</span>
                            </label>
                            <p class="text-gray-400 text-xs mt-1">Если включено, квиз всегда будет с вариантами ответов, даже для продвинутых уровней</p>
                        </div>
                    </div>
                    
                    <!-- Вставка списка кандзи через запятую -->
                    <div style="margin-bottom: 1.5rem; background: #1f2937; border-left: 4px solid #8b5cf6; padding: 1rem; border-radius: 6px;">
                        <label class="text-white text-sm block mb-2">📋 Вставить кандзи из списка</label>
                        <p class="text-gray-400 text-xs mb-2">Вставьте кандзи через запятую (например: 去, 物, 代)</p>
                        <textarea id="kanji-bulk-input-${listId}" placeholder="Вставьте кандзи через запятую..." 
                                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 h-20 font-mono"
                                  style="font-family: 'Noto Sans JP', monospace;"></textarea>
                        <div style="margin-top: 0.5rem;">
                            <button type="button" onclick="addKanjisFromBulkInput(${listId})" 
                                    style="background: #8b5cf6; border: none; color: white; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.875rem; transition: all 0.2s;"
                                    onmouseover="this.style.background='#7c3aed'"
                                    onmouseout="this.style.background='#8b5cf6'">
                                ↓ Добавить
                            </button>
                        </div>
                    </div>
                    
                    <!-- Поиск кандзи для добавления -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="text-white text-sm block mb-2">🔍 Или найти и добавить по одному</label>
                        <input type="text" id="kanji-search-input-${listId}" placeholder="Поиск по кандзи, переводу или чтению..." 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-2"
                               oninput="filterAddKanjis(${listId}, '${currentList.name}')">
                        <div id="kanji-search-results-${listId}" class="bg-gray-700/50 rounded-lg p-3 max-h-40 overflow-y-auto">
                            <p class="text-gray-400 text-sm">Начните вводить для поиска...</p>
                        </div>
                    </div>
                    
                    <!-- Текущие кандзи в списке -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="text-white text-sm block mb-2">✏️ Кандзи в списке (${currentList.kanji_count})</label>
                        <div id="current-kanjis-${listId}" class="bg-gray-700/50 rounded-lg p-4 border border-gray-600" style="min-height: 100px;">
                            ${renderCurrentKanjis(listId, currentList)}
                        </div>
                    </div>
                </div>
                
                <p id="list-edit-error-${listId}" class="text-red-400 text-sm mb-3 hidden"></p>
                
                <!-- Кнопки -->
                <div class="flex gap-2 justify-end">
                    <button onclick="closeEditListModal(${listId})" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded text-white font-medium transition">Отмена</button>
                    <button onclick="saveEditedList(${listId})" class="bg-purple-600 hover:bg-purple-500 px-4 py-2 rounded text-white font-medium transition">Сохранить</button>
                </div>
            </div>
        </div>
    `;
    
    // Удаляем старое модальное окно если оно есть
    const existing = document.getElementById(`modal-edit-list-${listId}`);
    if (existing) existing.remove();
    
    // Вставляем новое модальное окно
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Обработка закрытия при клике на фон
    const modal = document.getElementById(`modal-edit-list-${listId}`);
    // Backdrop clicks should not close the modal; close only via explicit controls (close button)
}

function renderCurrentKanjis(listId, currentList) {
    if (!currentList.kanji_in_list || currentList.kanji_in_list.length === 0) {
        return '<p class="text-gray-400 text-sm">В списке нет кандзи</p>';
    }
    
    const chunkSize = 10;
    let html = '<div style="display: inline-block; width: 100%;">';
    
    for (let i = 0; i < currentList.kanji_in_list.length; i += chunkSize) {
        html += '<div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">';
        
        const chunk = currentList.kanji_in_list.slice(i, i + chunkSize);
        chunk.forEach(kanji => {
            html += `
                <div style="background: #4b5563; padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #6b7280; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 1.25rem; font-family: 'Noto Sans JP', sans-serif;">${kanji}</span>
                    <button type="button" onclick="removeKanjiFromListEdit(${listId}, '${kanji}')" 
                            style="background: #dc2626; border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.875rem;">
                        ✕
                    </button>
                </div>
            `;
        });
        
        html += '</div>';
    }
    
    html += '</div>';
    return html;
}

function filterAddKanjis(listId, listName) {
    const searchInput = document.getElementById(`kanji-search-input-${listId}`);
    const query = searchInput.value.trim().toLowerCase();
    const resultsContainer = document.getElementById(`kanji-search-results-${listId}`);
    
    if (!query) {
        resultsContainer.innerHTML = '<p class="text-gray-400 text-sm">Начните вводить для поиска...</p>';
        return;
    }
    
    // Получаем все кандзи из таблицы (вне модального окна для избежания дублей)
    const allKanjisMap = new Map();
    document.querySelectorAll('[data-kanji]').forEach(el => {
        // Только берем из основной таблицы кандзи, не из модального окна
        if (!el.closest(`#modal-edit-list-${listId}`)) {
            const kanji = el.dataset.kanji;
            if (!allKanjisMap.has(kanji)) {
                allKanjisMap.set(kanji, {
                    kanji: kanji,
                    translation: el.dataset.translation,
                    reading: el.dataset.reading
                });
            }
        }
    });
    
    // Получаем текущий список кандзи
    const currentKanjisSet = new Set(
        Array.from(document.querySelectorAll(`#current-kanjis-${listId} span[style*="font-size"]`))
            .map(el => el.textContent.trim())
    );
    
    // Фильтруем и показываем только те, которых еще нет в списке
    const filtered = Array.from(allKanjisMap.values()).filter(item => {
        if (currentKanjisSet.has(item.kanji)) return false; // Уже в списке
        
        const translationMatch = item.translation?.toLowerCase().includes(query);
        const readingMatch = item.reading?.toLowerCase().includes(query);
        const kanjiMatch = item.kanji === query;
        
        return translationMatch || readingMatch || kanjiMatch;
    });
    
    if (filtered.length === 0) {
        resultsContainer.innerHTML = '<p class="text-gray-400 text-sm">Нет результатов</p>';
        return;
    }
    
    let html = '<div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">';
    filtered.slice(0, 20).forEach(item => {
        html += `
            <button type="button" 
                    onclick="addKanjiToListEdit(${listId}, '${item.kanji}')"
                    style="background: #6366f1; border: none; color: white; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-family: 'Noto Sans JP', sans-serif; font-size: 1rem; font-weight: bold;"
                    onmouseover="this.style.background='#4f46e5'"
                    onmouseout="this.style.background='#6366f1'">
                ${item.kanji}
            </button>
        `;
    });
    html += '</div>';
    
    resultsContainer.innerHTML = html;
}

function addKanjisFromBulkInput(listId) {
    const input = document.getElementById(`kanji-bulk-input-${listId}`).value.trim();
    
    if (!input) {
        alert('Пожалуйста, вставьте кандзи');
        return;
    }
    
    // Получаем все валидные кандзи из основной таблицы
    const validKanjisMap = new Map();
    document.querySelectorAll('[data-kanji]').forEach(el => {
        if (!el.closest(`#modal-edit-list-${listId}`)) {
            const kanji = el.dataset.kanji;
            if (!validKanjisMap.has(kanji)) {
                validKanjisMap.set(kanji, true);
            }
        }
    });
    
    // Парсим введенный текст - разделяем по запятым, пробелам и другим разделителям
    const kanjiChars = input
        .split(/[\s,，、]+/) // Разделяем по пробелам, запятым (обе версии) и другим разделителям
        .map(s => s.trim())
        .filter(s => s.length > 0);
    
    // Получаем текущий список кандзи в модальном окне
    const currentKanjisSet = new Set(
        Array.from(document.querySelectorAll(`#current-kanjis-${listId} span[style*="font-size"]`))
            .map(el => el.textContent.trim())
    );
    
    let addedCount = 0;
    let notFoundCount = 0;
    const notFound = [];
    
    kanjiChars.forEach(kanji => {
        if (currentKanjisSet.has(kanji)) {
            // Уже в списке, пропускаем
            return;
        }
        
        if (!validKanjisMap.has(kanji)) {
            // Кандзи не найдено в базе
            notFound.push(kanji);
            notFoundCount++;
            return;
        }
        
        // Добавляем кандзи
        addKanjiToListEdit(listId, kanji);
        addedCount++;
    });
    
    // Очищаем поле ввода
    document.getElementById(`kanji-bulk-input-${listId}`).value = '';
    
    // Показываем результат
    let message = `✓ Добавлено: ${addedCount}`;
    if (notFoundCount > 0) {
        message += `. ⚠️ Не найденные: ${notFound.slice(0, 5).join(', ')}${notFoundCount > 5 ? '...' : ''}`;
    }
    alert(message);
}

function addKanjiToListEdit(listId, kanji) {
    const currentKanjisContainer = document.getElementById(`current-kanjis-${listId}`);
    
    // Проверяем что кандзи еще не в списке
    const exists = Array.from(currentKanjisContainer.querySelectorAll('span[style*="font-size"]'))
        .some(el => el.textContent.trim() === kanji);
    
    if (exists) {
        return; // Уже в списке, пропускаем
    }
    
    // Если контейнер пуст или содержит сообщение "В списке нет", очищаем его
    if (currentKanjisContainer.textContent.includes('В списке нет')) {
        currentKanjisContainer.innerHTML = '';
    }
    
    // Проверяем нужно ли создавать новую строку
    let currentRow = currentKanjisContainer.querySelector('div[style*="display: flex"][style*="flex-wrap"]');
    
    if (!currentRow || currentRow.querySelectorAll('div').length >= 10) {
        // Создаем новую строку если текущей нет или она полная
        currentRow = document.createElement('div');
        currentRow.style.cssText = 'display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;';
        currentKanjisContainer.appendChild(currentRow);
    }
    
    // Добавляем новое кандзи в UI
    const kanjiEl = document.createElement('div');
    kanjiEl.style.cssText = 'background: #4b5563; padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #6b7280; display: inline-flex; align-items: center; gap: 0.5rem;';
    kanjiEl.innerHTML = `
        <span style="font-size: 1.25rem; font-family: 'Noto Sans JP', sans-serif;">${kanji}</span>
        <button type="button" onclick="this.parentElement.remove(); updateEditListUI(${listId});" 
                style="background: #dc2626; border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.875rem;">
            ✕
        </button>
    `;
    
    currentRow.appendChild(kanjiEl);
    updateEditListUI(listId);
}

function removeKanjiFromListEdit(listId, kanji) {
    // Находим и удаляем кандзи
    const container = document.getElementById(`current-kanjis-${listId}`);
    const kanjiElements = container.querySelectorAll('span[style*="font-size"]');
    
    kanjiElements.forEach(el => {
        if (el.textContent.trim() === kanji) {
            el.parentElement.remove();
        }
    });
    
    updateEditListUI(listId);
}

function updateEditListUI(listId) {
    // Обновляем счетчик кандзи
    const container = document.getElementById(`current-kanjis-${listId}`);
    const kanjiCount = container.querySelectorAll('span[style*="font-size"]').length;
    
    // Обновляем label
    const label = Array.from(document.querySelectorAll('label')).find(l => l.textContent.includes('Кандзи в списке'));
    if (label) {
        label.textContent = `Кандзи в списке (${kanjiCount})`;
    }
}

function closeEditListModal(listId) {
    document.getElementById(`modal-edit-list-${listId}`)?.remove();
}

function saveEditedList(listId) {
    const name = document.getElementById(`edit-list-name-${listId}`).value.trim();
    const description = document.getElementById(`edit-list-description-${listId}`).value.trim();
    const multipleChoiceOnly = document.getElementById(`edit-multiple-choice-only-${listId}`).checked;
    
    if (!name) {
        document.getElementById(`list-edit-error-${listId}`).textContent = 'Название не может быть пустым';
        document.getElementById(`list-edit-error-${listId}`).classList.remove('hidden');
        return;
    }
    
    // Получаем список кандзи из UI
    const container = document.getElementById(`current-kanjis-${listId}`);
    const kanjis = Array.from(container.querySelectorAll('span[style*="font-size"]'))
        .map(el => el.textContent.trim());
    
    // Сохраняем список на сервер
    fetch(`/kanji-lists/${listId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, description, multiple_choice_only: multipleChoiceOnly })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Теперь обновляем кандзи в списке
            // Сначала получаем текущий список кандзи
            fetch(`/kanji-lists/${listId}/kanjis`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                const currentKanjis = new Set(data.kanjis || []);
                const newKanjis = new Set(kanjis);
                
                // Удаляем кандзи которых больше нет
                const toRemove = Array.from(currentKanjis).filter(k => !newKanjis.has(k));
                // Добавляем новые кандзи
                const toAdd = Array.from(newKanjis).filter(k => !currentKanjis.has(k));
                
                let removed = 0;
                let added = 0;
                let errorOccurred = false;
                
                // Удаляем кандзи
                const removePromises = toRemove.map(kanji => 
                    fetch(`/kanji-lists/${listId}/toggle-kanji`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ kanji })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) removed++;
                    })
                    .catch(err => errorOccurred = true)
                );
                
                // Добавляем кандзи
                const addPromises = toAdd.map(kanji => 
                    fetch(`/kanji-lists/${listId}/toggle-kanji`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ kanji })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) added++;
                    })
                    .catch(err => errorOccurred = true)
                );
                
                Promise.all([...removePromises, ...addPromises]).then(() => {
                    if (errorOccurred) {
                        alert('Некоторые изменения не были сохранены');
                    }
                    closeEditListModal(listId);
                    loadKanjiLists();
                });
            })
            .catch(err => {
                console.error(err);
                alert('Ошибка обновления кандзи');
            });
        } else {
            document.getElementById(`list-edit-error-${listId}`).textContent = data.message || 'Ошибка сохранения';
            document.getElementById(`list-edit-error-${listId}`).classList.remove('hidden');
        }
    })
    .catch(err => {
        document.getElementById(`list-edit-error-${listId}`).textContent = 'Ошибка сети';
        document.getElementById(`list-edit-error-${listId}`).classList.remove('hidden');
        console.error(err);
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function deleteList(listId) {
    if (!confirm('Вы уверены? Список будет удален без возможности восстановления.')) return;
    
    fetch(`/kanji-lists/${listId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadKanjiLists();
        } else {
            alert(data.message || 'Ошибка удаления');
        }
    })
    .catch(err => {
        alert('Ошибка сети при удалении');
        console.error(err);
    });
}

// Функция для загрузки и отображения списков в модальном окне кандзи
function loadKanjiListsInModal(currentKanji) {
    const container = document.getElementById('kanji-lists-dropdown');
    if (!container) return;
    
    fetch('{{ route("kanji-lists.index") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.lists || data.lists.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm">Нет списков. Создайте список на странице управления.</p>';
            return;
        }
        
        let html = `
            <select id="kanji-lists-select" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">-- Выберите список для добавления --</option>
        `;
        
        data.lists.forEach(list => {
            const isInList = list.kanji_in_list && list.kanji_in_list.includes(currentKanji);
            const status = isInList ? ' ✓' : '';
            html += `<option value="${list.id}" data-is-in-list="${isInList ? '1' : '0'}">${list.name}${status}</option>`;
        });
        
        html += `</select>`;
        html += `<p class="text-gray-400 text-xs mt-2">Выберите список и нажмите "Добавить в список"</p>`;
        html += `<button type="button" id="add-to-list-btn" class="mt-3 w-full bg-purple-600 hover:bg-purple-500 text-white font-semibold px-4 py-2 rounded-lg transition">Добавить в список</button>`;
        
        container.innerHTML = html;
        
        // Обработчик кнопки добавления в список
        document.getElementById('add-to-list-btn')?.addEventListener('click', function() {
            const select = document.getElementById('kanji-lists-select');
            const listId = select.value;
            
            if (!listId) {
                alert('Выберите список');
                return;
            }
            
            // Определяем статус кандзи в списке
            const option = select.options[select.selectedIndex];
            const isInList = option.getAttribute('data-is-in-list') === '1';
            
            toggleKanjiInListFromModal(currentKanji, listId, select, option, isInList);
        });
    })
    .catch(err => {
        container.innerHTML = '<p class="text-red-400 text-sm">Ошибка загрузки списков</p>';
        console.error(err);
    });
}

// Переключение кандзи в списке из модального окна
function toggleKanjiInListFromModal(kanji, listId, select, option, isInList) {
    fetch(`/kanji-lists/${listId}/toggle-kanji`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ kanji })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Обновляем атрибут и текст опции
            const newStatus = data.added ? '1' : '0';
            option.setAttribute('data-is-in-list', newStatus);
            
            // Обновляем текст опции с символом
            const listName = option.textContent.replace(/ ✓$/, '').trim();
            if (data.added) {
                option.textContent = listName + ' ✓';
                alert('Кандзи добавлен в список!');
            } else {
                option.textContent = listName;
                alert('Кандзи удален из списка!');
            }
            
            // Сброс select к default опции
            select.value = '';
            
            // Обновляем списки на странице
            loadKanjiLists();
        } else {
            alert(data.message || 'Ошибка при добавлении в список');
        }
    })
    .catch(err => {
        alert('Ошибка сети');
        console.error(err);
    });
}

// Переключение кандзи в списке
function toggleKanjiInList(kanji, listId, buttonEl) {
    fetch(`/kanji-lists/${listId}/toggle-kanji`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ kanji })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.added) {
                buttonEl.classList.remove('bg-gray-600/50', 'text-gray-300', 'hover:bg-gray-600');
                buttonEl.classList.add('bg-purple-600/70', 'text-white');
                buttonEl.textContent = '✓ ' + buttonEl.textContent.substring(2);
            } else {
                buttonEl.classList.add('bg-gray-600/50', 'text-gray-300', 'hover:bg-gray-600');
                buttonEl.classList.remove('bg-purple-600/70', 'text-white');
                buttonEl.textContent = '○ ' + buttonEl.textContent.substring(2);
            }
            // Обновляем списки на странице
            loadKanjiLists();
        } else {
            alert(data.message || 'Ошибка при добавлении в список');
        }
    })
    .catch(err => {
        alert('Ошибка сети');
        console.error(err);
    });
}

// ========== Управление списками слов ==========
function loadWordLists() {
    console.log('Loading word lists...');
    fetch('{{ route("word-lists.index") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => {
        console.log('Response status:', r.status);
        return r.json();
    })
    .then(data => {
        console.log('Word lists data:', data);
        const container = document.getElementById('word-lists-container');
        
        if (!data.lists || data.lists.length === 0) {
            container.innerHTML = '<p class="text-gray-400">Нет списков. Создайте первый список!</p>';
            return;
        }
        
        let html = '<div class="space-y-6">';
        data.lists.forEach(list => {
            html += `
                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-semibold text-white text-lg">${escapeWordHtml(list.name)}</h4>
                                ${list.multiple_choice_only ? '<span class="bg-green-900/30 text-green-400 text-xs px-2 py-1 rounded border border-green-700/50">🎯 Только выбор</span>' : ''}
                            </div>
                            <p class="text-gray-400 text-sm">${escapeWordHtml(list.description || 'Без описания')}</p>
                            <p class="text-gray-500 text-xs mt-1">${list.word_count} слов</p>
                            <div style="width:220px; height:8px; background-color: rgba(75,85,99,0.35); border-radius:9999px; overflow:hidden; margin-top:8px;">
                                <div style="height:100%; width: ${list.progress_percent || 0}%; background: linear-gradient(90deg, #a855f7 0%, #6366f1 100%); border-radius:9999px; transition: width 0.3s ease; box-shadow: 0 0 6px rgba(168,85,247,0.35);"></div>
                            </div>
                            <p class="text-gray-400 text-xs mt-1">Прогресс: ${list.progress_percent || 0}% — ${list.completed_count || 0} завершено</p>
                            <p class="text-gray-400 text-xs mt-1">📚 Повторений: ${list.repetitions_completed || 0}</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button onclick="openEditWordListModal(${list.id})" class="bg-blue-600 hover:bg-blue-500 px-3 py-2 rounded text-sm text-white">✏️</button>
                            <button onclick="deleteWordList(${list.id})" class="bg-red-600 hover:bg-red-500 px-3 py-2 rounded text-sm text-white">🗑️</button>
                            <a href="{{ route('kanji.word-quiz') }}?list_id=${list.id}${list.progress_percent === 100 ? '&count=' + list.word_count : ''}" class="bg-purple-600 hover:bg-purple-500 px-3 py-2 rounded text-sm text-white">▶️ Квиз</a>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    })
    .catch(err => {
        console.error('Error loading word lists:', err);
        document.getElementById('word-lists-container').innerHTML = '<p class="text-red-400">Ошибка загрузки списков</p>';
    });
}

function openCreateWordListModal() {
    const modalHtml = `
        <div id="modal-create-word-list" style="position: fixed; inset: 0; display: flex; justify-content: center; align-items: center; background: rgba(0, 0, 0, 0.5); z-index: 50;" class="modal-backdrop">
            <div style="background: #2d3748; border-radius: 12px; padding: 2rem; width: 90%; max-width: 500px; border: 1px solid #4b5563;" class="modal-content">
                <h2 class="text-2xl font-bold text-purple-400 mb-4">Создать новый список слов</h2>
                <input type="text" id="word-list-name" placeholder="Название списка" 
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-3">
                <textarea id="word-list-description" placeholder="Описание (опционально)" 
                          class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-4 h-20"></textarea>
                <div class="flex gap-2 justify-end">
                    <button onclick="closeCreateWordListModal()" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded text-white">Отмена</button>
                    <button onclick="saveNewWordList()" class="bg-purple-600 hover:bg-purple-500 px-4 py-2 rounded text-white">Создать</button>
                </div>
                <p id="word-list-create-error" class="text-red-400 text-sm mt-3 hidden"></p>
            </div>
        </div>
    `;
    
    const existing = document.getElementById('modal-create-word-list');
    if (existing) existing.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    // Backdrop clicks should not close the modal; close only via explicit controls
}

function closeCreateWordListModal() {
    document.getElementById('modal-create-word-list')?.remove();
}

function saveNewWordList() {
    const name = document.getElementById('word-list-name').value.trim();
    const description = document.getElementById('word-list-description').value.trim();
    
    if (!name) {
        document.getElementById('word-list-create-error').textContent = 'Название не может быть пустым';
        document.getElementById('word-list-create-error').classList.remove('hidden');
        return;
    }
    
    fetch('{{ route("word-lists.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, description })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeCreateWordListModal();
            loadWordLists();
        } else {
            document.getElementById('word-list-create-error').textContent = data.message || 'Ошибка создания';
            document.getElementById('word-list-create-error').classList.remove('hidden');
        }
    })
    .catch(err => {
        document.getElementById('word-list-create-error').textContent = 'Ошибка сети';
        document.getElementById('word-list-create-error').classList.remove('hidden');
        console.error(err);
    });
}

function openEditWordListModal(listId) {
    fetch('{{ route("word-lists.index") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const currentList = data.lists.find(l => l.id === listId);
        if (!currentList) {
            alert('Список не найден');
            return;
        }
        
        displayEditWordListModal(listId, currentList);
    })
    .catch(err => {
        alert('Ошибка загрузки данных списка');
        console.error(err);
    });
}

function displayEditWordListModal(listId, currentList) {
    const modalHtml = `
        <div id="modal-edit-word-list-${listId}" style="position: fixed; inset: 0; display: flex; justify-content: center; align-items: center; background: rgba(0, 0, 0, 0.5); z-index: 50; overflow-y: auto;" class="modal-backdrop">
            <div style="background: #2d3748; border-radius: 12px; padding: 2rem; width: 90%; max-width: 700px; border: 1px solid #4b5563; margin: 2rem auto;" class="modal-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 class="text-2xl font-bold text-purple-400">Редактировать список слов</h2>
                    <button onclick="closeEditWordListModal(${listId})" style="background: none; border: none; color: #9ca3af; font-size: 1.5rem; cursor: pointer;">×</button>
                </div>
                
                <div style="max-height: 600px; overflow-y: auto;">
                    <div style="margin-bottom: 1.5rem;">
                        <label class="text-white text-sm block mb-2">Название списка</label>
                        <input type="text" id="edit-word-list-name-${listId}" placeholder="Название списка" 
                               value="${escapeWordHtml(currentList.name)}"
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-3">
                        
                        <label class="text-white text-sm block mb-2">Описание</label>
                        <textarea id="edit-word-list-description-${listId}" placeholder="Описание (опционально)" 
                                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 h-20">${escapeWordHtml(currentList.description || '')}</textarea>
                        
                        <!-- Опция: Только множественный выбор -->
                        <div style="margin-top: 1rem; padding: 0.75rem; background: #374151; border-radius: 6px; border-left: 3px solid #10b981;">
                            <label class="flex items-center cursor-pointer" style="gap: 0.75rem;">
                                <input type="checkbox" id="edit-word-multiple-choice-only-${listId}" 
                                       ${currentList.multiple_choice_only ? 'checked' : ''}
                                       class="w-4 h-4" style="cursor: pointer;">
                                <span class="text-white text-sm font-medium">🎯 Только множественный выбор</span>
                            </label>
                            <p class="text-gray-400 text-xs mt-1">Если включено, квиз всегда будет с вариантами ответов, даже для продвинутых уровней</p>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem; background: #1f2937; border-left: 4px solid #8b5cf6; padding: 1rem; border-radius: 6px;">
                        <label class="text-white text-sm block mb-2">🔎 Найти и добавить слово</label>
                        <p class="text-gray-400 text-xs mb-2">Поиск по слову, чтению или переводу. Выберите слово из подсказок.</p>
                        <input id="word-search-input-${listId}" type="text" placeholder="Поиск слова..." 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-2">
                        <div id="word-search-suggestions-${listId}" class="bg-gray-800/30 rounded-lg p-2 border border-gray-600" style="max-height: 200px; overflow-y: auto;"></div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label class="text-white text-sm block mb-2">✏️ Слова в списке (${currentList.word_count})</label>
                        <div id="current-words-${listId}" class="bg-gray-700/50 rounded-lg p-4 border border-gray-600" style="min-height: 100px;">
                            ${renderCurrentWords(listId, currentList)}
                        </div>
                    </div>
                </div>
                
                <p id="word-list-edit-error-${listId}" class="text-red-400 text-sm mb-3 hidden"></p>
                
                <div class="flex gap-2 justify-end">
                    <button onclick="closeEditWordListModal(${listId})" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded text-white font-medium transition">Отмена</button>
                    <button onclick="saveEditedWordList(${listId})" class="bg-purple-600 hover:bg-purple-500 px-4 py-2 rounded text-white font-medium transition">Сохранить</button>
                </div>
            </div>
        </div>
    `;
    
    const existing = document.getElementById(`modal-edit-word-list-${listId}`);
    if (existing) existing.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = document.getElementById(`modal-edit-word-list-${listId}`);
    // Backdrop clicks should not close the modal; close only via explicit controls (close button)
    
    // Load detailed word info for existing IDs to show readable labels
    try {
        const ids = (currentList.word_ids_in_list || []).join(',');
        if (ids) {
            fetch(`/dictionary/batch?ids=${encodeURIComponent(ids)}`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (data.words && data.words.length) {
                        const container = document.getElementById(`current-words-${listId}`);
                        container.innerHTML = '';
                        data.words.forEach(w => addWordToListEdit(listId, w.id, w.japanese_word));
                    }
                })
                .catch(err => console.error('Batch load error', err));
        }
    } catch (e) {
        console.error(e);
    }
    // Attach search input handler
    const searchInput = document.getElementById(`word-search-input-${listId}`);
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const q = this.value.trim();
            const suggestionsEl = document.getElementById(`word-search-suggestions-${listId}`);
            if (!q) { suggestionsEl.innerHTML = ''; return; }
            searchWordsDebounced(listId, q);
        });
    }
}

function renderCurrentWords(listId, currentList) {
    if (!currentList.word_ids_in_list || currentList.word_ids_in_list.length === 0) {
        return '<p class="text-gray-400 text-sm">В списке нет слов</p>';
    }
    
    const chunkSize = 10;
    let html = '<div style="display: inline-block; width: 100%;">';
    
    for (let i = 0; i < currentList.word_ids_in_list.length; i += chunkSize) {
        html += '<div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">';
        
        const chunk = currentList.word_ids_in_list.slice(i, i + chunkSize);
        chunk.forEach(wordId => {
            html += `
                <div style="background: #4b5563; padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #6b7280; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.875rem;">ID: ${wordId}</span>
                    <button type="button" onclick="removeWordFromListEdit(${listId}, ${wordId})" 
                            style="background: #dc2626; border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.875rem;">
                        ✕
                    </button>
                </div>
            `;
        });
        
        html += '</div>';
    }
    
    html += '</div>';
    return html;
}

// Search words in local dictionary and show suggestions
let _wordSearchDebounce = null;
function searchWordsDebounced(listId, q) {
    clearTimeout(_wordSearchDebounce);
    _wordSearchDebounce = setTimeout(() => searchWords(listId, q), 250);
}

function searchWords(listId, q) {
    const suggestionsEl = document.getElementById(`word-search-suggestions-${listId}`);
    suggestionsEl.innerHTML = '<p class="text-gray-400 text-sm">Идёт поиск...</p>';

    fetch(`/dictionary/search?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (!data.words || data.words.length === 0) {
                suggestionsEl.innerHTML = '<p class="text-gray-400 text-sm">Ничего не найдено</p>';
                return;
            }

            let html = '';
            data.words.forEach(w => {
                html += `<div data-word-id="${w.id}" data-word-text="${encodeURIComponent(w.japanese_word || '')}" style="padding:0.35rem 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.03); cursor: pointer;" onclick="selectSuggestedWord(${listId}, this)">` +
                        `<strong class=\"text-white\">${escapeWordHtml(w.japanese_word)}</strong> <span class=\"text-gray-400 text-sm\">${escapeWordHtml(w.reading || '')}</span> <div class=\"text-gray-300 text-xs\">${escapeWordHtml(w.translation_ru || '')}</div>` +
                        `</div>`;
            });
            suggestionsEl.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            suggestionsEl.innerHTML = '<p class="text-red-400 text-sm">Ошибка поиска</p>';
        });
}

function selectSuggestedWord(listId, el) {
    const wordId = parseInt(el.getAttribute('data-word-id'));
    const wordText = decodeURIComponent(el.getAttribute('data-word-text') || '');
    fetch(`/word-lists/${listId}/toggle-word`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ word_id: wordId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            addWordToListEdit(listId, wordId, wordText);
            loadWordLists();
        } else {
            alert(data.message || 'Ошибка добавления');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Ошибка сети');
    });
}

function addWordToListEdit(listId, wordId, displayText) {
    const currentWordsContainer = document.getElementById(`current-words-${listId}`);

    const exists = Array.from(currentWordsContainer.querySelectorAll('[data-word-id]'))
        .some(el => parseInt(el.getAttribute('data-word-id')) === wordId);

    if (exists) return;

    if (currentWordsContainer.textContent.includes('В списке нет')) {
        currentWordsContainer.innerHTML = '';
    }

    let currentRow = currentWordsContainer.querySelector('div[style*="display: flex"][style*="flex-wrap"]');
    if (!currentRow || currentRow.querySelectorAll('div').length >= 10) {
        currentRow = document.createElement('div');
        currentRow.style.cssText = 'display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;';
        currentWordsContainer.appendChild(currentRow);
    }

    const wordEl = document.createElement('div');
    wordEl.setAttribute('data-word-id', wordId);
    wordEl.style.cssText = 'background: #4b5563; padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #6b7280; display: inline-flex; align-items: center; gap: 0.5rem;';
    const label = displayText ? `${escapeWordHtml(displayText)} (ID: ${wordId})` : `ID: ${wordId}`;
    wordEl.innerHTML = `
        <span style="font-size: 0.875rem;">${label}</span>
        <button type="button" onclick="this.parentElement.remove(); updateEditWordListUI(${listId});" 
                style="background: #dc2626; border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.875rem;">
            ✕
        </button>
    `;

    currentRow.appendChild(wordEl);
    updateEditWordListUI(listId);
}

function removeWordFromListEdit(listId, wordId) {
    const container = document.getElementById(`current-words-${listId}`);
    // Try to remove element marked with data-word-id
    const el = container.querySelector(`[data-word-id="${wordId}"]`);
    if (el) {
        el.remove();
    } else {
        const wordElements = container.querySelectorAll('span');
        wordElements.forEach(el2 => {
            const match = el2.textContent.match(/\d+/);
            if (match && parseInt(match[0]) === wordId) {
                el2.parentElement.remove();
            }
        });
    }
    
    updateEditWordListUI(listId);
}

function updateEditWordListUI(listId) {
    const container = document.getElementById(`current-words-${listId}`);
    const wordCount = container.querySelectorAll('[data-word-id]').length || container.querySelectorAll('span').length;
    
    const label = Array.from(document.querySelectorAll('label')).find(l => l.textContent.includes('Слова в списке'));
    if (label) {
        label.textContent = `✏️ Слова в списке (${wordCount})`;
    }
}

function closeEditWordListModal(listId) {
    document.getElementById(`modal-edit-word-list-${listId}`)?.remove();
}

function saveEditedWordList(listId) {
    const name = document.getElementById(`edit-word-list-name-${listId}`).value.trim();
    const description = document.getElementById(`edit-word-list-description-${listId}`).value.trim();
    const multipleChoiceOnly = document.getElementById(`edit-word-multiple-choice-only-${listId}`).checked;
    
    if (!name) {
        document.getElementById(`word-list-edit-error-${listId}`).textContent = 'Название не может быть пустым';
        document.getElementById(`word-list-edit-error-${listId}`).classList.remove('hidden');
        return;
    }
    
    const container = document.getElementById(`current-words-${listId}`);
    const wordIds = Array.from(container.querySelectorAll('[data-word-id]'))
        .map(el => parseInt(el.getAttribute('data-word-id')))
        .filter(id => !isNaN(id));
    
    fetch(`/word-lists/${listId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, description, multiple_choice_only: multipleChoiceOnly })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            fetch(`/word-lists/${listId}/words`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                const currentWords = new Set(data.words || []);
                const newWords = new Set(wordIds);
                
                const toRemove = Array.from(currentWords).filter(w => !newWords.has(w));
                const toAdd = Array.from(newWords).filter(w => !currentWords.has(w));
                
                let removed = 0;
                let added = 0;
                let errorOccurred = false;
                
                const removePromises = toRemove.map(wordId => 
                    fetch(`/word-lists/${listId}/toggle-word`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ word_id: wordId })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) removed++;
                    })
                    .catch(err => errorOccurred = true)
                );
                
                const addPromises = toAdd.map(wordId => 
                    fetch(`/word-lists/${listId}/toggle-word`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ word_id: wordId })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) added++;
                    })
                    .catch(err => errorOccurred = true)
                );
                
                Promise.all([...removePromises, ...addPromises]).then(() => {
                    if (errorOccurred) {
                        alert('Некоторые изменения не были сохранены');
                    }
                    closeEditWordListModal(listId);
                    loadWordLists();
                });
            })
            .catch(err => {
                console.error(err);
                alert('Ошибка обновления слов');
            });
        } else {
            document.getElementById(`word-list-edit-error-${listId}`).textContent = data.message || 'Ошибка сохранения';
            document.getElementById(`word-list-edit-error-${listId}`).classList.remove('hidden');
        }
    })
    .catch(err => {
        document.getElementById(`word-list-edit-error-${listId}`).textContent = 'Ошибка сети';
        document.getElementById(`word-list-edit-error-${listId}`).classList.remove('hidden');
        console.error(err);
    });
}

function deleteWordList(listId) {
    if (!confirm('Вы уверены? Список будет удален без возможности восстановления.')) return;
    
    fetch(`/word-lists/${listId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadWordLists();
        } else {
            alert(data.message || 'Ошибка удаления');
        }
    })
    .catch(err => {
        alert('Ошибка сети при удалении');
        console.error(err);
    });
}

function escapeWordHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>
@endpush
@endsection
