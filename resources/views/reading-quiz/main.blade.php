@extends('layouts.app')

@section('title', 'Квиз на чтение - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-bold text-blue-400 mb-2">📚 Квиз на чтение</h1>
            <p class="text-gray-400">Практикуйте чтение японских слов</p>
        </div>
    </div>

    <!-- Начать квиз -->
    <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <h3 class="text-xl font-bold text-blue-400 mb-4">⚡ Начать квиз</h3>
        <form action="{{ route('reading-quiz.quiz') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-gray-300">Список слов:</label>
                <select name="list_id" 
                       id="quiz-list-select"
                       class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Все слова</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-gray-300">Количество:</label>
                <input type="number" name="count" value="10" min="1" max="50" 
                       class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white w-24 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-blue-500/50 transform hover:scale-105">
                Начать квиз 🎯
            </button>
        </form>
    </div>

    <!-- Управление списками слов для чтения -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-blue-400">📋 Мои списки для чтения</h3>
            <button type="button" id="btn-create-reading-list" class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-semibold px-5 py-2.5 rounded-lg transition-all">
                ＋ Создать список
            </button>
        </div>
        
        <div id="reading-lists-container">
            <p class="text-gray-400 text-sm">Загрузка...</p>
        </div>
        <script>
            (function(){
                try {
                    fetch('{{ route("reading-quiz-lists.index") }}', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => {
                        const container = document.getElementById('reading-lists-container');
                        const select = document.getElementById('quiz-list-select');
                        if (!container) return;
                        if (!data.lists || data.lists.length === 0) {
                            container.innerHTML = '<p class="text-gray-400">Нет списков. Создайте первый список!</p>';
                            return;
                        }
                        // Очищаем select от старых опций (кроме первой "Все слова")
                        while (select.children.length > 1) {
                            select.removeChild(select.children[1]);
                        }
                        let html = '<div class="space-y-6">';
                        data.lists.forEach(list => {
                            html += `
                                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="font-semibold text-white text-lg">${list.name}</h4>
                                            <p class="text-gray-400 text-sm">${list.description || 'Без описания'}</p>
                                            <p class="text-gray-500 text-xs mt-1">${list.word_count} слов</p>
                                            <div style="width:220px; height:8px; background-color: rgba(75,85,99,0.35); border-radius:9999px; overflow:hidden; margin-top:8px;">
                                                <div style="height:100%; width: ${list.progress_percent || 0}%; background: linear-gradient(90deg, #0ea5e9 0%, #06b6d4 100%); border-radius:9999px; transition: width 0.3s ease; box-shadow: 0 0 6px rgba(14,165,233,0.35);"></div>
                                            </div>
                                            <p class="text-gray-400 text-xs mt-1">Прогресс: ${list.progress_percent || 0}% — ${list.completed_count || 0} завершено</p>
                                            <p class="text-gray-400 text-xs mt-1">📚 Повторений: ${list.repetitions_completed || 0}</p>
                                        </div>
                                        <div class="flex gap-2 flex-shrink-0">
                                            <button onclick="openEditReadingListModal(${list.id})" class="bg-blue-600 hover:bg-blue-500 px-3 py-2 rounded text-sm text-white">✏️</button>
                                            <button onclick="deleteReadingList(${list.id})" class="bg-red-600 hover:bg-red-500 px-3 py-2 rounded text-sm text-white">🗑️</button>
                                            <a href="{{ route('reading-quiz.quiz') }}?list_id=${list.id}${list.progress_percent === 100 ? '&count=' + list.word_count : ''}" class="bg-blue-600 hover:bg-blue-500 px-3 py-2 rounded text-sm text-white">▶️ Квиз</a>
                                        </div>
                                    </div>
                            `;
                            
                            // Добавляем таблицу со словами если нужно (зависит от дизайна)
                            if (list.word_ids_in_list && list.word_ids_in_list.length > 0) {
                                html += `<div class="text-gray-400 text-sm mt-2">Слова: ${list.word_ids_in_list.join(', ')}</div>`;
                            }
                            
                            html += '</div>';
                            
                            // Добавляем опцию в select
                            const option = document.createElement('option');
                            option.value = list.id;
                            option.textContent = `${list.name} (${list.word_count})`;
                            select.appendChild(option);
                        });
                        html += '</div>';
                        container.innerHTML = html;
                    })
                    .catch(err => {
                        const container = document.getElementById('reading-lists-container');
                        if (container) container.innerHTML = '<p class="text-red-400">Ошибка загрузки списков</p>';
                        console.error(err);
                    });
                } catch (e) { console.error(e); }
            })();
        </script>
    </div>

    <!-- Список слов для чтения -->
    <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <h3 class="text-xl font-bold text-blue-400">Список слов для чтения</h3>
            <button type="button" id="btn-add-reading-word" class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-semibold px-5 py-2.5 rounded-lg transition-all">
                ＋ Добавить слово
            </button>
        </div>
        @if(isset($wordsWithProgress) && $wordsWithProgress->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="reading-words-list-body">
                @foreach($wordsWithProgress as $w)
                    <div class="reading-word-card bg-gray-700/50 border border-gray-600 rounded-xl p-4 hover:border-blue-500/50 transition-all flex flex-col" data-word-id="{{ $w['id'] }}" data-word="{{ $w['japanese_word'] }}" data-reading="{{ $w['reading'] }}" data-translation="{{ e($w['translation_ru']) }}">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="min-w-0 flex-1">
                                <div class="text-2xl font-bold text-white japanese-font truncate" style="font-family: 'Noto Sans JP', sans-serif;">{{ $w['japanese_word'] }}</div>
                                @if($w['reading'])
                                    <div class="text-sm text-gray-400 japanese-font">{{ $w['reading'] }}</div>
                                @endif
                            </div>
                            <div class="flex gap-1 flex-shrink-0">
                                <button type="button" class="reading-word-edit-btn text-blue-400 hover:text-blue-300 p-1 rounded" title="Редактировать" data-word-id="{{ $w['id'] }}">✏️</button>
                                <button type="button" class="reading-word-delete-btn text-red-400 hover:text-red-300 p-1 rounded" title="Удалить" data-word-id="{{ $w['id'] }}">🗑️</button>
                            </div>
                        </div>
                        <div class="text-gray-300 text-sm mb-3 line-clamp-2 flex-1">{{ $w['translation_ru'] }}</div>
                        <div class="mt-auto">
                            <div style="width: 100%; height: 6px; background-color: rgba(75, 85, 99, 0.5); border-radius: 9999px; overflow: hidden; position: relative;">
                                <div style="height: 100%; width: {{ $w['progress_percent'] }}%; background: linear-gradient(90deg, #0ea5e9 0%, #06b6d4 100%); border-radius: 9999px; transition: width 0.3s ease; box-shadow: 0 0 4px rgba(14, 165, 233, 0.4);"></div>
                            </div>
                            <span class="text-xs text-gray-500 mt-0.5">{{ (int)$w['progress_percent'] }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400">У вас пока нет слов для чтения. Нажмите «Добавить слово», чтобы добавить первое.</p>
        @endif
    </div>
</div>

<!-- Модальные окна -->

<!-- Модальное окно: добавить слово для чтения -->
<div id="modal-add-reading-word" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" aria-hidden="true">
    <div class="bg-gray-800 rounded-2xl shadow-2xl w-full border border-gray-700 mx-4 max-h-[90vh] overflow-y-auto" style="max-width:680px;">
        <div class="p-6 border-b border-gray-700 flex justify-between items-center sticky top-0 bg-gray-800 z-10">
            <h3 class="text-xl font-bold text-blue-400">Добавить слово для чтения</h3>
            <button type="button" id="modal-add-reading-word-close" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-gray-300 mb-1">Японское слово *</label>
                <input type="text" id="add-reading-japanese-word" placeholder="例えば: 日本 или にほん" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white japanese-font focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-300 mb-1">Чтение (хирагана/катакана) *</label>
                <input type="text" id="add-reading-reading" placeholder="例えば: にほん" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-300 mb-1">Перевод (RU) *</label>
                <input type="text" id="add-reading-translation-ru" placeholder="Например: Япония" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-300 mb-1">Перевод (EN)</label>
                <input type="text" id="add-reading-translation-en" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3">
                <button type="button" id="add-reading-word-submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition">Добавить</button>
                <button type="button" id="add-reading-word-cancel" class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2.5 rounded-lg transition">Отмена</button>
            </div>
            <p id="add-reading-word-message" class="mt-2 text-sm hidden"></p>
        </div>
    </div>
</div>

<!-- Модальное окно: редактировать слово для чтения -->
<div id="modal-edit-reading-word" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" aria-hidden="true">
    <div class="bg-gray-800 rounded-2xl shadow-2xl w-full border border-gray-700 mx-4 max-h-[90vh] overflow-y-auto" style="max-width:680px;">
        <div class="p-6 border-b border-gray-700 flex justify-between items-center sticky top-0 bg-gray-800 z-10">
            <h3 class="text-xl font-bold text-blue-400">Редактировать слово</h3>
            <button type="button" id="modal-edit-reading-word-close" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="edit-reading-word-id">
            <div>
                <label class="block text-gray-300 mb-1">Японское слово *</label>
                <input type="text" id="edit-reading-japanese-word" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white japanese-font focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-300 mb-1">Чтение *</label>
                <input type="text" id="edit-reading-reading" placeholder="хирагана/катакана" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-300 mb-1">Перевод (RU) *</label>
                <input type="text" id="edit-reading-translation-ru" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-300 mb-1">Перевод (EN)</label>
                <input type="text" id="edit-reading-translation-en" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3">
                <button type="button" id="edit-reading-word-submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition">Сохранить</button>
                <button type="button" id="edit-reading-word-cancel" class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2.5 rounded-lg transition">Отмена</button>
            </div>
            <p id="edit-reading-word-message" class="mt-2 text-sm hidden"></p>
        </div>
    </div>
</div>

<!-- Модальное окно: создать список для чтения -->
<div id="modal-create-reading-list" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" aria-hidden="true">
    <div class="bg-gray-800 rounded-2xl shadow-2xl w-full border border-gray-700 mx-4" style="max-width:500px;">
        <div class="p-6 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-blue-400">Создать новый список</h3>
            <button type="button" id="modal-create-reading-list-close" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <input type="text" id="create-reading-list-name" placeholder="Название списка" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <textarea id="create-reading-list-description" placeholder="Описание (опционально)" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 h-20"></textarea>
            <div class="flex gap-3">
                <button type="button" id="create-reading-list-submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition">Создать</button>
                <button type="button" id="create-reading-list-cancel" class="flex-1 bg-gray-600 hover:bg-gray-500 text-white px-5 py-2.5 rounded-lg transition">Отмена</button>
            </div>
            <p id="create-reading-list-message" class="mt-2 text-sm hidden"></p>
        </div>
    </div>
</div>

<!-- Модальное окно: редактировать список для чтения -->
<div id="modal-edit-reading-list" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" aria-hidden="true">
    <div class="bg-gray-800 rounded-2xl shadow-2xl w-full border border-gray-700 mx-4" style="max-width:500px;">
        <div class="p-6 border-b border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-blue-400">Редактировать список</h3>
            <button type="button" id="modal-edit-reading-list-close" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="edit-reading-list-id">
            <div>
                <label class="block text-gray-300 mb-1">Название списка</label>
                <input type="text" id="edit-reading-list-name" placeholder="Название списка" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-300 mb-1">Описание</label>
                <textarea id="edit-reading-list-description" placeholder="Описание (опционально)" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 h-20"></textarea>
            </div>
            <div class="border-t border-gray-600 pt-4">
                <div class="flex justify-between items-center mb-3">
                    <label class="block text-gray-300 font-semibold">Слова в списке</label>
                    <button type="button" id="edit-reading-list-add-words-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-sm">＋ Добавить</button>
                </div>
                <div id="edit-reading-list-words-display" class="bg-gray-700/50 rounded-lg p-3 max-h-40 overflow-y-auto">
                    <p class="text-gray-400 text-sm">Загрузка...</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" id="edit-reading-list-submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition">Сохранить</button>
                <button type="button" id="edit-reading-list-cancel" class="flex-1 bg-gray-600 hover:bg-gray-500 text-white px-5 py-2.5 rounded-lg transition">Отмена</button>
            </div>
            <p id="edit-reading-list-message" class="mt-2 text-sm hidden"></p>
        </div>
    </div>
</div>

<!-- Модальное окно: добавить слова в список при редактировании -->
<div id="modal-edit-reading-list-add-words" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" aria-hidden="true">
    <div class="bg-gray-800 rounded-2xl shadow-2xl w-full border border-gray-700 mx-4 max-h-[90vh] overflow-y-auto" style="max-width:600px;">
        <div class="p-6 border-b border-gray-700 flex justify-between items-center sticky top-0 bg-gray-800 z-10">
            <h3 class="text-xl font-bold text-blue-400">Добавить слова в список</h3>
            <button type="button" id="modal-edit-reading-list-add-words-close" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>
        <div class="p-6">
            <input type="text" id="edit-reading-list-words-search" placeholder="Поиск по слову, чтению или переводу..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4">
            <div id="edit-reading-list-words-to-add-list" class="space-y-2 mb-4 max-h-64 overflow-y-auto">
                <p class="text-gray-400 text-center py-4">Загрузка...</p>
            </div>
            <div class="flex gap-3">
                <button type="button" id="edit-reading-list-add-words-confirm" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg transition">Готово</button>
                <button type="button" id="edit-reading-list-add-words-cancel" class="flex-1 bg-gray-600 hover:bg-gray-500 text-white px-5 py-2.5 rounded-lg transition">Отмена</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentEditReadingListId = null;
let currentEditReadingListAllWords = [];
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

// Показать/скрыть модальные окна
function showReadingModal(id) { const m = document.getElementById(id); if (m) m.style.display = 'flex'; }
function hideReadingModal(id) { const m = document.getElementById(id); if (m) m.style.display = 'none'; }

// Закрытие модальных окон по клику на background
['modal-add-reading-word', 'modal-edit-reading-word', 'modal-create-reading-list', 'modal-edit-reading-list', 'modal-edit-reading-list-add-words'].forEach(id => {
    const m = document.getElementById(id);
    if (m) m.addEventListener('click', e => { if (e.target === m) hideReadingModal(id); });
});

// Кнопки закрытия
document.getElementById('modal-add-reading-word-close')?.addEventListener('click', () => hideReadingModal('modal-add-reading-word'));
document.getElementById('add-reading-word-cancel')?.addEventListener('click', () => hideReadingModal('modal-add-reading-word'));
document.getElementById('modal-edit-reading-word-close')?.addEventListener('click', () => hideReadingModal('modal-edit-reading-word'));
document.getElementById('edit-reading-word-cancel')?.addEventListener('click', () => hideReadingModal('modal-edit-reading-word'));
document.getElementById('modal-create-reading-list-close')?.addEventListener('click', () => hideReadingModal('modal-create-reading-list'));
document.getElementById('create-reading-list-cancel')?.addEventListener('click', () => hideReadingModal('modal-create-reading-list'));
document.getElementById('modal-edit-reading-list-close')?.addEventListener('click', () => hideReadingModal('modal-edit-reading-list'));
document.getElementById('edit-reading-list-cancel')?.addEventListener('click', () => hideReadingModal('modal-edit-reading-list'));
document.getElementById('modal-edit-reading-list-add-words-close')?.addEventListener('click', () => hideReadingModal('modal-edit-reading-list-add-words'));
document.getElementById('edit-reading-list-add-words-cancel')?.addEventListener('click', () => hideReadingModal('modal-edit-reading-list-add-words'));

// Кнопка добавления слова
document.getElementById('btn-add-reading-word')?.addEventListener('click', () => {
    document.getElementById('add-reading-japanese-word').value = '';
    document.getElementById('add-reading-reading').value = '';
    document.getElementById('add-reading-translation-ru').value = '';
    document.getElementById('add-reading-translation-en').value = '';
    document.getElementById('add-reading-word-message').classList.add('hidden');
    showReadingModal('modal-add-reading-word');
});

// Кнопка добавления слова - подтверждение
document.getElementById('add-reading-word-submit')?.addEventListener('click', () => {
    const japaneseWord = document.getElementById('add-reading-japanese-word').value.trim();
    const reading = document.getElementById('add-reading-reading').value.trim();
    const translationRu = document.getElementById('add-reading-translation-ru').value.trim();
    
    if (!japaneseWord || !reading || !translationRu) {
        document.getElementById('add-reading-word-message').textContent = 'Заполните обязательные поля';
        document.getElementById('add-reading-word-message').classList.remove('hidden');
        return;
    }
    
    fetch('{{ route("reading-quiz-words.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            japanese_word: japaneseWord,
            reading: reading,
            translation_ru: translationRu,
            translation_en: document.getElementById('add-reading-translation-en').value.trim() || null
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            hideReadingModal('modal-add-reading-word');
            location.reload();
        } else {
            document.getElementById('add-reading-word-message').textContent = data.error || 'Ошибка';
            document.getElementById('add-reading-word-message').classList.remove('hidden');
        }
    })
    .catch(() => {
        document.getElementById('add-reading-word-message').textContent = 'Ошибка сети';
        document.getElementById('add-reading-word-message').classList.remove('hidden');
    });
});

// Кнопка редактирования слова
document.querySelectorAll('.reading-word-edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const wordId = this.getAttribute('data-word-id');
        fetch('{{ route("reading-quiz-words.index") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const word = (data.data || []).find(w => w.id === parseInt(wordId));
            if (word) {
                document.getElementById('edit-reading-word-id').value = word.id;
                document.getElementById('edit-reading-japanese-word').value = word.japanese_word || '';
                document.getElementById('edit-reading-reading').value = word.reading || '';
                document.getElementById('edit-reading-translation-ru').value = word.translation_ru || '';
                document.getElementById('edit-reading-translation-en').value = word.translation_en || '';
                document.getElementById('edit-reading-word-message').classList.add('hidden');
                showReadingModal('modal-edit-reading-word');
            }
        });
    });
});

