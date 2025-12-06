@extends('layouts.app')

@section('title', 'Повторение слов - Obake')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-4xl font-bold text-purple-400">📚 Повторение слов</h1>
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

