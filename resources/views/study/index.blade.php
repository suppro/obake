@extends('layouts.app')

@section('title', 'Повторение слов - Obake')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-4xl font-bold text-purple-400">📚 Повторение слов</h1>
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
    </div>
    
    <!-- Статистика -->
    <div class="bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-2xl font-bold text-purple-400" id="words-today">{{ $wordsToReview->count() }}</div>
                <div class="text-gray-400 text-sm">Слов на сегодня</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-purple-400" id="correct-count">0</div>
                <div class="text-gray-400 text-sm">Правильно</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-purple-400" id="incorrect-count">0</div>
                <div class="text-gray-400 text-sm">Неправильно</div>
            </div>
        </div>
    </div>
    
    <!-- Карточка слова -->
    <div id="word-card" class="bg-gray-800 rounded-lg shadow-lg p-8 hidden">
        <div class="text-center mb-6">
            <div class="text-sm text-gray-400 mb-2" id="direction-label"></div>
            <div class="text-2xl font-bold text-purple-400 mb-4" id="word-question"></div>
            <div id="word-hint" class="text-lg text-gray-500 mb-4 hidden"></div>
        </div>
        
        <div class="mb-6">
            <input 
                type="text" 
                id="user-answer" 
                class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white text-lg focus:outline-none focus:border-purple-500 japanese-font"
                placeholder="Введите ответ..."
                autocomplete="off">
        </div>
        
        <div class="flex gap-4">
            <button 
                id="check-btn" 
                class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                Проверить
            </button>
            <button 
                id="skip-btn" 
                class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                Пропустить
            </button>
        </div>
    </div>
    
    <!-- Результат -->
    <div id="result-card" class="bg-gray-800 rounded-lg shadow-lg p-8 hidden">
        <div class="text-center mb-6">
            <div id="result-icon" class="text-6xl mb-4"></div>
            <div id="result-message" class="text-2xl font-bold mb-4"></div>
            <div class="text-gray-400 mb-2">Ваш ответ:</div>
            <div id="user-answer-display" class="text-xl mb-4 japanese-font"></div>
            <div class="text-gray-400 mb-2">Правильный ответ:</div>
            <div id="correct-answer-display" class="text-xl font-bold text-purple-400 japanese-font"></div>
        </div>
        
        <button 
            id="next-btn" 
            class="w-full bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition font-semibold">
            Следующее слово
        </button>
    </div>
    
    <!-- Начальный экран -->
    <div id="start-screen" class="bg-gray-800 rounded-lg shadow-lg p-8 text-center">
        @if($wordsToReview->isEmpty())
            <div class="mb-6">
                <div class="text-6xl mb-4">🎉</div>
                <h2 class="text-2xl font-bold text-purple-400 mb-2">Отлично!</h2>
                <p class="text-gray-400 mb-4">На сегодня слов для повторения нет.</p>
            </div>
            
            @if($availableWords->isNotEmpty())
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-300 mb-4">Начать изучение новых слов:</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($availableWords->take(6) as $word)
                            <button 
                                class="start-word-btn bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition text-sm"
                                data-word-id="{{ $word->id }}">
                                <div class="japanese-font text-lg">{{ $word->japanese_word }}</div>
                                <div class="text-xs text-gray-400">{{ $word->translation_ru }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <button 
                id="extra-review-btn" 
                class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                Повторить изученные слова
            </button>
        @else
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-purple-400 mb-2">Готовы начать?</h2>
                <p class="text-gray-400 mb-4">У вас {{ $wordsToReview->count() }} слов для повторения сегодня.</p>
            </div>
            
            <button 
                id="start-btn" 
                class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition font-semibold text-lg">
                Начать повторение
            </button>
        @endif
    </div>
    
    <!-- Экран завершения -->
    <div id="finish-screen" class="bg-gray-800 rounded-lg shadow-lg p-8 text-center hidden">
        <div class="text-6xl mb-4">🎊</div>
        <h2 class="text-2xl font-bold text-purple-400 mb-2">Повторение завершено!</h2>
        <p class="text-gray-400 mb-6">Отличная работа! Вы повторили все слова на сегодня.</p>
        
        <div class="flex gap-4 justify-center">
            <button 
                id="review-again-btn" 
                class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                Повторить еще
            </button>
            <a 
                href="{{ route('dashboard') }}" 
                class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-semibold">
                На главную
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentWord = null;
let correctCount = 0;
let incorrectCount = 0;
let currentProgressId = null;

document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('start-btn');
    const startScreen = document.getElementById('start-screen');
    const wordCard = document.getElementById('word-card');
    const resultCard = document.getElementById('result-card');
    const finishScreen = document.getElementById('finish-screen');
    const checkBtn = document.getElementById('check-btn');
    const skipBtn = document.getElementById('skip-btn');
    const nextBtn = document.getElementById('next-btn');
    const userAnswerInput = document.getElementById('user-answer');
    const extraReviewBtn = document.getElementById('extra-review-btn');
    const reviewAgainBtn = document.getElementById('review-again-btn');
    const startWordBtns = document.querySelectorAll('.start-word-btn');
    
    // Загружаем списки слов
    loadWordLists();
    
    // Обработчик кнопки создания списка
    document.getElementById('btn-create-word-list')?.addEventListener('click', function() {
        openCreateWordListModal();
    });
    
    // Начать повторение
    if (startBtn) {
        startBtn.addEventListener('click', function() {
            startScreen.classList.add('hidden');
            loadNextWord();
        });
    }
    
    // Начать изучение нового слова
    startWordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const wordId = this.dataset.wordId;
            startStudyingWord(wordId);
        });
    });
    
    // Дополнительное повторение
    if (extraReviewBtn) {
        extraReviewBtn.addEventListener('click', function() {
            loadExtraWords();
        });
    }
    
    // Повторить еще раз
    if (reviewAgainBtn) {
        reviewAgainBtn.addEventListener('click', function() {
            finishScreen.classList.add('hidden');
            loadNextWord();
        });
    }
    
    // Проверить ответ
    checkBtn.addEventListener('click', checkAnswer);
    
    // Пропустить
    skipBtn.addEventListener('click', function() {
        loadNextWord();
    });
    
    // Следующее слово
    nextBtn.addEventListener('click', function() {
        resultCard.classList.add('hidden');
        wordCard.classList.remove('hidden');
        userAnswerInput.value = '';
        userAnswerInput.focus();
        loadNextWord();
    });
    
    // Enter для проверки
    userAnswerInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && resultCard.classList.contains('hidden')) {
            checkAnswer();
        }
    });
    
    function loadNextWord() {
        fetch('{{ route("study.get-next-word") }}')
            .then(response => response.json())
            .then(data => {
                if (!data.has_words) {
                    wordCard.classList.add('hidden');
                    finishScreen.classList.remove('hidden');
                    return;
                }
                
                currentWord = data.word;
                currentWord.direction = data.direction; // Сохраняем направление
                currentProgressId = data.progress_id;
                
                // Обновляем интерфейс
                if (data.direction === 'ru_to_jp') {
                    document.getElementById('direction-label').textContent = 'Напишите на японском (хираганой)';
                    document.getElementById('word-question').textContent = data.word.translation_ru;
                    // Не показываем подсказку с японским словом
                    document.getElementById('word-hint').classList.add('hidden');
                } else {
                    document.getElementById('direction-label').textContent = 'Напишите перевод на русском';
                    document.getElementById('word-question').textContent = data.word.japanese;
                    document.getElementById('word-hint').classList.add('hidden');
                }
                
                wordCard.classList.remove('hidden');
                userAnswerInput.focus();
                
                // Озвучивание отключено в режиме повторения
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при загрузке слова');
            });
    }
    
    function checkAnswer() {
        const userAnswer = userAnswerInput.value.trim();
        if (!userAnswer) {
            alert('Введите ответ');
            return;
        }
        
        checkBtn.disabled = true;
        checkBtn.textContent = 'Проверяю...';
        
        fetch('{{ route("study.check-answer") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                word_id: currentWord.id,
                direction: currentWord.direction,
                user_answer: userAnswer,
                progress_id: currentProgressId
            })
        })
        .then(response => response.json())
        .then(data => {
            wordCard.classList.add('hidden');
            resultCard.classList.remove('hidden');
            
            if (data.is_correct) {
                document.getElementById('result-icon').textContent = '✓';
                document.getElementById('result-icon').classList.add('text-green-400');
                document.getElementById('result-icon').classList.remove('text-red-400');
                document.getElementById('result-message').textContent = 'Правильно!';
                document.getElementById('result-message').classList.add('text-green-400');
                document.getElementById('result-message').classList.remove('text-red-400');
                correctCount++;
            } else {
                document.getElementById('result-icon').textContent = '✗';
                document.getElementById('result-icon').classList.add('text-red-400');
                document.getElementById('result-icon').classList.remove('text-green-400');
                document.getElementById('result-message').textContent = 'Неправильно';
                document.getElementById('result-message').classList.add('text-red-400');
                document.getElementById('result-message').classList.remove('text-green-400');
                incorrectCount++;
            }
            
            document.getElementById('user-answer-display').textContent = data.user_answer;
            document.getElementById('correct-answer-display').textContent = data.correct_answer;
            document.getElementById('correct-count').textContent = correctCount;
            document.getElementById('incorrect-count').textContent = incorrectCount;
            
            checkBtn.disabled = false;
            checkBtn.textContent = 'Проверить';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при проверке ответа');
            checkBtn.disabled = false;
            checkBtn.textContent = 'Проверить';
        });
    }
    
    function startStudyingWord(wordId) {
        fetch('{{ route("study.start") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                word_id: wordId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при начале изучения слова');
        });
    }
    
    function loadExtraWords() {
        fetch('{{ route("study.get-extra-words") }}')
            .then(response => response.json())
            .then(data => {
                if (!data.has_words) {
                    alert(data.message || 'Нет изученных слов для повторения');
                    return;
                }
                
                // Показываем слово для повторения
                startScreen.classList.add('hidden');
                wordCard.classList.remove('hidden');
                
                currentWord = data.word;
                currentWord.direction = data.direction;
                currentProgressId = data.progress_id;
                
                // Обновляем интерфейс
                if (data.direction === 'ru_to_jp') {
                    document.getElementById('direction-label').textContent = 'Напишите на японском (хираганой)';
                    document.getElementById('word-question').textContent = data.word.translation_ru;
                    // Не показываем подсказку с японским словом
                    document.getElementById('word-hint').classList.add('hidden');
                } else {
                    document.getElementById('direction-label').textContent = 'Напишите перевод на русском';
                    document.getElementById('word-question').textContent = data.word.japanese;
                    document.getElementById('word-hint').classList.add('hidden');
                }
                
                userAnswerInput.focus();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при загрузке слов');
            });
    }
});