// Кнопка редактирования слова - подтверждение
document.getElementById('edit-reading-word-submit')?.addEventListener('click', () => {
    const wordId = document.getElementById('edit-reading-word-id').value;
    
    fetch('{{ url("/reading-quiz-words") }}/' + wordId, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            japanese_word: document.getElementById('edit-reading-japanese-word').value,
            reading: document.getElementById('edit-reading-reading').value,
            translation_ru: document.getElementById('edit-reading-translation-ru').value,
            translation_en: document.getElementById('edit-reading-translation-en').value
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            hideReadingModal('modal-edit-reading-word');
            location.reload();
        } else {
            document.getElementById('edit-reading-word-message').textContent = data.error || 'Ошибка';
            document.getElementById('edit-reading-word-message').classList.remove('hidden');
        }
    });
});

// Кнопка удаления слова
document.querySelectorAll('.reading-word-delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Удалить это слово?')) return;
        const wordId = this.getAttribute('data-word-id');
        fetch('{{ url("/reading-quiz-words") }}/' + wordId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Ошибка удаления');
            }
        });
    });
});

// Кнопка создания списка
document.getElementById('btn-create-reading-list')?.addEventListener('click', () => {
    document.getElementById('create-reading-list-name').value = '';
    document.getElementById('create-reading-list-description').value = '';
    document.getElementById('create-reading-list-message').classList.add('hidden');
    showReadingModal('modal-create-reading-list');
});

