@extends('layouts.app')

@section('title', 'Тренировка спряжений')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-purple-400 mb-2">📝 Тренировка спряжений</h1>
        <p class="text-gray-400">Изучайте спряжения японских глаголов и прилагательных</p>
        <div class="mt-4">
            <a href="{{ route('conjugation.guide') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                📖 Открыть гайд по спряжениям
            </a>
        </div>
    </div>

    <!-- Настройки тренировки -->
    <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700 mb-8">
        <h3 class="text-xl font-bold text-purple-400 mb-4">Настройки тренировки</h3>
        
        <div class="space-y-6">
            <!-- Тип слова -->
            <div>
                <label class="block text-gray-300 mb-2">Тип слова:</label>
                <div class="flex gap-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="word_type" value="verb" class="mr-2" checked>
                        <span class="text-gray-200">Глаголы</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="word_type" value="adjective" class="mr-2">
                        <span class="text-gray-200">Прилагательные</span>
                    </label>
                </div>
            </div>

            <!-- Группа глаголов -->
            <div id="verb_group_settings">
                <label class="block text-gray-300 mb-2">Группа глаголов:</label>
                <div class="flex flex-wrap gap-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="verb_group" value="group1" class="mr-2" checked>
                        <span class="text-gray-200">Группа I (Godan/у-глаголы)</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="verb_group" value="group2" class="mr-2" checked>
                        <span class="text-gray-200">Группа II (Ichidan/ру-глаголы)</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="verb_group" value="group3" class="mr-2" checked>
                        <span class="text-gray-200">Группа III (неправильные)</span>
                    </label>
                </div>
            </div>

            <!-- Тип прилагательных -->
            <div id="adjective_type_settings" class="hidden">
                <label class="block text-gray-300 mb-2">Тип прилагательных:</label>
                <div class="flex flex-wrap gap-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="adjective_type" value="i_adjectives" class="mr-2" checked>
                        <span class="text-gray-200">I-прилагательные (い-прилагательные)</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="adjective_type" value="na_adjectives" class="mr-2" checked>
                        <span class="text-gray-200">Na-прилагательные</span>
                    </label>
                </div>
            </div>

            <!-- Формы для тренировки -->
            <div id="verb_forms_settings">
                <label class="block text-gray-300 mb-2">Формы для тренировки (глаголы):</label>
                <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-2 bg-gray-900/50 rounded">
                    @foreach($verbForms as $formKey => $form)
                        <label class="flex items-center cursor-pointer whitespace-nowrap">
                            <input type="checkbox" name="verb_form" value="{{ $formKey }}" class="mr-2" checked>
                            <span class="text-gray-200 text-sm">{{ $form['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="adjective_forms_settings" class="hidden">
                <label class="block text-gray-300 mb-2">Формы для тренировки (прилагательные):</label>
                <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-2 bg-gray-900/50 rounded">
                    @foreach($adjectiveForms as $formKey => $form)
                        <label class="flex items-center cursor-pointer whitespace-nowrap">
                            <input type="checkbox" name="adjective_form" value="{{ $formKey }}" class="mr-2" checked>
                            <span class="text-gray-200 text-sm">{{ $form['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button id="start_quiz" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition">
                Начать тренировку
            </button>
        </div>
    </div>

    <!-- Область для тренировки -->
    <div id="quiz_area" class="hidden">
        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-br from-purple-800/50 to-indigo-800/50 rounded-xl p-6 border border-purple-700/50">
                <div class="text-3xl font-bold text-purple-300 mb-1" id="total_questions">0</div>
                <div class="text-gray-300">Всего вопросов</div>
            </div>
            <div class="bg-gradient-to-br from-green-800/50 to-emerald-800/50 rounded-xl p-6 border border-green-700/50">
                <div class="text-3xl font-bold text-green-300 mb-1" id="correct_answers">0</div>
                <div class="text-gray-300">Правильных</div>
            </div>
            <div class="bg-gradient-to-br from-red-800/50 to-pink-800/50 rounded-xl p-6 border border-red-700/50">
                <div class="text-3xl font-bold text-red-300 mb-1" id="wrong_answers">0</div>
                <div class="text-gray-300">Неправильных</div>
            </div>
        </div>

        <!-- Вопрос -->
        <div class="bg-gray-800/50 rounded-xl p-8 border border-gray-700 mb-6">
            <div id="question_content">
                <div class="text-center mb-6">
                    <div class="text-2xl text-gray-300 mb-2">Напишите спряжение:</div>
                    <div class="text-4xl font-bold text-purple-400 mb-2" id="current_word" style="font-family: 'Noto Sans JP', sans-serif;">-</div>
                    <div class="text-xl text-gray-400 mb-1" id="current_word_reading">-</div>
                    <div class="text-lg text-gray-500 mb-4" id="current_word_meaning">-</div>
                    <div class="text-2xl text-purple-300 font-semibold" id="current_form_name">-</div>
                </div>

                <div class="max-w-md mx-auto">
                    <input 
                        type="text" 
                        id="answer_input" 
                        placeholder="Введите ответ..."
                        class="w-full bg-gray-900 text-white text-2xl text-center py-4 px-6 rounded-lg border border-gray-600 focus:border-purple-500 focus:ring-2 focus:ring-purple-500 outline-none"
                        style="font-family: 'Noto Sans JP', sans-serif;"
                        autocomplete="off"
                    >
                    <div class="flex gap-4 mt-4">
                        <button 
                            id="check_answer_btn" 
                            class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition"
                        >
                            Проверить
                        </button>
                        <button 
                            id="next_question_btn" 
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition hidden"
                        >
                            Следующий
                        </button>
                    </div>
                </div>
            </div>

            <!-- Результат -->
            <div id="result_content" class="hidden text-center">
                <div id="result_message" class="text-2xl font-bold mb-4"></div>
                <div class="text-xl mb-2">
                    <span class="text-gray-400">Ваш ответ:</span>
                    <span id="user_answer_display" class="text-yellow-400 font-semibold" style="font-family: 'Noto Sans JP', sans-serif;"></span>
                </div>
                <div class="text-xl mb-4">
                    <span class="text-gray-400">Правильный ответ:</span>
                    <span id="correct_answer_display" class="text-green-400 font-semibold" style="font-family: 'Noto Sans JP', sans-serif;"></span>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button 
                id="stop_quiz" 
                class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition"
            >
                Завершить тренировку
            </button>
        </div>
    </div>
</div>

<script>
let stats = {
    total: 0,
    correct: 0,
    wrong: 0
};

let currentQuestion = null;

// Переключение между типами слов
document.querySelectorAll('input[name="word_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'verb') {
            document.getElementById('verb_group_settings').classList.remove('hidden');
            document.getElementById('verb_forms_settings').classList.remove('hidden');
            document.getElementById('adjective_type_settings').classList.add('hidden');
            document.getElementById('adjective_forms_settings').classList.add('hidden');
        } else {
            document.getElementById('verb_group_settings').classList.add('hidden');
            document.getElementById('verb_forms_settings').classList.add('hidden');
            document.getElementById('adjective_type_settings').classList.remove('hidden');
            document.getElementById('adjective_forms_settings').classList.remove('hidden');
        }
    });
});

// Начало тренировки
document.getElementById('start_quiz').addEventListener('click', function() {
    document.getElementById('quiz_area').classList.remove('hidden');
    stats = { total: 0, correct: 0, wrong: 0 };
    updateStats();
    getNextQuestion();
});

// Остановка тренировки
document.getElementById('stop_quiz').addEventListener('click', function() {
    if (confirm('Завершить тренировку?')) {
        document.getElementById('quiz_area').classList.add('hidden');
        document.getElementById('question_content').classList.remove('hidden');
        document.getElementById('result_content').classList.add('hidden');
        document.getElementById('next_question_btn').classList.add('hidden');
        document.getElementById('check_answer_btn').classList.remove('hidden');
        document.getElementById('answer_input').value = '';
    }
});

// Загрузка выбранных форм из URL параметров
function loadFormsFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const verbForms = urlParams.get('verb_forms');
    const adjectiveForms = urlParams.get('adjective_forms');
    
    if (verbForms) {
        const forms = verbForms.split(',');
        document.querySelectorAll('input[name="verb_form"]').forEach(cb => {
            cb.checked = forms.includes(cb.value);
        });
    }
    
    if (adjectiveForms) {
        const forms = adjectiveForms.split(',');
        document.querySelectorAll('input[name="adjective_form"]').forEach(cb => {
            cb.checked = forms.includes(cb.value);
        });
    }
}

// Загружаем формы из URL при загрузке страницы
document.addEventListener('DOMContentLoaded', loadFormsFromUrl);

// Получение следующего вопроса
async function getNextQuestion() {
    const wordType = document.querySelector('input[name="word_type"]:checked').value;
    
    if (wordType === 'verb') {
        const groups = Array.from(document.querySelectorAll('input[name="verb_group"]:checked'))
            .map(cb => cb.value);
        
        if (groups.length === 0) {
            alert('Выберите хотя бы одну группу глаголов');
            return;
        }
        
        const group = groups[Math.floor(Math.random() * groups.length)];
        
        const forms = Array.from(document.querySelectorAll('input[name="verb_form"]:checked'))
            .map(cb => cb.value);
        
        if (forms.length === 0) {
            alert('Выберите хотя бы одну форму для тренировки');
            return;
        }
        
        try {
            const params = new URLSearchParams({
                type: 'verb',
                group: group || '',
                forms: forms.join(',')
            });
            
            const response = await fetch(`{{ route('conjugation.get-question') }}?${params.toString()}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (!response.ok) {
                throw new Error('Ошибка получения вопроса');
            }
            
            const data = await response.json();
            currentQuestion = data;
            
            document.getElementById('current_word').textContent = data.word.word;
            document.getElementById('current_word_reading').textContent = data.word.reading;
            document.getElementById('current_word_meaning').textContent = data.word.meaning;
            document.getElementById('current_form_name').textContent = data.formName;
            document.getElementById('answer_input').value = '';
            document.getElementById('answer_input').focus();
            
            document.getElementById('question_content').classList.remove('hidden');
            document.getElementById('result_content').classList.add('hidden');
            document.getElementById('next_question_btn').classList.add('hidden');
            document.getElementById('check_answer_btn').classList.remove('hidden');
        } catch (error) {
            console.error('Ошибка получения вопроса:', error);
            alert('Ошибка получения вопроса: ' + error.message);
        }
    } else {
        const adjectiveTypes = Array.from(document.querySelectorAll('input[name="adjective_type"]:checked'))
            .map(cb => cb.value);
        
        if (adjectiveTypes.length === 0) {
            alert('Выберите хотя бы один тип прилагательных');
            return;
        }
        
        const adjectiveType = adjectiveTypes[Math.floor(Math.random() * adjectiveTypes.length)];
        
        const forms = Array.from(document.querySelectorAll('input[name="adjective_form"]:checked'))
            .map(cb => cb.value);
        
        if (forms.length === 0) {
            alert('Выберите хотя бы одну форму для тренировки');
            return;
        }
        
        try {
            const params = new URLSearchParams({
                type: 'adjective',
                adjective_type: adjectiveType || '',
                forms: forms.join(',')
            });
            
            const response = await fetch(`{{ route('conjugation.get-question') }}?${params.toString()}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (!response.ok) {
                throw new Error('Ошибка получения вопроса');
            }
            
            const data = await response.json();
            currentQuestion = data;
            
            document.getElementById('current_word').textContent = data.word.word;
            document.getElementById('current_word_reading').textContent = data.word.reading;
            document.getElementById('current_word_meaning').textContent = data.word.meaning;
            document.getElementById('current_form_name').textContent = data.formName;
            document.getElementById('answer_input').value = '';
            document.getElementById('answer_input').focus();
            
            document.getElementById('question_content').classList.remove('hidden');
            document.getElementById('result_content').classList.add('hidden');
            document.getElementById('next_question_btn').classList.add('hidden');
            document.getElementById('check_answer_btn').classList.remove('hidden');
        } catch (error) {
            console.error('Ошибка получения вопроса:', error);
            alert('Ошибка получения вопроса: ' + error.message);
        }
    }
}

// Проверка ответа
document.getElementById('check_answer_btn').addEventListener('click', async function() {
    const answer = document.getElementById('answer_input').value.trim();
    if (!answer) {
        alert('Введите ответ');
        return;
    }
    
    try {
        const response = await fetch('{{ route('conjugation.check-answer') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ answer: answer })
        });
        
        const data = await response.json();
        
        stats.total++;
        if (data.correct) {
            stats.correct++;
            document.getElementById('result_message').textContent = '✓ Правильно!';
            document.getElementById('result_message').classList.remove('text-red-400');
            document.getElementById('result_message').classList.add('text-green-400');
        } else {
            stats.wrong++;
            document.getElementById('result_message').textContent = '✗ Неправильно';
            document.getElementById('result_message').classList.remove('text-green-400');
            document.getElementById('result_message').classList.add('text-red-400');
        }
        
        document.getElementById('user_answer_display').textContent = data.userAnswer;
        document.getElementById('correct_answer_display').textContent = data.correctAnswer;
        
        document.getElementById('question_content').classList.add('hidden');
        document.getElementById('result_content').classList.remove('hidden');
        document.getElementById('check_answer_btn').classList.add('hidden');
        document.getElementById('next_question_btn').classList.remove('hidden');
        
        updateStats();
    } catch (error) {
        console.error('Ошибка проверки ответа:', error);
        alert('Ошибка проверки ответа');
    }
});

// Следующий вопрос
document.getElementById('next_question_btn').addEventListener('click', function() {
    getNextQuestion();
});

// Enter для проверки/следующего вопроса
document.getElementById('answer_input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        if (!document.getElementById('check_answer_btn').classList.contains('hidden')) {
            document.getElementById('check_answer_btn').click();
        } else {
            document.getElementById('next_question_btn').click();
        }
    }
});

function updateStats() {
    document.getElementById('total_questions').textContent = stats.total;
    document.getElementById('correct_answers').textContent = stats.correct;
    document.getElementById('wrong_answers').textContent = stats.wrong;
}

// Фокус на поле ввода при загрузке
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('answer_input');
    if (input) {
        input.addEventListener('focus', function() {
            // Убеждаемся что клавиатура открывается для японского ввода
            if (input.inputMode !== 'text') {
                input.setAttribute('inputmode', 'text');
            }
        });
    }
});
</script>

<style>
/* Стили для японского текста */
[style*="font-family: 'Noto Sans JP'"] {
    font-family: 'Noto Sans JP', sans-serif;
    letter-spacing: 0.05em;
}
</style>
@endsection

