@extends('layouts.app')

@section('title', 'Квиз на чтение')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div>
            <h1 class="text-4xl font-bold text-blue-400 mb-2">📚 Квиз на чтение</h1>
            <p class="text-gray-400">Редактируйте список слов и практикуйте чтение</p>
        </div>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-800/50 to-cyan-800/50 rounded-xl p-6 border border-blue-700/50">
            <div class="text-3xl font-bold text-blue-300 mb-1">{{ $totalWords ?? 0 }}</div>
            <div class="text-gray-300">Всего слов</div>
        </div>
        <div class="bg-gradient-to-br from-blue-800/50 to-cyan-800/50 rounded-xl p-6 border border-blue-700/50">
            <div class="text-3xl font-bold text-blue-300 mb-1">{{ $studiedWords ?? 0 }}</div>
            <div class="text-gray-300">Изучается</div>
        </div>
        <div class="bg-gradient-to-br from-green-800/50 to-emerald-800/50 rounded-xl p-6 border border-green-700/50">
            <div class="text-3xl font-bold text-green-300 mb-1">{{ $completedWords ?? 0 }}</div>
            <div class="text-gray-300">Изучено (10/10)</div>
        </div>
        <div class="bg-gradient-to-br from-yellow-800/50 to-amber-800/50 rounded-xl p-6 border border-yellow-700/50">
            <div class="text-3xl font-bold text-yellow-300 mb-1">{{ $dueWords ?? 0 }}</div>
            <div class="text-gray-300">Пора повторить</div>
        </div>
    </div>

    <!-- Начать квиз -->
    <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <h3 class="text-xl font-bold text-blue-400 mb-4">⚡ Начать квиз</h3>
        <form action="{{ route('reading-quiz.quiz') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-gray-300">Колич-во:</label>
                <input type="number" name="count" value="10" min="1" max="50" 
                       class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white w-24 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-blue-500/50 transform hover:scale-105">
                Начать квиз 🎯
            </button>
        </form>
    </div>

    <!-- Управление списками слов -->
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
                                            <h4 class="font-semibold text-white text-lg">${list.name}</h4>
                                            <p class="text-gray-400 text-sm">${list.description || 'Без описания'}</p>
                                            <p class="text-gray-500 text-xs mt-1">${list.word_count} слов</p>
                                            <div style="width:220px; height:8px; background-color: rgba(75,85,99,0.35); border-radius:9999px; overflow:hidden; margin-top:8px;">
                                                <div style="height:100%; width: ${list.progress_percent || 0}%; background: linear-gradient(90deg, #0ea5e9 0%, #06b6d4 100%); border-radius:9999px; transition: width 0.3s ease; box-shadow: 0 0 6px rgba(14,165,233,0.35);"></div>
                                            </div>
                                            <p class="text-gray-400 text-xs mt-1">Прогресс: ${list.progress_percent || 0}%</p>
                                        </div>
                                        <div class="flex gap-2 flex-shrink-0">
                                            <button onclick="openEditReadingListModal(${list.id})" class="bg-blue-600 hover:bg-blue-500 px-3 py-2 rounded text-sm text-white">✏️</button>
                                            <button onclick="deleteReadingList(${list.id})" class="bg-red-600 hover:bg-red-500 px-3 py-2 rounded text-sm text-white">🗑️</button>
                                            <a href="{{ route('reading-quiz.quiz') }}?list_id=${list.id}" class="bg-blue-600 hover:bg-blue-500 px-3 py-2 rounded text-sm text-white">▶️ Квиз</a>
                                        </div>
                                    </div>
                            `;
                        });
                        html += '</div>';
                        container.innerHTML = html;
                    })
                    .catch(() => {
                        const container = document.getElementById('reading-lists-container');
                        if (container) container.innerHTML = '<p class="text-red-400">Ошибка загрузки списков</p>';
                    });
                } catch (e) { console.error(e); }
            })();
        </script>
    </div>

    <!-- Список слов -->
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Модальное окно для добавления слова
    const modalAddWord = document.getElementById('modal-add-reading-word');
    const modalEditWord = document.getElementById('modal-edit-reading-word');

    document.getElementById('btn-add-reading-word')?.addEventListener('click', function() {
        document.getElementById('add-reading-japanese-word').value = '';
        document.getElementById('add-reading-reading').value = '';
        document.getElementById('add-reading-translation-ru').value = '';
        document.getElementById('add-reading-translation-en').value = '';
        document.getElementById('add-reading-word-message').classList.add('hidden');
        modalAddWord.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    document.getElementById('modal-add-reading-word-close')?.addEventListener('click', function() {
        modalAddWord.style.display = 'none';
        document.body.style.overflow = '';
    });

    document.getElementById('add-reading-word-cancel')?.addEventListener('click', function() {
        modalAddWord.style.display = 'none';
        document.body.style.overflow = '';
    });

    document.getElementById('add-reading-word-submit')?.addEventListener('click', function() {
        const japaneseWord = document.getElementById('add-reading-japanese-word').value.trim();
        const reading = document.getElementById('add-reading-reading').value.trim();
        const translationRu = document.getElementById('add-reading-translation-ru').value.trim();

        if (!japaneseWord || !reading || !translationRu) {
            document.getElementById('add-reading-word-message').textContent = 'Заполните обязательные поля';
            document.getElementById('add-reading-word-message').classList.remove('hidden');
            return;
        }

        this.disabled = true;
        const msgEl = document.getElementById('add-reading-word-message');

        fetch('{{ route("reading-quiz-words.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                japanese_word: japaneseWord,
                reading: reading,
                translation_ru: translationRu,
                translation_en: document.getElementById('add-reading-translation-en').value || ''
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                modalAddWord.style.display = 'none';
                document.body.style.overflow = '';
                location.reload();
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

    // Редактирование слова
    document.getElementById('modal-edit-reading-word-close')?.addEventListener('click', function() {
        modalEditWord.style.display = 'none';
        document.body.style.overflow = '';
    });

    document.getElementById('edit-reading-word-cancel')?.addEventListener('click', function() {
        modalEditWord.style.display = 'none';
        document.body.style.overflow = '';
    });

    document.querySelectorAll('.reading-word-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const wordId = this.getAttribute('data-word-id');
            const card = this.closest('.reading-word-card');
            
            // Получаем данные из карточки элемента
            const japaneseWord = card.getAttribute('data-word') || '';
            const reading = card.getAttribute('data-reading') || '';
            const translation = card.getAttribute('data-translation') || '';
            
            document.getElementById('edit-reading-word-id').value = wordId;
            document.getElementById('edit-reading-japanese-word').value = japaneseWord;
            document.getElementById('edit-reading-reading').value = reading;
            document.getElementById('edit-reading-translation-ru').value = translation;
            document.getElementById('edit-reading-translation-en').value = '';
            document.getElementById('edit-reading-word-message').classList.add('hidden');
            modalEditWord.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    document.getElementById('edit-reading-word-submit')?.addEventListener('click', function() {
        const wordId = document.getElementById('edit-reading-word-id').value;
        const japaneseWord = document.getElementById('edit-reading-japanese-word').value.trim();
        const reading = document.getElementById('edit-reading-reading').value.trim();
        const translationRu = document.getElementById('edit-reading-translation-ru').value.trim();

        if (!japaneseWord || !reading || !translationRu) {
            document.getElementById('edit-reading-word-message').textContent = 'Заполните обязательные поля';
            document.getElementById('edit-reading-word-message').classList.remove('hidden');
            return;
        }

        this.disabled = true;
        const msgEl = document.getElementById('edit-reading-word-message');

        fetch(`/reading-quiz-words/${wordId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                japanese_word: japaneseWord,
                reading: reading,
                translation_ru: translationRu,
                translation_en: document.getElementById('edit-reading-translation-en').value || ''
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                modalEditWord.style.display = 'none';
                document.body.style.overflow = '';
                location.reload();
            } else {
                msgEl.textContent = data.error || 'Ошибка сохранения';
                msgEl.classList.remove('hidden');
            }
        })
        .catch(() => {
            msgEl.textContent = 'Ошибка сети';
            msgEl.classList.remove('hidden');
        })
        .finally(() => { this.disabled = false; });
    });

    // Удаление слова
    document.querySelectorAll('.reading-word-delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Вы уверены? Слово будет удалено.')) return;
            
            const wordId = this.getAttribute('data-word-id');
            this.disabled = true;

            fetch(`/reading-quiz-words/${wordId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Ошибка удаления');
                    this.disabled = false;
                }
            })
            .catch(() => {
                alert('Ошибка сети');
                this.disabled = false;
            });
        });
    });

    // Управление списками
    document.getElementById('btn-create-reading-list')?.addEventListener('click', function() {
        openCreateReadingListModal();
    });
});

// Модальное окно для создания нового списка чтения
function openCreateReadingListModal() {
    const modalHtml = `
        <div id="modal-create-reading-list" style="position: fixed; inset: 0; display: flex; justify-content: center; align-items: center; background: rgba(0, 0, 0, 0.5); z-index: 50; overflow-y: auto;" class="modal-backdrop">
            <div style="background: #2d3748; border-radius: 12px; padding: 2rem; width: 90%; max-width: 700px; border: 1px solid #4b5563; margin: 2rem auto;" class="modal-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 class="text-2xl font-bold text-blue-400">Создать новый список</h2>
                    <button onclick="closeCreateReadingListModal()" style="background: none; border: none; color: #9ca3af; font-size: 1.5rem; cursor: pointer;">×</button>
                </div>
                
                <div style="max-height: 600px; overflow-y: auto;">
                    <!-- Название и описание -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="text-white text-sm block mb-2">Название списка *</label>
                        <input type="text" id="new-reading-list-name" placeholder="Например: JLPT N3" 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3">
                        
                        <label class="text-white text-sm block mb-2">Описание</label>
                        <textarea id="new-reading-list-description" placeholder="Описание (опционально)" 
                                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 h-16"></textarea>
                    </div>
                    
                    <!-- Поиск слов для добавления -->
                    <div style="margin-bottom: 1.5rem; background: #1f2937; border-left: 4px solid #0ea5e9; padding: 1rem; border-radius: 6px;">
                        <label class="text-white text-sm block mb-2">🔍 Найти и добавить слова в список</label>
                        <p class="text-gray-400 text-xs mb-2">Поиск по слову, чтению или переводу</p>
                        <input id="new-reading-word-search" type="text" placeholder="Введите для поиска..." 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"
                               oninput="searchReadingWords(undefined, this.value)">
                        <div id="new-reading-word-suggestions" class="bg-gray-800/30 rounded-lg p-2 border border-gray-600" style="max-height: 200px; overflow-y: auto;">
                            <p class="text-gray-400 text-sm">Начните вводить для поиска...</p>
                        </div>
                    </div>
                    
                    <!-- Выбранные слова -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="text-white text-sm block mb-2">📝 Слова в этом списке (0)</label>
                        <div id="new-reading-selected-words" class="bg-gray-700/50 rounded-lg p-4 border border-gray-600" style="min-height: 80px;">
                            <p class="text-gray-400 text-sm">Слова не выбраны</p>
                        </div>
                    </div>
                </div>
                
                <p id="new-reading-list-error" class="text-red-400 text-sm mb-3 hidden"></p>
                
                <div class="flex gap-2 justify-end">
                    <button onclick="closeCreateReadingListModal()" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded text-white font-medium transition">Отмена</button>
                    <button onclick="saveNewReadingList()" class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded text-white font-medium transition">Создать список</button>
                </div>
            </div>
        </div>
    `;
    
    const existing = document.getElementById('modal-create-reading-list');
    if (existing) existing.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeCreateReadingListModal() {
    document.getElementById('modal-create-reading-list')?.remove();
}

// Поиск слов для чтения с debounce
let _readingWordSearchDebounce = null;
function searchReadingWords(listId, q) {
    clearTimeout(_readingWordSearchDebounce);
    _readingWordSearchDebounce = setTimeout(() => {
        if (!q || q.trim() === '') {
            const suggestionsEl = listId 
                ? document.getElementById(`reading-word-search-suggestions-${listId}`)
                : document.getElementById('new-reading-word-suggestions');
            if (suggestionsEl) {
                suggestionsEl.innerHTML = '<p class="text-gray-400 text-sm">Начните вводить для поиска...</p>';
            }
            return;
        }

        const suggestionsEl = listId 
            ? document.getElementById(`reading-word-search-suggestions-${listId}`)
            : document.getElementById('new-reading-word-suggestions');
        
        if (!suggestionsEl) return;

        suggestionsEl.innerHTML = '<p class="text-gray-400 text-sm">Идёт поиск...</p>';

        fetch(`/reading-quiz-words`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.data || data.data.length === 0) {
                    suggestionsEl.innerHTML = '<p class="text-gray-400 text-sm">Слова не найдены</p>';
                    return;
                }

                const query = q.toLowerCase();
                const filtered = data.data.filter(w => {
                    const wordMatch = (w.japanese_word || '').toLowerCase().includes(query);
                    const readingMatch = (w.reading || '').toLowerCase().includes(query);
                    const translationMatch = (w.translation_ru || '').toLowerCase().includes(query);
                    return wordMatch || readingMatch || translationMatch;
                }).slice(0, 15);

                if (filtered.length === 0) {
                    suggestionsEl.innerHTML = '<p class="text-gray-400 text-sm">Ничего не найдено</p>';
                    return;
                }

                let html = '';
                filtered.forEach(w => {
                    html += `<div data-word-id="${w.id}" data-word-text="${escapeHtml(w.japanese_word || '')}" style="padding:0.5rem; border-bottom: 1px solid rgba(255,255,255,0.03); cursor: pointer;" onclick="selectReadingWordForList(${listId || 'undefined'}, ${w.id}, this)">` +
                            `<strong class="text-white">${escapeHtml(w.japanese_word)}</strong> <span class="text-gray-400 text-sm">${escapeHtml(w.reading || '')}</span> <div class="text-gray-300 text-xs">${escapeHtml(w.translation_ru || '')}</div>` +
                            `</div>`;
                });
                suggestionsEl.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                suggestionsEl.innerHTML = '<p class="text-red-400 text-sm">Ошибка поиска</p>';
            });
    }, 250);
}

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function selectReadingWordForList(listId, wordId, suggestionEl) {
    const container = listId
        ? document.getElementById(`reading-selected-words-${listId}`)
        : document.getElementById('new-reading-selected-words');
    
    if (!container) return;

    // Проверяем, есть ли уже такое слово
    const exists = Array.from(container.querySelectorAll('[data-word-id]'))
        .some(el => parseInt(el.getAttribute('data-word-id')) === wordId);

    if (exists) return;

    if (container.textContent.includes('не выбраны') || container.textContent.includes('В списке нет')) {
        container.innerHTML = '';
    }

    const wordText = suggestionEl?.getAttribute('data-word-text') || '';
    let currentRow = container.querySelector('div[style*="display: flex"][style*="flex-wrap"]');

    if (!currentRow || currentRow.querySelectorAll('div').length >= 8) {
        currentRow = document.createElement('div');
        currentRow.style.cssText = 'display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;';
        container.appendChild(currentRow);
    }

    const wordEl = document.createElement('div');
    wordEl.setAttribute('data-word-id', wordId);
    wordEl.style.cssText = 'background: #4b5563; padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #6b7280; display: inline-flex; align-items: center; gap: 0.5rem;';
    const label = wordText ? `${wordText} (${wordId})` : `ID: ${wordId}`;
    wordEl.innerHTML = `
        <span style="font-size: 0.875rem;">${label}</span>
        <button type="button" onclick="this.parentElement.remove(); updateReadingListUI(${listId || 'undefined'});" 
                style="background: #dc2626; border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.875rem;">
            ✕
        </button>
    `;

    currentRow.appendChild(wordEl);
    updateReadingListUI(listId);
}

function updateReadingListUI(listId) {
    const container = listId
        ? document.getElementById(`reading-selected-words-${listId}`)
        : document.getElementById('new-reading-selected-words');
    
    if (!container) return;

    const wordCount = container.querySelectorAll('[data-word-id]').length;
    const label = listId
        ? document.querySelector(`#modal-edit-reading-list-${listId} label`)
        : Array.from(document.querySelectorAll('label')).find(l => l.textContent.includes('Слова в этом списке'));
    
    if (label) {
        label.textContent = `${listId ? '✏️' : '📝'} Слова в ${listId ? 'списке' : 'этом списке'} (${wordCount})`;
    }
}

