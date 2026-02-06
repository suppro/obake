@extends('layouts.app')

@section('title', 'Квиз по кандзи')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-purple-400 mb-2">🎯 Квиз по кандзи</h1>
        <p class="text-gray-400">
            Повторите {{ $count }} кандзи
            @if($jlptLevel !== 'any')
                ({{ $jlptLevel === '5' ? 'N5' : ($jlptLevel === '4' ? 'N4' : ($jlptLevel === '3' ? 'N3' : ($jlptLevel === '2' ? 'N2' : 'N1'))) }})
            @else
                (любой уровень)
            @endif
            @if($forceInputMode ?? false)
                <span class="inline-block ml-2 bg-purple-600 text-white px-2 py-1 rounded text-xs">✍️ Только ручной ввод</span>
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
            <div id="question-image-container" class="mb-4 hidden">
                <img id="question-image" src="" alt="Kanji image" class="max-w-xs mx-auto rounded-lg">
            </div>
            <div class="text-6xl font-bold text-white mb-4" id="question-text" style="font-family: 'Noto Sans JP', sans-serif;"></div>
            <div class="text-xl text-gray-400 mb-4" id="question-hint"></div>
            <div id="hint-container" class="mb-4 hidden">
                <button id="hint-button" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold px-6 py-2 rounded-lg transition-all shadow-lg hover:shadow-yellow-500/50">
                    💡 Показать подсказку
                </button>
                <div id="hint-text" class="mt-4 p-4 bg-gray-700/50 rounded-lg border border-yellow-600/50 text-gray-300 hidden"></div>
            </div>
        </div>

        <div id="answers-container" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <!-- Варианты ответов будут добавлены через JavaScript -->
        </div>

        <!-- Ручной ввод (режим после 5/10) -->
        <div id="input-container" class="hidden mb-6">
            <div class="flex flex-col sm:flex-row gap-3 items-stretch">
                <input id="answer-input"
                       type="text"
                       class="flex-1 bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500"
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

            <!-- Детали после ответа -->
            <div id="after-answer-details" class="hidden text-left max-w-2xl mx-auto mb-6">
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700 mb-3 hidden" id="after-reading-container">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-gray-400 text-sm mb-1">Чтение</div>
                            <div class="text-white text-lg font-semibold" id="after-reading"></div>
                        </div>
                        <button id="speak-reading"
                                class="bg-blue-700 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg transition-all">
                            🔊 Озвучить
                        </button>
                    </div>
                </div>
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700 hidden" id="after-examples-container">
                    <div class="text-gray-400 text-sm mb-2">Примеры слов</div>
                    <div class="text-gray-200 text-sm leading-relaxed whitespace-pre-line" id="after-examples"></div>
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
            <div class="text-gray-400 mb-6">Вы повторили все кандзи</div>
            <a href="{{ route('kanji.index') }}" 
               class="inline-block bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-8 py-3 rounded-lg transition-all shadow-lg hover:shadow-purple-500/50 transform hover:scale-105">
                Вернуться к списку кандзи
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .answer-button {
        transition: all 0.3s ease;
    }
    .answer-button:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(168, 85, 247, 0.4);
    }
    .answer-button:disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }
    .answer-button.correct {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        border-color: #10b981 !important;
    }
    .answer-button.incorrect {
        background: linear-gradient(135deg, #ef4444, #dc2626) !important;
        border-color: #ef4444 !important;
    }
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
    const afterAnswerDetails = document.getElementById('after-answer-details');
    const afterReadingContainer = document.getElementById('after-reading-container');
    const afterReading = document.getElementById('after-reading');
    const speakReadingBtn = document.getElementById('speak-reading');
    const afterExamplesContainer = document.getElementById('after-examples-container');
    const afterExamples = document.getElementById('after-examples');
    
    let currentQuestion = null;
    let answeredCount = 0;
    const totalCount = {{ $count }};
    let answered = false;
    const quizId = '{{ $quizId }}';
    const hintContainer = document.getElementById('hint-container');
    const hintButton = document.getElementById('hint-button');
    const hintText = document.getElementById('hint-text');
    const questionImageContainer = document.getElementById('question-image-container');

    // Загружаем первый вопрос
    loadQuestion();

    function loadQuestion() {
        answered = false;
        resultContainer.classList.add('hidden');
        answersContainer.innerHTML = '';
        answersContainer.classList.remove('hidden');
        inputContainer.classList.add('hidden');
        if (answerInput) answerInput.value = '';
        if (answerInput) answerInput.disabled = false;
        if (submitInputBtn) submitInputBtn.disabled = false;
        if (afterAnswerDetails) afterAnswerDetails.classList.add('hidden');
        
        // Скрываем подсказку и изображение по умолчанию
        hintContainer.classList.add('hidden');
        hintText.classList.add('hidden');
        hintButton.textContent = '💡 Показать подсказку';
        questionImageContainer.classList.add('hidden');
        
        fetch(`{{ route('kanji.get-question') }}?count=${totalCount}&jlpt_level={{ $jlptLevel }}&force_input_mode={{ $forceInputMode ? '1' : '0' }}&quiz_id=${encodeURIComponent(quizId)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
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
                // Если вопросов больше нет — завершаем квиз корректно
                finishContainer.classList.remove('hidden');
                document.getElementById('quiz-container')?.classList.add('opacity-90');
                return;
            }
            currentQuestion = data;
            
            if (data.question_type === 'kanji_to_ru') {
                questionText.textContent = data.question;
                questionHint.textContent = 'Выберите правильный перевод:';
                
                // Показываем изображение только для kanji_to_ru
                if (data.image_path) {
                    let imageUrl;
                    if (data.image_path.startsWith('/storage/') || data.image_path.startsWith('http://') || data.image_path.startsWith('https://')) {
                        imageUrl = data.image_path;
                    } else if (data.image_path.startsWith('storage/')) {
                        imageUrl = '/' + data.image_path;
                    } else {
                        imageUrl = '{{ asset("storage") }}/' + data.image_path;
                    }
                    document.getElementById('question-image').src = imageUrl;
                    questionImageContainer.classList.remove('hidden');
                }
                
                // Показываем кнопку подсказки, если есть мнемоника
                if (data.mnemonic && data.mnemonic.trim() !== '') {
                    hintContainer.classList.remove('hidden');
                    hintText.textContent = data.mnemonic;
                } else {
                    hintContainer.classList.add('hidden');
                }
            } else {
                // ru_to_kanji - не показываем изображение
                questionText.textContent = data.question;
                questionHint.textContent = 'Выберите правильный кандзи:';
                
                // Показываем кнопку подсказки, если есть мнемоника
                if (data.mnemonic && data.mnemonic.trim() !== '') {
                    hintContainer.classList.remove('hidden');
                    hintText.textContent = data.mnemonic;
                } else {
                    hintContainer.classList.add('hidden');
                }
            }
            
            // Режим ответа: варианты или ручной ввод
            if (data.answer_mode === 'input') {
                answersContainer.classList.add('hidden');
                inputContainer.classList.remove('hidden');

                if (data.question_type === 'kanji_to_ru') {
                    answerInput.placeholder = 'Введите перевод на русский...';
                    inputHint.textContent = 'Режим ввода: напишите перевод и нажмите Enter или «Проверить».';
                } else {
                    answerInput.placeholder = 'Введите кандзи...';
                    inputHint.textContent = 'Режим ввода: напишите кандзи и нажмите Enter или «Проверить».';
                }

                setTimeout(() => answerInput?.focus(), 50);
            } else {
                // Создаем кнопки с ответами
                data.answers.forEach((answer) => {
                    const button = document.createElement('button');
                    button.className = 'answer-button bg-gray-700 hover:bg-gray-600 border-2 border-gray-600 text-white font-semibold px-6 py-4 rounded-lg text-lg';
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
        
        const buttons = answersContainer.querySelectorAll('.answer-button');
        buttons.forEach(btn => {
            btn.disabled = true;
        });

        if (answerInput) answerInput.disabled = true;
        if (submitInputBtn) submitInputBtn.disabled = true;
        
        // Отправляем ответ на сервер
        fetch('{{ route("kanji.submit-answer") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                kanji: currentQuestion.kanji,
                answer: answer,
                quiz_id: quizId,
                question_id: currentQuestion.question_id,
            })
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.error || 'Ошибка отправки ответа');
            }
            return data;
        })
        .then(data => {
            // Подсвечиваем правильный/неправильный уже после ответа (correct_answer серверный)
            const serverCorrect = data.correct_answer;
            if (currentQuestion?.answer_mode !== 'input') {
                const buttons2 = answersContainer.querySelectorAll('.answer-button');
                buttons2.forEach(btn => {
                    if (btn.dataset.answer === serverCorrect) {
                        btn.classList.add('correct');
                    } else if (btn.dataset.answer === answer) {
                        btn.classList.add('incorrect');
                    }
                });
            }

            answeredCount++;
            updateProgress();
            
            // Показываем результат
            if (data.correct) {
                resultIcon.textContent = '✅';
                resultText.textContent = 'Правильно!';
                resultText.className = 'text-2xl font-bold mb-4 text-green-400';
            } else {
                resultIcon.textContent = '❌';
                resultText.textContent = 'Неправильно!';
                resultText.className = 'text-2xl font-bold mb-4 text-red-400';
            }
            
            resultLevel.textContent = `Правильный ответ: ${serverCorrect} | Новый уровень: ${data.new_level}/10`;
            resultContainer.classList.remove('hidden');
            answersContainer.classList.add('hidden');
            inputContainer.classList.add('hidden');

            // Показываем чтение + примеры слов (если есть)
            if (afterAnswerDetails) afterAnswerDetails.classList.remove('hidden');
            if (currentQuestion?.reading && String(currentQuestion.reading).trim() !== '') {
                afterReading.textContent = currentQuestion.reading;
                afterReadingContainer.classList.remove('hidden');
            } else {
                afterReadingContainer.classList.add('hidden');
            }
            if (currentQuestion?.description && String(currentQuestion.description).trim() !== '') {
                afterExamples.textContent = currentQuestion.description;
                afterExamplesContainer.classList.remove('hidden');
            } else {
                afterExamplesContainer.classList.add('hidden');
            }

            // Авто-озвучка чтения (если доступно)
            if (currentQuestion?.reading && window.speakJapanese) {
                window.speakJapanese(String(currentQuestion.reading));
            }
            
            // Если это последний вопрос, показываем финальное сообщение
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
            alert(error?.message || 'Ошибка отправки ответа. Попробуйте еще раз.');
            // Разрешаем попробовать снова (и снимаем disabled)
            answered = false;
            const buttons3 = answersContainer.querySelectorAll('.answer-button');
            buttons3.forEach(btn => { btn.disabled = false; btn.classList.remove('correct', 'incorrect'); });
            if (answerInput) answerInput.disabled = false;
            if (submitInputBtn) submitInputBtn.disabled = false;
        });
    }

    function updateProgress() {
        const percent = (answeredCount / totalCount) * 100;
        progressBar.style.width = percent + '%';
        progressText.textContent = `${answeredCount} / ${totalCount}`;
    }

    nextButton.addEventListener('click', function() {
        if (answeredCount < totalCount) {
            loadQuestion();
        }
    });
    
    // Обработчик кнопки подсказки
    hintButton.addEventListener('click', function() {
        if (hintText.classList.contains('hidden')) {
            hintText.classList.remove('hidden');
            hintButton.textContent = '💡 Скрыть подсказку';
        } else {
            hintText.classList.add('hidden');
            hintButton.textContent = '💡 Показать подсказку';
        }
    });

    // Ручной ввод: Enter / кнопка
    function submitTypedAnswer() {
        const val = (answerInput?.value || '').trim();
        if (!val) return;
        selectAnswer(val);
    }

    submitInputBtn?.addEventListener('click', function() {
        submitTypedAnswer();
    });

    answerInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitTypedAnswer();
        }
    });

    // Озвучка чтения через глобальную функцию (определена в layouts/app.blade.php)
    // window.speakJapanese уже доступна

    // Кнопка озвучки
    speakReadingBtn?.addEventListener('click', function() {
        const text = String(afterReading?.textContent || '').trim();
        if (window.speakJapanese) {
            window.speakJapanese(text);
        }
    });
});
</script>
@endpush
@endsection