// Функции управления списками слов
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
                            <h4 class="font-semibold text-white text-lg">${escapeWordHtml(list.name)}</h4>
                            <p class="text-gray-400 text-sm">${escapeWordHtml(list.description || 'Без описания')}</p>
                            <p class="text-gray-500 text-xs mt-1">${list.word_count} слов</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button onclick="openEditWordListModal(${list.id})" class="bg-blue-600 hover:bg-blue-500 px-3 py-2 rounded text-sm text-white">✏️</button>
                            <button onclick="deleteWordList(${list.id})" class="bg-red-600 hover:bg-red-500 px-3 py-2 rounded text-sm text-white">🗑️</button>
                            <button onclick="startWordListQuiz(${list.id})" class="bg-purple-600 hover:bg-purple-500 px-3 py-2 rounded text-sm text-white">▶️ Повторить</button>
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
                    </div>
                    
                    <div style="margin-bottom: 1.5rem; background: #1f2937; border-left: 4px solid #8b5cf6; padding: 1rem; border-radius: 6px;">
                        <label class="text-white text-sm block mb-2">📋 Добавить слова из списка (по ID)</label>
                        <p class="text-gray-400 text-xs mb-2">Вставьте ID слов через запятую (например: 1, 2, 3)</p>
                        <textarea id="word-bulk-input-${listId}" placeholder="Вставьте ID слов через запятую..." 
                                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 h-20 font-mono"></textarea>
                        <div style="margin-top: 0.5rem;">
                            <button type="button" onclick="addWordsFromBulkInput(${listId})" 
                                    style="background: #8b5cf6; border: none; color: white; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 0.875rem; transition: all 0.2s;"
                                    onmouseover="this.style.background='#7c3aed'"
                                    onmouseout="this.style.background='#8b5cf6'">
                                ↓ Добавить
                            </button>
                        </div>
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

