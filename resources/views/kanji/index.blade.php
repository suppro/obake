@extends('layouts.app')

@section('title', 'Изучение кандзи')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-purple-400 mb-2">📚 Изучение кандзи</h1>
        <p class="text-gray-400">Изучайте кандзи в формате квиза</p>
    </div>

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
        <form method="GET" action="{{ route('kanji.index') }}" id="search-form">
            <div class="flex items-center gap-4">
                <input type="text" 
                       name="search"
                       id="kanji-search" 
                       placeholder="Введите перевод или чтение (например: замок, しろ, じょう)" 
                       value="{{ $search ?? '' }}"
                       class="flex-1 bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <button type="submit" 
                        class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-purple-500/50">
                    Найти 🔍
                </button>
                @if($search ?? '')
                <a href="{{ route('kanji.index') }}" 
                   class="bg-gray-600 hover:bg-gray-500 text-white font-semibold px-6 py-3 rounded-lg transition-all">
                    Очистить
                </a>
                @endif
            </div>
        </form>
        <p class="text-gray-400 text-sm mt-2">Поиск работает по переводу на русский и чтению (хирагана/ромадзи). Фильтрация происходит в реальном времени при вводе.</p>
    </div>

    <!-- Кнопка начала квиза -->
    <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <h3 class="text-xl font-bold text-purple-400 mb-4">Начать квиз</h3>
        <form action="{{ route('kanji.quiz') }}" method="GET" class="space-y-4">
            <div class="flex flex-wrap items-center gap-4">
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

// Поиск кандзи
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('kanji-search');
    const kanjiList = document.getElementById('kanji-list');
    
    if (searchInput) {
        // Поиск при вводе (клиентская фильтрация в реальном времени)
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterKanji(this.value.trim());
            }, 200);
        });
    }
    
    function filterKanji(searchTerm) {
        if (!kanjiList) return;
        
        const searchLower = searchTerm.toLowerCase();
        const kanjiItems = kanjiList.querySelectorAll('.kanji-item');
        let visibleCount = 0;
        
        kanjiItems.forEach(item => {
            const translation = (item.getAttribute('data-translation') || '').toLowerCase();
            const reading = (item.getAttribute('data-reading') || '').toLowerCase();
            const kanji = (item.getAttribute('data-kanji') || '').toLowerCase();
            
            const matches = !searchTerm || 
                translation.includes(searchLower) || 
                reading.includes(searchLower) ||
                kanji.includes(searchLower);
            
            if (matches) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Скрываем пустые строки
        const rows = kanjiList.querySelectorAll('tr');
        rows.forEach(row => {
            const visibleItems = row.querySelectorAll('.kanji-item[style=""], .kanji-item:not([style*="display: none"])');
            if (visibleItems.length === 0) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
            }
        });
        
        // Скрываем секции уровней, если в них нет видимых кандзи
        const levelSections = kanjiList.querySelectorAll('.bg-gray-800\\/50');
        levelSections.forEach(section => {
            const visibleInSection = section.querySelectorAll('.kanji-item[style=""], .kanji-item:not([style*="display: none"])');
            if (visibleInSection.length === 0 && searchTerm) {
                section.style.display = 'none';
            } else {
                section.style.display = '';
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Закрытие модального окна
    function closeModal() {
        const modal = document.getElementById('kanji-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }
    }
    
    const closeModalBtn = document.getElementById('close-modal');
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    
    const modal = document.getElementById('kanji-modal');
    if (modal) {
        // Закрываем при клике на фон
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
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
});
</script>
@endpush
@endsection