function saveNewReadingList() {
    const name = document.getElementById('new-reading-list-name').value.trim();
    const description = document.getElementById('new-reading-list-description').value.trim();

    if (!name) {
        document.getElementById('new-reading-list-error').textContent = 'Введите название списка';
        document.getElementById('new-reading-list-error').classList.remove('hidden');
        return;
    }

    const container = document.getElementById('new-reading-selected-words');
    const wordIds = Array.from(container.querySelectorAll('[data-word-id]'))
        .map(el => parseInt(el.getAttribute('data-word-id')))
        .filter(id => !isNaN(id));

    // Создаем список
    fetch('{{ route("reading-quiz-lists.store") }}', {
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
            const listId = data.list.id;
            
            // Добавляем слова в список
            if (wordIds.length === 0) {
                closeCreateReadingListModal();
                console.log('Список создан без слов');
                return;
            }

            const promises = wordIds.map(wordId => {
                console.log('Добавляем слово', wordId, 'в список', listId);
                return fetch(`/reading-quiz-lists/${listId}/toggle-word`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ word_id: wordId })
                })
                .then(r => {
                    console.log('Ответ для wordId', wordId, '- статус:', r.status);
                    return r.json().then(data => {
                        console.log('JSON ответ для wordId', wordId, ':', data);
                        return data;
                    });
                })
                .catch(err => {
                    console.error('Ошибка при добавлении wordId', wordId, ':', err);
                    throw err;
                });
            });

            Promise.all(promises)
            .then(results => {
                console.log('Все слова успешно добавлены:', results);
                closeCreateReadingListModal();
                alert('Список создан успешно! Проверьте консоль (F12) для деталей');
            })
            .catch(err => {
                console.error('Ошибка при добавлении слов:', err);
                document.getElementById('new-reading-list-error').textContent = 'Ошибка при добавлении слов в список: ' + (err.message || 'неизвестная ошибка');
                document.getElementById('new-reading-list-error').classList.remove('hidden');
            });
        } else {
            document.getElementById('new-reading-list-error').textContent = data.error || 'Ошибка создания списка';
            document.getElementById('new-reading-list-error').classList.remove('hidden');
        }
    })
    .catch(() => {
        document.getElementById('new-reading-list-error').textContent = 'Ошибка сети';
        document.getElementById('new-reading-list-error').classList.remove('hidden');
    });
}

