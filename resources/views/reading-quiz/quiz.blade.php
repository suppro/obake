@extends('layouts.app')

@section('title', 'Квиз на чтение')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-blue-400 mb-2">📖 Квиз на чтение</h1>
        <p class="text-gray-400">
            Повторите чтение {{ $count }} слов
        </p>
    </div>

    <!-- Прогресс -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300">Прогресс</span>
            <span class="text-blue-300 font-semibold" id="progress-text">0 / {{ $count }}</span>
        </div>
        <div class="w-full bg-gray-700 rounded-full h-3">
            <div id="progress-bar" class="bg-gradient-to-r from-blue-500 to-cyan-500 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>

    <!-- Вопрос -->
    <div id="quiz-container" class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-8 border border-gray-700 shadow-2xl">
        <div id="question-container" class="text-center mb-8">
            <div class="text-sm text-gray-400 mb-2" id="question-type-hint"></div>
            <div class="text-6xl font-bold text-white mb-4 japanese-font" id="question-text" style="font-family: 'Noto Sans JP', sans-serif;"></div>
            <div class="text-gray-400 text-sm" id="question-answer-info"></div>
        </div>

        <div id="answers-container" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        </div>

        <div id="result-container" class="hidden text-center">
            <div id="result-icon" class="text-8xl mb-4"></div>
            <div id="result-text" class="text-4xl font-bold mb-4"></div>
            <div id="result-level" class="text-gray-400 text-lg mb-6"></div>
            <div id="after-answer-details" class="text-left max-w-2xl mx-auto mb-6">
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700 mb-3 hidden" id="word-info-container">
                    <div class="mb-3">
                        <div class="text-gray-400 text-sm mb-1">Слово</div>
                        <div class="text-white text-2xl font-semibold japanese-font" id="word-display"></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-gray-400 text-sm mb-1">Чтение</div>
                        <div class="text-white text-xl font-semibold japanese-font" id="reading-display"></div>
                    </div>
                    <div class="mb-3" id="translation-display-container">
                        <div class="text-gray-400 text-sm mb-1">Перевод</div>
                        <div class="text-white" id="translation-display"></div>
                    </div>
                </div>
            </div>
            <button id="next-button"
                    class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-semibold px-8 py-4 rounded-lg transition-all shadow-lg hover:shadow-blue-500/50 transform hover:scale-105 text-lg">
                Следующий вопрос →
            </button>
        </div>

        <div id="finish-container" class="hidden text-center">
            <div class="text-6xl mb-4">🎉</div>
            <div class="text-3xl font-bold text-blue-400 mb-4">Квиз завершен!</div>
            <div class="text-gray-400 mb-6">Вы повторили чтение всех слов</div>
            <div id="list-finish-info" class="mb-4"></div>
            <div id="finish-actions" class="space-x-3">
                <a id="finish-return-main" href="{{ route('reading-quiz.index') }}"
                   class="inline-block bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-2 rounded-lg transition-all shadow-lg">Вернуться</a>
                <a id="finish-repeat-quiz" href="#"
                   class="inline-block bg-blue-600 hover:bg-blue-500 text-white font-semibold px-6 py-2 rounded-lg transition-all shadow-lg">Повторить квиз</a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .answer-button { transition: all 0.3s ease; }
    .answer-button:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(34, 197, 244, 0.4);
    }
    .answer-button:disabled { cursor: not-allowed; opacity: 0.7; }
    .answer-button.correct {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        border-color: #10b981 !important;
    }
    .answer-button.incorrect {
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        border-color: #ef4444 !important;
    }
    .japanese-font { font-family: 'Noto Sans JP', sans-serif; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionText = document.getElementById('question-text');
    const questionTypeHint = document.getElementById('question-type-hint');
    const questionAnswerInfo = document.getElementById('question-answer-info');
    const answersContainer = document.getElementById('answers-container');
    const resultContainer = document.getElementById('result-container');
    const resultIcon = document.getElementById('result-icon');
    const resultText = document.getElementById('result-text');
    const resultLevel = document.getElementById('result-level');
    const nextButton = document.getElementById('next-button');
    const finishContainer = document.getElementById('finish-container');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const wordInfoContainer = document.getElementById('word-info-container');
    const wordDisplay = document.getElementById('word-display');
    const readingDisplay = document.getElementById('reading-display');
    const translationDisplay = document.getElementById('translation-display');

    let currentQuestion = null;
    let answeredCount = 0;
    const totalCount = {{ $count }};
    let answered = false;
    const quizId = '{{ $quizId }}';
    const listIdParam = '{{ $listId ?? '' }}';

    console.log('=== QUIZ PAGE LOADED ===');
    console.log('Total count:', totalCount);
    console.log('Quiz ID:', quizId);
    console.log('List ID:', listIdParam);
    
    // Очищаем сессию для нового квиза (запрашиваем сброс с сервера)
    if (quizId) {
        fetch('/reading-quiz/reset-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quiz_id: quizId })
        })
        .then(r => r.json())
        .then(d => console.log('Session reset result:', d))
        .catch(e => console.error('Error resetting session:', e));
    }
    
    // Если есть список, проверяем его слова
    if (listIdParam) {
        fetch(`/reading-quiz-lists/${listIdParam}/words`)
            .then(r => r.json())
            .then(d => {
                console.log('Words in list:', d);
                if (d.words && d.words.length > 0) {
                    console.log('Words IDs:', d.words);
                } else {
                    console.error('NO WORDS IN LIST!');
                }
            })
            .catch(e => console.error('Error fetching words:', e));
    }

    loadQuestion();

    function loadQuestion() {
        answered = false;
        nextButton.style.display = '';
        resultContainer.classList.add('hidden');
        answersContainer.innerHTML = '';
        answersContainer.classList.remove('hidden');

        let url = `{{ route('reading-quiz.get-question') }}?count=${totalCount}&quiz_id=${encodeURIComponent(quizId)}`;
        if (listIdParam) {
            url += `&list_id=${encodeURIComponent(listIdParam)}`;
        }
        console.log('Loading question with URL:', url);
        
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            console.log('Question response status:', response.status);
            console.log('Question response data:', data);
            if (!response.ok && !data.no_more_questions) {
                throw new Error(data.error || 'Ошибка загрузки вопроса');
            }
            return data;
        })
        .then(data => {
            if (data && data.no_more_questions) {
                console.error('ERROR: no_more_questions returned!', data);
                finishContainer.classList.remove('hidden');
                document.getElementById('quiz-container')?.classList.add('opacity-90');
                return;
            }
            currentQuestion = data;
            console.log('Current question loaded:', data);

            // Определяем тип вопроса
            const isWordToReading = data.question_type === 'word_to_reading';
            if (isWordToReading) {
                questionTypeHint.textContent = 'Прочитайте слово:';
                questionAnswerInfo.textContent = 'Выберите правильное чтение';
            } else {
                questionTypeHint.textContent = 'Назовите слово:';
                questionAnswerInfo.textContent = 'Выберите правильное слово';
            }

            questionText.textContent = data.question;

            // Создаем кнопки ответов
            data.answers.forEach((answer) => {
                const button = document.createElement('button');
                button.className = 'answer-button bg-gray-700 hover:bg-gray-600 border-2 border-gray-600 text-white font-semibold px-8 py-5 rounded-lg text-lg japanese-font';
                button.textContent = answer;
                button.dataset.answer = answer;
                button.onclick = () => selectAnswer(answer);
                answersContainer.appendChild(button);
            });
            answersContainer.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Ошибка загрузки вопроса:', error);
            alert(error?.message || 'Ошибка загрузки вопроса. Попробуйте обновить страницу.');
        });
    }

    function selectAnswer(answer) {
        if (answered) return;
        answered = true;

        answersContainer.querySelectorAll('.answer-button').forEach(btn => { btn.disabled = true; });

        const submitData = {
            word_id: currentQuestion.word_id,
            answer: answer,
            quiz_id: quizId,
            question_id: currentQuestion.question_id,
        };
        console.log('Sending answer with data:', submitData);
        console.log('Current question:', currentQuestion);

        fetch('{{ route('reading-quiz.submit-answer') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(submitData)
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            console.log('Response status:', response.status);
            console.log('Response data:', data);
            if (!response.ok) throw new Error(data.error || 'Ошибка отправки ответа');
            return data;
        })
        .then(data => {
            const serverCorrect = data.correct_answer;
            answersContainer.querySelectorAll('.answer-button').forEach(btn => {
                if (btn.dataset.answer === serverCorrect) btn.classList.add('correct');
                else if (btn.dataset.answer === answer) btn.classList.add('incorrect');
            });

            answeredCount++;
            progressBar.style.width = (answeredCount / totalCount) * 100 + '%';
            progressText.textContent = answeredCount + ' / ' + totalCount;

            if (data.correct) {
                resultIcon.textContent = '✅';
                resultText.textContent = 'Правильно!';
                resultText.className = 'text-4xl font-bold mb-4 text-green-400';
            } else {
                resultIcon.textContent = '❌';
                resultText.textContent = 'Неправильно!';
                resultText.className = 'text-4xl font-bold mb-4 text-red-400';
            }
            resultLevel.textContent = 'Правильный ответ: ' + serverCorrect + ' | Уровень: ' + data.new_level + '/10';

            // Показываем информацию о слове
            wordDisplay.textContent = data.japanese_word || '';
            readingDisplay.textContent = data.reading || '';
            translationDisplay.textContent = currentQuestion.translation_ru || 'Нет перевода';
            wordInfoContainer.classList.remove('hidden');

            resultContainer.classList.remove('hidden');
            answersContainer.classList.add('hidden');

            if (answeredCount >= totalCount) {
                nextButton.style.display = 'none';
                setTimeout(() => {
                    resultContainer.classList.add('hidden');
                    finishContainer.classList.remove('hidden');
                    showListFinishOptions();
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Ошибка отправки ответа:', error);
            alert(error?.message || 'Ошибка отправки ответа.');
            answered = false;
            answersContainer.querySelectorAll('.answer-button').forEach(btn => { btn.disabled = false; btn.classList.remove('correct', 'incorrect'); });
        });
    }

    nextButton.addEventListener('click', function() {
        if (answeredCount < totalCount) loadQuestion();
    });

    // При нажатии Enter в окне результата — переход к следующему вопросу
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Enter') return;
        if (!resultContainer) return;
        if (!resultContainer.classList.contains('hidden')) {
            if (answeredCount < totalCount) {
                e.preventDefault();
                nextButton.click();
            }
        }
    });

    // Показать информацию о завершении списка
    function showListFinishOptions() {
        const listId = listIdParam || '';
        if (!listId) {
            document.getElementById('finish-return-main').href = '{{ route('reading-quiz.index') }}';
            document.getElementById('finish-repeat-quiz').href = '{{ route('reading-quiz.quiz') }}?count=' + totalCount;
            return;
        }
        const infoEl = document.getElementById('list-finish-info');
        const returnBtn = document.getElementById('finish-return-main');
        const repeatBtn = document.getElementById('finish-repeat-quiz');

        console.log('Setting up list finish options. ListID:', listId);
        console.log('Repeat button element:', repeatBtn);

        function escapeHtmlLocal(text) {
            if (!text) return '';
            return String(text).replace(/[&<>\"']/g, function(m) {
                return ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":"&#039;"})[m];
            });
        }

        fetch('{{ route('reading-quiz-lists.index') }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(json => {
                const lists = json.lists || [];
                const list = lists.find(l => String(l.id) === String(listId));
                if (!list) return;

                const percent = Math.round(Number(list.progress_percent) || 0);

                infoEl.innerHTML = `
                    <div class="mb-3">
                        <div class="text-sm text-gray-300">Прогресс списка "${escapeHtmlLocal(list.name)}"</div>
                        <div style="width:320px; height:10px; background-color: rgba(75,85,99,0.25); border-radius:9999px; overflow:hidden; margin:6px auto 0;">
                            <div style="height:100%; width: ${percent}%; background: linear-gradient(90deg, #06b6d4 0%, #0891b2 100%); border-radius:9999px;"></div>
                        </div>
                        <div class="text-gray-400 text-xs mt-2 text-center">${percent}% — ${list.word_count || 0} слов, ${list.completed_count || 0} завершено</div>
                        <div class="text-gray-400 text-xs mt-2 text-center">📚 Повторений: ${list.repetitions_completed || 0}</div>
                    </div>
                `;

                // Если список на 100%, увеличиваем счётчик повторений
                if (percent === 100) {
                    const completeUrl = '{{ url('/reading-quiz-lists') }}/' + listId + '/complete-repetition';
                    fetch(completeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    }).catch(err => console.error('Failed to record list repetition', err));
                }

                returnBtn.href = '{{ route('reading-quiz.index') }}';
                const quizUrl = '{{ route('reading-quiz.quiz') }}?list_id=' + listId + '&count=' + (list.word_count || totalCount);
                repeatBtn.href = quizUrl;
                
                console.log('Set repeat button href to:', quizUrl);

                // Удаляем старые event listeners если были
                const newRepeatBtn = repeatBtn.cloneNode(true);
                repeatBtn.parentNode.replaceChild(newRepeatBtn, repeatBtn);
                
                // Добавляем новый listener
                newRepeatBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Repeat button clicked. Going to:', this.href);
                    window.location.href = this.href;
                });
            })
            .catch(err => console.error('Failed to load reading quiz lists', err));
    }
});
</script>
@endpush
@endsection
