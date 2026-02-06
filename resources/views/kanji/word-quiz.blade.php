@extends('layouts.app')

@section('title', 'Квиз по словам')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-purple-400 mb-2">🎯 Квиз по словам</h1>
        <p class="text-gray-400">
            Повторите {{ $count }} слов
            @if($wordType !== '')
                (тип: {{ $wordType }})
            @else
                (все типы)
            @endif
        </p>
    </div>

    <!-- Прогресс -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300">Прогресс</span>
            <span class="text-purple-300 font-semibold" id="progress-text">0 / {{ $count }}</span>
        </div>
        <div class="w-full bg-gray-700 rounded-full h-3">
            <div id="progress-bar" class="bg-gradient-to-r from-purple-500 to-indigo-500 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>

    <!-- Вопрос -->
    <div id="quiz-container" class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl p-8 border border-gray-700 shadow-2xl">
        <div id="question-container" class="text-center mb-8">
            <div class="text-6xl font-bold text-white mb-4 japanese-font" id="question-text" style="font-family: 'Noto Sans JP', sans-serif;"></div>
            <div class="text-xl text-gray-400 mb-4" id="question-hint"></div>
        </div>

        <div id="answers-container" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        </div>

        <div id="input-container" class="hidden mb-6">
            <div class="flex flex-col sm:flex-row gap-3 items-stretch">
                <input id="answer-input"
                       type="text"
                       class="flex-1 bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 japanese-font"
                       placeholder="Введите ответ..."
                       autocomplete="off"
                       autocapitalize="off"
                       spellcheck="false" />
                <button id="submit-input"
                        class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-purple-500/50">
                    Проверить
                </button>
            </div>
            <div class="text-gray-400 text-sm mt-2" id="input-hint"></div>
        </div>

        <div id="result-container" class="hidden text-center">
            <div id="result-icon" class="text-6xl mb-4"></div>
            <div id="result-text" class="text-2xl font-bold mb-4"></div>
            <div id="result-level" class="text-gray-400 mb-6"></div>
            <div id="after-answer-details" class="text-left max-w-2xl mx-auto mb-6">
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700 mb-3 hidden" id="after-reading-container">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-gray-400 text-sm mb-1">Чтение</div>
                            <div class="text-white text-lg font-semibold japanese-font" id="after-reading"></div>
                        </div>
                        <button id="speak-reading" class="bg-blue-700 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg transition-all">
                            🔊 Озвучить
                        </button>
                    </div>
                </div>
            </div>
            <button id="next-button"
                    class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-8 py-3 rounded-lg transition-all shadow-lg hover:shadow-purple-500/50 transform hover:scale-105">
                Следующий вопрос →
            </button>
        </div>

        <div id="finish-container" class="hidden text-center">
            <div class="text-6xl mb-4">🎉</div>
            <div class="text-3xl font-bold text-purple-400 mb-4">Квиз завершен!</div>
            <div class="text-gray-400 mb-6">Вы повторили все слова</div>
            <a href="{{ route('kanji.index', ['tab' => 'words']) }}"
               class="inline-block bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-8 py-3 rounded-lg transition-all shadow-lg hover:shadow-purple-500/50 transform hover:scale-105">
                Вернуться к списку слов
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .answer-button { transition: all 0.3s ease; }
    .answer-button:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(168, 85, 247, 0.4);
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
    const questionHint = document.getElementById('question-hint');
    const answersContainer = document.getElementById('answers-container');
    const resultContainer = document.getElementById('result-container');
    const resultIcon = document.getElementById('result-icon');
    const resultText = document.getElementById('result-text');
    const resultLevel = document.getElementById('result-level');
    const nextButton = document.getElementById('next-button');
    const finishContainer = document.getElementById('finish-container');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const inputContainer = document.getElementById('input-container');
    const answerInput = document.getElementById('answer-input');
    const submitInputBtn = document.getElementById('submit-input');
    const inputHint = document.getElementById('input-hint');
    const afterReadingContainer = document.getElementById('after-reading-container');
    const afterReading = document.getElementById('after-reading');
    const speakReadingBtn = document.getElementById('speak-reading');

    let currentQuestion = null;
    let answeredCount = 0;
    const totalCount = {{ $count }};
    let answered = false;
    const quizId = '{{ $quizId }}';
    const wordTypeParam = '{{ $wordType }}';

    loadQuestion();

    function loadQuestion() {
        answered = false;
        nextButton.style.display = '';
        resultContainer.classList.add('hidden');
        answersContainer.innerHTML = '';
        answersContainer.classList.remove('hidden');
        inputContainer.classList.add('hidden');
        if (answerInput) answerInput.value = '';
        if (answerInput) answerInput.disabled = false;
        if (submitInputBtn) submitInputBtn.disabled = false;

        const url = `{{ route('kanji.get-word-question') }}?count=${totalCount}&quiz_id=${encodeURIComponent(quizId)}${wordTypeParam ? '&word_type=' + encodeURIComponent(wordTypeParam) : ''}`;
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok && !data.no_more_questions) {
                throw new Error(data.error || 'Ошибка загрузки вопроса');
            }
            return data;
        })
        .then(data => {
            if (data && data.no_more_questions) {
                finishContainer.classList.remove('hidden');
                document.getElementById('quiz-container')?.classList.add('opacity-90');
                return;
            }
            currentQuestion = data;

            questionText.textContent = data.question;
            questionHint.textContent = 'Выберите или введите слово на японском (кандзи или фуригана):';
            if (data.answer_mode === 'input') {
                answerInput.placeholder = 'Введите слово: 私 или わたし';
                inputHint.textContent = 'Принимаются и кандзи, и чтение (фуригана).';
            }

            if (data.answer_mode === 'input') {
                answersContainer.classList.add('hidden');
                inputContainer.classList.remove('hidden');
                setTimeout(() => answerInput?.focus(), 50);
            } else {
                data.answers.forEach((answer) => {
                    const button = document.createElement('button');
                    button.className = 'answer-button bg-gray-700 hover:bg-gray-600 border-2 border-gray-600 text-white font-semibold px-6 py-4 rounded-lg text-lg japanese-font';
                    button.textContent = answer;
                    button.dataset.answer = answer;
                    button.onclick = () => selectAnswer(answer);
                    answersContainer.appendChild(button);
                });
                answersContainer.classList.remove('hidden');
            }
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
        if (answerInput) answerInput.disabled = true;
        if (submitInputBtn) submitInputBtn.disabled = true;

        fetch('{{ route("kanji.submit-word-answer") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                word_id: currentQuestion.word_id,
                answer: answer,
                quiz_id: quizId,
                question_id: currentQuestion.question_id,
            })
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.error || 'Ошибка отправки ответа');
            return data;
        })
        .then(data => {
            const serverCorrect = data.correct_answer;
            if (currentQuestion?.answer_mode !== 'input') {
                answersContainer.querySelectorAll('.answer-button').forEach(btn => {
                    if (btn.dataset.answer === serverCorrect) btn.classList.add('correct');
                    else if (btn.dataset.answer === answer) btn.classList.add('incorrect');
                });
            }

            answeredCount++;
            progressBar.style.width = (answeredCount / totalCount) * 100 + '%';
            progressText.textContent = answeredCount + ' / ' + totalCount;

            if (data.correct) {
                resultIcon.textContent = '✅';
                resultText.textContent = 'Правильно!';
                resultText.className = 'text-2xl font-bold mb-4 text-green-400';
            } else {
                resultIcon.textContent = '❌';
                resultText.textContent = 'Неправильно!';
                resultText.className = 'text-2xl font-bold mb-4 text-red-400';
            }
            resultLevel.textContent = 'Правильный ответ: ' + serverCorrect + ' | Уровень: ' + data.new_level + '/10';
            resultContainer.classList.remove('hidden');
            answersContainer.classList.add('hidden');
            inputContainer.classList.add('hidden');

            if (currentQuestion?.reading && String(currentQuestion.reading).trim() !== '') {
                afterReading.textContent = currentQuestion.reading;
                afterReadingContainer.classList.remove('hidden');
                if (window.speakJapanese) window.speakJapanese(String(currentQuestion.reading));
            } else {
                afterReadingContainer.classList.add('hidden');
            }

            if (answeredCount >= totalCount) {
                nextButton.style.display = 'none';
                setTimeout(() => {
                    resultContainer.classList.add('hidden');
                    finishContainer.classList.remove('hidden');
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Ошибка отправки ответа:', error);
            alert(error?.message || 'Ошибка отправки ответа.');
            answered = false;
            answersContainer.querySelectorAll('.answer-button').forEach(btn => { btn.disabled = false; btn.classList.remove('correct', 'incorrect'); });
            if (answerInput) answerInput.disabled = false;
            if (submitInputBtn) submitInputBtn.disabled = false;
        });
    }

    nextButton.addEventListener('click', function() {
        if (answeredCount < totalCount) loadQuestion();
    });

    function submitTypedAnswer() {
        const val = (answerInput?.value || '').trim();
        if (!val) return;
        selectAnswer(val);
    }
    submitInputBtn?.addEventListener('click', submitTypedAnswer);
    answerInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); submitTypedAnswer(); }
    });

    speakReadingBtn?.addEventListener('click', function() {
        const text = String(afterReading?.textContent || '').trim();
        if (window.speakJapanese) window.speakJapanese(text);
    });
});
</script>
@endpush
@endsection
