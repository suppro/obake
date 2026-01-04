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
            <div class="text-xl text-gray-400" id="question-hint"></div>
        </div>

        <div id="answers-container" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <!-- Варианты ответов будут добавлены через JavaScript -->
        </div>

        <div id="result-container" class="hidden text-center">
            <div id="result-icon" class="text-6xl mb-4"></div>
            <div id="result-text" class="text-2xl font-bold mb-4"></div>
            <div id="result-level" class="text-gray-400 mb-6"></div>
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
    
    let currentQuestion = null;
    let answeredCount = 0;
    const totalCount = {{ $count }};
    let answered = false;

    // Загружаем первый вопрос
    loadQuestion();

    function loadQuestion() {
        answered = false;
        resultContainer.classList.add('hidden');
        answersContainer.innerHTML = '';
        answersContainer.classList.remove('hidden');
        
        fetch(`{{ route('kanji.get-question') }}?count=${totalCount}&jlpt_level={{ $jlptLevel }}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            currentQuestion = data;
            
            // Скрываем изображение по умолчанию
            document.getElementById('question-image-container').classList.add('hidden');
            
            if (data.question_type === 'kanji_to_ru') {
                questionText.textContent = data.question;
                questionHint.textContent = 'Выберите правильный перевод:';
            } else {
                questionText.textContent = data.question;
                questionHint.textContent = 'Выберите правильный кандзи:';
                
                // Показываем изображение только для вопросов типа ru_to_kanji
                if (data.image_path) {
                    const imageUrl = '{{ asset("storage") }}/' + data.image_path;
                    document.getElementById('question-image').src = imageUrl;
                    document.getElementById('question-image-container').classList.remove('hidden');
                }
            }
            
            // Создаем кнопки с ответами
            data.answers.forEach((answer, index) => {
                const button = document.createElement('button');
                button.className = 'answer-button bg-gray-700 hover:bg-gray-600 border-2 border-gray-600 text-white font-semibold px-6 py-4 rounded-lg text-lg';
                button.textContent = answer;
                button.dataset.answer = answer;
                button.onclick = () => selectAnswer(answer);
                answersContainer.appendChild(button);
            });
        })
        .catch(error => {
            console.error('Ошибка загрузки вопроса:', error);
            alert('Ошибка загрузки вопроса. Попробуйте обновить страницу.');
        });
    }

    function selectAnswer(answer) {
        if (answered) return;
        answered = true;
        
        const buttons = answersContainer.querySelectorAll('.answer-button');
        buttons.forEach(btn => {
            btn.disabled = true;
            if (btn.dataset.answer === currentQuestion.correct_answer) {
                btn.classList.add('correct');
            } else if (btn.dataset.answer === answer) {
                btn.classList.add('incorrect');
            }
        });
        
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
                correct_answer: currentQuestion.correct_answer,
            })
        })
        .then(response => response.json())
        .then(data => {
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
            
            resultLevel.textContent = `Правильный ответ: ${currentQuestion.correct_answer} | Новый уровень: ${data.new_level}/10`;
            resultContainer.classList.remove('hidden');
            answersContainer.classList.add('hidden');
            
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
            alert('Ошибка отправки ответа. Попробуйте еще раз.');
            answered = false;
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
});
</script>
@endpush
@endsection