document.getElementById('create-reading-list-submit')?.addEventListener('click', () => {
    const name = document.getElementById('create-reading-list-name').value.trim();
    if (!name) {
        document.getElementById('create-reading-list-message').textContent = 'Введите название';
        document.getElementById('create-reading-list-message').classList.remove('hidden');
        return;
    }
    
    fetch('{{ route("reading-quiz-lists.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: name,
            description: document.getElementById('create-reading-list-description').value
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            hideReadingModal('modal-create-reading-list');
            location.reload();
        } else {
            document.getElementById('create-reading-list-message').textContent = data.error || 'Ошибка';
            document.getElementById('create-reading-list-message').classList.remove('hidden');
        }
    });
});

// Функция редактирования списка
window.openEditReadingListModal = function(listId) {
    currentEditReadingListId = listId;
    fetch('{{ route("reading-quiz-lists.index") }}', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(d => {
        const l = (d.lists || []).find(x => x.id === listId);
        if (l) {
            document.getElementById('edit-reading-list-id').value = l.id;
            document.getElementById('edit-reading-list-name').value = l.name || '';
            document.getElementById('edit-reading-list-description').value = l.description || '';
            document.getElementById('edit-reading-list-message').classList.add('hidden');
            
            // Загружаем слова для отображения
            fetch('{{ route("reading-quiz-words.index") }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                currentEditReadingListAllWords = data.data || [];
                renderEditListWordsDisplay(l.word_ids_in_list || []);
            })
            .catch(e => console.error('Error loading words:', e));
            
            showReadingModal('modal-edit-reading-list');
        }
    });
};