function addWordsFromBulkInput(listId) {
    const input = document.getElementById(`word-bulk-input-${listId}`).value.trim();
    
    if (!input) {
        alert('Пожалуйста, вставьте ID слов');
        return;
    }
    
    // Парсим введенный текст
    const wordIds = input
        .split(/[\s,]+/)
        .map(s => parseInt(s.trim()))
        .filter(id => !isNaN(id) && id > 0);
    
    if (wordIds.length === 0) {
        alert('Не найдены корректные ID слов');
        return;
    }
    
    // Получаем текущий список слов
    const currentWordIds = new Set(
        Array.from(document.querySelectorAll(`#current-words-${listId} span`))
            .map(el => parseInt(el.textContent.match(/\d+/)[0]))
    );
    
    let addedCount = 0;
    let duplicateCount = 0;
    
    wordIds.forEach(wordId => {
        if (currentWordIds.has(wordId)) {
            duplicateCount++;
            return;
        }
        
        addWordToListEdit(listId, wordId);
        addedCount++;
    });
    
    document.getElementById(`word-bulk-input-${listId}`).value = '';
    
    let message = `✓ Добавлено: ${addedCount}`;
    if (duplicateCount > 0) {
        message += `. ⚠️ Уже в списке: ${duplicateCount}`;
    }
    alert(message);
}