function deleteReadingList(listId) {
    if (!confirm('Вы уверены? Список будет удален.')) return;

    fetch(`/reading-quiz-lists/${listId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
    })
    .catch(() => alert('Ошибка сети'));
}

function openEditReadingListModal(listId) {
    // Загружаем данные списка
    fetch('{{ route("reading-quiz-lists.index") }}', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        const currentList = data.lists?.find(l => l.id === listId);
        if (!currentList) {
            alert('Список не найден');
            return;
        }
        
        displayEditReadingListModal(listId, currentList);
    })
    .catch(err => {
        alert('Ошибка загрузки данных списка');
        console.error(err);
    });
}

function displayEditReadingListModal(listId, currentList) {
    const currentWordIds = currentList.word_ids_in_list || [];
    
    const modalHtml = `
        <div id="modal-edit-reading-list-${listId}" style="position: fixed; inset: 0; display: flex; justify-content: center; align-items: center; background: rgba(0, 0, 0, 0.5); z-index: 50; overflow-y: auto;" class="modal-backdrop">
            <div style="background: #2d3748; border-radius: 12px; padding: 2rem; width: 90%; max-width: 700px; border: 1px solid #4b5563; margin: 2rem auto;" class="modal-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 class="text-2xl font-bold text-blue-400">Редактировать список</h2>
                    <button onclick="closeEditReadingListModal(${listId})" style="background: none; border: none; color: #9ca3af; font-size: 1.5rem; cursor: pointer;">×</button>
                </div>
                
                <div style="max-height: 600px; overflow-y: auto;">
                    <!-- Название и описание -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="text-white text-sm block mb-2">Название списка</label>
                        <input type="text" id="edit-reading-list-name-${listId}" placeholder="Название" 
                               value="${escapeHtml(currentList.name)}"
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3">
                        
                        <label class="text-white text-sm block mb-2">Описание</label>
                        <textarea id="edit-reading-list-description-${listId}" placeholder="Описание" 
                                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 h-16">${escapeHtml(currentList.description || '')}</textarea>
                    </div>
                    
                    <!-- Поиск слов для добавления -->
                    <div style="margin-bottom: 1.5rem; background: #1f2937; border-left: 4px solid #0ea5e9; padding: 1rem; border-radius: 6px;">
                        <label class="text-white text-sm block mb-2">🔍 Найти и добавить словa</label>
                        <p class="text-gray-400 text-xs mb-2">Поиск по слову, чтению или переводу</p>
                        <input id="reading-word-search-input-${listId}" type="text" placeholder="Введите для поиска..." 
                               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2"
                               oninput="searchReadingWords(${listId}, this.value)">
                        <div id="reading-word-search-suggestions-${listId}" class="bg-gray-800/30 rounded-lg p-2 border border-gray-600" style="max-height: 200px; overflow-y: auto;">
                            <p class="text-gray-400 text-sm">Начните вводить для поиска...</p>
                        </div>
                    </div>
                    
                    <!-- Слова в списке -->
                    <div style="margin-bottom: 1.5rem;">
                        <label class="text-white text-sm block mb-2">✏️ Слова в списке (${currentWordIds.length})</label>
                        <div id="reading-selected-words-${listId}" class="bg-gray-700/50 rounded-lg p-4 border border-gray-600" style="min-height: 80px;">
                            ${currentWordIds.length > 0 
                                ? `<div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    ${currentWordIds.map(wId => `
                                        <div data-word-id="${wId}" style="background: #4b5563; padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #6b7280; display: inline-flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-size: 0.875rem;">Слово #${wId}</span>
                                            <button type="button" onclick="this.parentElement.remove(); updateReadingListUI(${listId});" 
                                                    style="background: #dc2626; border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.875rem;">
                                                ✕
                                            </button>
                                        </div>
                                    `).join('')}
                                  </div>`
                                : '<p class="text-gray-400 text-sm">В списке нет слов</p>'
                            }
                        </div>
                    </div>
                </div>
                
                <p id="reading-list-edit-error-${listId}" class="text-red-400 text-sm mb-3 hidden"></p>
                
                <div class="flex gap-2 justify-end">
                    <button onclick="closeEditReadingListModal(${listId})" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded text-white font-medium transition">Отмена</button>
                    <button onclick="saveEditedReadingList(${listId})" class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded text-white font-medium transition">Сохранить</button>
                </div>
            </div>
        </div>
    `;
    
    const existing = document.getElementById(`modal-edit-reading-list-${listId}`);
    if (existing) existing.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeEditReadingListModal(listId) {
    document.getElementById(`modal-edit-reading-list-${listId}`)?.remove();
}

function saveEditedReadingList(listId) {
    const name = document.getElementById(`edit-reading-list-name-${listId}`).value.trim();
    const description = document.getElementById(`edit-reading-list-description-${listId}`).value.trim();

    if (!name) {
        document.getElementById(`reading-list-edit-error-${listId}`).textContent = 'Введите название списка';
        document.getElementById(`reading-list-edit-error-${listId}`).classList.remove('hidden');
        return;
    }

    const container = document.getElementById(`reading-selected-words-${listId}`);
    const newWordIds = Array.from(container.querySelectorAll('[data-word-id]'))
        .map(el => parseInt(el.getAttribute('data-word-id')))
        .filter(id => !isNaN(id));

    // Обновляем информацию о списке
    fetch(`/reading-quiz-lists/${listId}`, {
        method: 'PUT',
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
            // Получаем текущие слова в списке
            fetch(`/reading-quiz-lists/${listId}/words`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const currentWordIds = new Set(data.words || []);
                const nextWordIds = new Set(newWordIds);
                
                // Определяем что удалить и что добавить
                const toRemove = Array.from(currentWordIds).filter(w => !nextWordIds.has(w));
                const toAdd = Array.from(nextWordIds).filter(w => !currentWordIds.has(w));
                
                console.log('Список', listId, ': удалить', toRemove, ', добавить', toAdd);
                
                // Отправляем все операции
                const promises = [
                    ...toRemove.map(wordId => {
                        console.log('Удаляем слово', wordId, 'из списка', listId);
                        return fetch(`/reading-quiz-lists/${listId}/toggle-word`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ word_id: wordId })
                        })
                        .then(r => {
                            console.log('Ответ удаления wordId', wordId, '- статус:', r.status);
                            return r.json().then(data => {
                                console.log('JSON ответ удаления для wordId', wordId, ':', data);
                                return data;
                            });
                        });
                    }),
                    ...toAdd.map(wordId => {
                        console.log('Добавляем слово', wordId, 'в список', listId);
                        return fetch(`/reading-quiz-lists/${listId}/toggle-word`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ word_id: wordId })
                        })
                        .then(r => {
                            console.log('Ответ добавления wordId', wordId, '- статус:', r.status);
                            return r.json().then(data => {
                                console.log('JSON ответ добавления для wordId', wordId, ':', data);
                                return data;
                            });
                        });
                    })
                ];
                
                Promise.all(promises)
                .then(results => {
                    console.log('Все операции завершены:', results);
                    closeEditReadingListModal(listId);
                    alert('Список обновлён успешно! Проверьте консоль (F12) для деталей');
                })
                .catch(err => {
                    console.error('Ошибка при обновлении слов:', err);
                    document.getElementById(`reading-list-edit-error-${listId}`).textContent = 'Ошибка при обновлении слов: ' + (err.message || 'неизвестная ошибка');
                    document.getElementById(`reading-list-edit-error-${listId}`).classList.remove('hidden');
                });
            });
        } else {
            document.getElementById(`reading-list-edit-error-${listId}`).textContent = data.error || 'Ошибка обновления';
            document.getElementById(`reading-list-edit-error-${listId}`).classList.remove('hidden');
        }
    })
    .catch(() => {
        document.getElementById(`reading-list-edit-error-${listId}`).textContent = 'Ошибка сети';
        document.getElementById(`reading-list-edit-error-${listId}`).classList.remove('hidden');
    });
}
</script>
@endpush
@endsection