// Отобразить слова в списке при редактировании
function renderEditListWordsDisplay(wordIdsInList) {
    const container = document.getElementById('edit-reading-list-words-display');
    if (!wordIdsInList || wordIdsInList.length === 0) {
        container.innerHTML = '<p class="text-gray-400 text-sm">В списке нет слов. Нажмите «Добавить» чтобы добавить.</p>';
        return;
    }
    
    const wordsInList = currentEditReadingListAllWords.filter(w => wordIdsInList.includes(w.id));
    container.innerHTML = wordsInList.map(w => `
        <div class="flex justify-between items-center p-2 bg-gray-700/50 rounded mb-1">
            <div class="flex-1">
                <div class="text-sm font-semibold text-white">${w.japanese_word}</div>
                <div class="text-xs text-gray-400">${w.reading}</div>
            </div>
            <button type="button" class="edit-list-remove-word-btn text-red-400 hover:text-red-300 px-2" data-word-id="${w.id}">🗑️</button>
        </div>
    `).join('');
    
    // Добавляем обработчики удаления слов
    document.querySelectorAll('.edit-list-remove-word-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const wordId = parseInt(this.getAttribute('data-word-id'));
            const currentWords = document.getElementById('edit-reading-list-id').value;
            fetch('{{ url("/reading-quiz-lists") }}/' + currentEditReadingListId + '/toggle-word', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ word_id: wordId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Перезагружаем список
                    fetch('{{ route("reading-quiz-lists.index") }}', { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(d => {
                        const l = (d.lists || []).find(x => x.id === currentEditReadingListId);
                        if (l) renderEditListWordsDisplay(l.word_ids_in_list || []);
                    });
                }
            });
        });
    });
}