function addWordToListEdit(listId, wordId) {
    const currentWordsContainer = document.getElementById(`current-words-${listId}`);
    
    const exists = Array.from(currentWordsContainer.querySelectorAll('span'))
        .some(el => {
            const match = el.textContent.match(/\d+/);
            return match && parseInt(match[0]) === wordId;
        });
    
    if (exists) {
        return;
    }
    
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
    wordEl.style.cssText = 'background: #4b5563; padding: 0.5rem 1rem; border-radius: 6px; border: 1px solid #6b7280; display: inline-flex; align-items: center; gap: 0.5rem;';
    wordEl.innerHTML = `
        <span style="font-size: 0.875rem;">ID: ${wordId}</span>
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
    const wordElements = container.querySelectorAll('span');
    
    wordElements.forEach(el => {
        const match = el.textContent.match(/\d+/);
        if (match && parseInt(match[0]) === wordId) {
            el.parentElement.remove();
        }
    });
    
    updateEditWordListUI(listId);
}

function updateEditWordListUI(listId) {
    const container = document.getElementById(`current-words-${listId}`);
    const wordCount = container.querySelectorAll('span').length;
    
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
    
    if (!name) {
        document.getElementById(`word-list-edit-error-${listId}`).textContent = 'Название не может быть пустым';
        document.getElementById(`word-list-edit-error-${listId}`).classList.remove('hidden');
        return;
    }
    
    const container = document.getElementById(`current-words-${listId}`);
    const wordIds = Array.from(container.querySelectorAll('span'))
        .map(el => {
            const match = el.textContent.match(/\d+/);
            return match ? parseInt(match[0]) : null;
        })
        .filter(id => id !== null);
    
    fetch(`/word-lists/${listId}`, {
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

function startWordListQuiz(listId) {
    // TODO: реализовать квиз из списка слов
    alert('Функция еще в разработке');
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

@push('styles')
<style>
    .japanese-font {
        font-family: 'Noto Sans JP', sans-serif;
    }
</style>
@endpush
@endsection