// Обработчик кнопки "Добавить слова" при редактировании
document.getElementById('edit-reading-list-add-words-btn')?.addEventListener('click', () => {
    const wordIdsInList = [];
    fetch('{{ route("reading-quiz-lists.index") }}', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(d => {
        const l = (d.lists || []).find(x => x.id === currentEditReadingListId);
        const inList = new Set(l ? (l.word_ids_in_list || []) : []);
        
        const container = document.getElementById('edit-reading-list-words-to-add-list');
        if (!Array.isArray(currentEditReadingListAllWords) || currentEditReadingListAllWords.length === 0) {
            container.innerHTML = '<p class="text-center text-gray-400 py-8">Нет слов. Сначала добавьте слова!</p>';
        } else {
            container.innerHTML = currentEditReadingListAllWords.map(w => `
                <label class="flex gap-3 p-3 bg-gray-700/50 rounded hover:bg-gray-700 cursor-pointer">
                    <input type="checkbox" class="edit-list-word-checkbox" value="${w.id}" ${inList.has(w.id) ? 'checked' : ''}>
                    <div class="flex-1">
                        <div class="text-white font-semibold">${w.japanese_word}</div>
                        <div class="text-gray-400 text-sm">${w.reading}</div>
                        <div class="text-gray-500 text-xs">${w.translation_ru}</div>
                    </div>
                </label>
            `).join('');
        }
        showReadingModal('modal-edit-reading-list-add-words');
    })
    .catch(e => {
        console.error('Error loading words:', e);
        document.getElementById('edit-reading-list-words-to-add-list').innerHTML = '<p class="text-red-400">Ошибка загрузки</p>';
        showReadingModal('modal-edit-reading-list-add-words');
    });
});

// Поиск в списке добавления слов
document.getElementById('edit-reading-list-words-search')?.addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    
    fetch('{{ route("reading-quiz-lists.index") }}', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(d => {
        const l = (d.lists || []).find(x => x.id === currentEditReadingListId);
        const inList = new Set(l ? (l.word_ids_in_list || []) : []);
        
        const filtered = currentEditReadingListAllWords.filter(w =>
            w.japanese_word.toLowerCase().includes(term) ||
            w.reading.toLowerCase().includes(term) ||
            (w.translation_ru && w.translation_ru.toLowerCase().includes(term))
        );
        
        const container = document.getElementById('edit-reading-list-words-to-add-list');
        if (filtered.length === 0) {
            container.innerHTML = '<p class="text-center text-gray-400 py-4">Слова не найдены</p>';
        } else {
            container.innerHTML = filtered.map(w => `
                <label class="flex gap-3 p-3 bg-gray-700/50 rounded hover:bg-gray-700 cursor-pointer">
                    <input type="checkbox" class="edit-list-word-checkbox" value="${w.id}" ${inList.has(w.id) ? 'checked' : ''}>
                    <div class="flex-1">
                        <div class="text-white font-semibold">${w.japanese_word}</div>
                        <div class="text-gray-400 text-sm">${w.reading}</div>
                        <div class="text-gray-500 text-xs">${w.translation_ru}</div>
                    </div>
                </label>
            `).join('');
        }
    })
    .catch(e => console.error('Search error:', e));
});

// Подтверждение добавления слов
document.getElementById('edit-reading-list-add-words-confirm')?.addEventListener('click', () => {
    const selected = Array.from(document.querySelectorAll('.edit-list-word-checkbox')).filter(x => x.checked).map(x => parseInt(x.value));
    
    fetch('{{ route("reading-quiz-lists.index") }}', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(d => {
        const l = (d.lists || []).find(x => x.id === currentEditReadingListId);
        const current = new Set(l ? (l.word_ids_in_list || []) : []);
        const next = new Set(selected);
        const add = Array.from(next).filter(id => !current.has(id));
        const remove = Array.from(current).filter(id => !next.has(id));
        
        const reqs = [
            ...add.map(wid => fetch('{{ url("/reading-quiz-lists") }}/' + currentEditReadingListId + '/toggle-word', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ word_id: wid })
            }).then(r => r.json())),
            ...remove.map(wid => fetch('{{ url("/reading-quiz-lists") }}/' + currentEditReadingListId + '/toggle-word', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ word_id: wid })
            }).then(r => r.json()))
        ];
        
        Promise.all(reqs)
        .then(() => {
            hideReadingModal('modal-edit-reading-list-add-words');
            // Перезагружаем список слов в modal-edit-reading-list
            fetch('{{ route("reading-quiz-lists.index") }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                const l = (d.lists || []).find(x => x.id === currentEditReadingListId);
                if (l) renderEditListWordsDisplay(l.word_ids_in_list || []);
            });
        })
        .catch(e => {
            console.error(e);
            alert('Ошибка при добавлении слов');
        });
    });
});

document.getElementById('edit-reading-list-submit')?.addEventListener('click', () => {
    const name = document.getElementById('edit-reading-list-name').value.trim();
    if (!name) {
        document.getElementById('edit-reading-list-message').textContent = 'Введите название';
        document.getElementById('edit-reading-list-message').classList.remove('hidden');
        return;
    }
    
    fetch('{{ url("/reading-quiz-lists") }}/' + currentEditReadingListId, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: name,
            description: document.getElementById('edit-reading-list-description').value
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            hideReadingModal('modal-edit-reading-list');
            location.reload();
        } else {
            document.getElementById('edit-reading-list-message').textContent = data.error || 'Ошибка';
            document.getElementById('edit-reading-list-message').classList.remove('hidden');
        }
    });
});

// Функция удаления списка
window.deleteReadingList = function(listId) {
    if (!confirm('Удалить этот список?')) return;
    fetch('{{ url("/reading-quiz-lists") }}/' + listId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Ошибка удаления');
        }
    });
};
</script>
@endpush

@endsection

