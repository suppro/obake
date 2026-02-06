@extends('layouts.app')

@section('title', 'Гайд по спряжениям')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-purple-400 mb-2">📖 Полный гайд по спряжениям</h1>
        <p class="text-gray-400">Изучите все формы спряжения японских глаголов и прилагательных. Отмечайте изученные формы для тренировки.</p>
        <div class="mt-4 flex gap-4">
            <a href="{{ route('conjugation.index') }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition">
                ← Вернуться к тренировке
            </a>
            <button id="clear_selection" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition">
                Сбросить выбор
            </button>
            <a href="{{ route('conjugation.index') }}" id="practice_link" class="hidden bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                Тренировать выбранные формы →
            </a>
        </div>
    </div>

    <!-- Глаголы -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-purple-400 mb-6">Глаголы</h2>
        
        <!-- Группа I (Godan/у-глаголы) -->
        <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h3 class="text-2xl font-bold text-blue-400 mb-4">Группа I (Godan / う-глаголы)</h3>
            <p class="text-gray-300 mb-4">
                Глаголы группы I оканчиваются на слоги う (u), кроме る (ru). При спряжении изменяется последний слог.
                <br><span class="text-gray-400">Примеры: 書く (писать), 話す (говорить), 読む (читать), 買う (покупать), 行く (идти)</span>
            </p>
            
            <div class="space-y-6">
                @foreach($verbForms as $formKey => $form)
                    <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-600 form-section" data-type="verb" data-form="{{ $formKey }}">
                        <div class="flex items-start gap-4">
                            <label class="flex items-center cursor-pointer flex-shrink-0 mt-1">
                                <input type="checkbox" 
                                       class="form-checkbox h-5 w-5 text-purple-600 rounded focus:ring-purple-500 focus:ring-2" 
                                       data-type="verb" 
                                       data-form="{{ $formKey }}"
                                       data-group="group1,group2,group3">
                                <span class="ml-2 text-gray-300 font-semibold">Изучено</span>
                            </label>
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-purple-300 mb-2">{{ $form['name'] }}</h4>
                                <p class="text-gray-300 mb-3">{{ $form['description'] }}</p>
                                
                                <div class="bg-gray-800/50 rounded p-3 mb-3">
                                    <div class="text-sm text-gray-400 mb-1">Примеры использования:</div>
                                    <div class="text-gray-200" style="font-family: 'Noto Sans JP', sans-serif;">{{ $form['usage'] }}</div>
                                </div>
                                
                                <!-- Примеры спряжений -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @foreach($exampleVerbs['group1'] as $verb)
                                        @if(isset($verb['conjugations'][$formKey]))
                                            <div class="bg-gray-800/30 rounded p-2 border border-gray-700">
                                                <div class="text-sm text-gray-400 mb-1">{{ $verb['word'] }} ({{ $verb['reading'] }})</div>
                                                <div class="text-lg font-semibold text-white" style="font-family: 'Noto Sans JP', sans-serif;">
                                                    {{ $verb['conjugations'][$formKey] }}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $verb['meaning'] }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Группа II (Ichidan/ру-глаголы) -->
        <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h3 class="text-2xl font-bold text-green-400 mb-4">Группа II (Ichidan / る-глаголы)</h3>
            <p class="text-gray-300 mb-4">
                Глаголы группы II оканчиваются на る (ru), который предшествует i или e. При спряжении просто убирается る.
                <br><span class="text-gray-400">Примеры: 食べる (есть), 見る (смотреть), 起きる (вставать)</span>
            </p>
            
            <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-600 mb-4">
                <p class="text-gray-300">
                    <strong class="text-green-300">Примечание:</strong> Все формы спряжения для глаголов группы II образуются одинаково - просто убирается る и добавляется нужное окончание.
                    <br>Пример: 食べる → 食べます, 食べて, 食べたい и т.д.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($exampleVerbs['group2'] as $verb)
                    <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-600">
                        <div class="mb-3">
                            <span class="text-xl font-bold text-white" style="font-family: 'Noto Sans JP', sans-serif;">{{ $verb['word'] }}</span>
                            <span class="text-gray-400 ml-2">{{ $verb['reading'] }}</span>
                            <div class="text-gray-300 text-sm mt-1">{{ $verb['meaning'] }}</div>
                        </div>
                        <div class="space-y-2">
                            @foreach(array_slice($verbForms, 0, 6) as $formKey => $form)
                                @if(isset($verb['conjugations'][$formKey]))
                                    <div class="text-sm">
                                        <span class="text-gray-400">{{ $form['name'] }}:</span>
                                        <span class="text-white font-semibold ml-2" style="font-family: 'Noto Sans JP', sans-serif;">
                                            {{ $verb['conjugations'][$formKey] }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Группа III (неправильные глаголы) -->
        <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h3 class="text-2xl font-bold text-yellow-400 mb-4">Группа III (Неправильные глаголы)</h3>
            <p class="text-gray-300 mb-4">
                Эти глаголы имеют особые правила спряжения. Основные неправильные глаголы: する (делать) и 来る (приходить).
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($exampleVerbs['group3'] as $verb)
                    <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-600">
                        <div class="mb-4">
                            <span class="text-2xl font-bold text-white" style="font-family: 'Noto Sans JP', sans-serif;">{{ $verb['word'] }}</span>
                            <span class="text-xl text-gray-400 ml-2">{{ $verb['reading'] }}</span>
                            <span class="text-gray-300 ml-2">— {{ $verb['meaning'] }}</span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-800 border-b border-gray-700">
                                        <th class="py-2 px-3 text-purple-300 font-semibold">Форма</th>
                                        <th class="py-2 px-3 text-white font-semibold" style="font-family: 'Noto Sans JP', sans-serif;">Спряжение</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($verbForms as $formKey => $form)
                                        @if(isset($verb['conjugations'][$formKey]))
                                            <tr class="border-b border-gray-700">
                                                <td class="py-2 px-3 text-gray-300">{{ $form['name'] }}</td>
                                                <td class="py-2 px-3 text-white font-semibold" style="font-family: 'Noto Sans JP', sans-serif;">
                                                    {{ $verb['conjugations'][$formKey] }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Прилагательные -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-purple-400 mb-6">Прилагательные</h2>
        
        <!-- I-прилагательные -->
        <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h3 class="text-2xl font-bold text-pink-400 mb-4">I-прилагательные (い-прилагательные)</h3>
            <p class="text-gray-300 mb-4">
                I-прилагательные оканчиваются на い (i) и могут спрягаться как глаголы, изменяя окончание.
                <br><span class="text-gray-400">Примеры: 高い (высокий), 大きい (большой), 面白い (интересный)</span>
            </p>
            
            <div class="space-y-6">
                @foreach($adjectiveForms as $formKey => $form)
                    <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-600 form-section" data-type="adjective" data-form="{{ $formKey }}">
                        <div class="flex items-start gap-4">
                            <label class="flex items-center cursor-pointer flex-shrink-0 mt-1">
                                <input type="checkbox" 
                                       class="form-checkbox h-5 w-5 text-purple-600 rounded focus:ring-purple-500 focus:ring-2" 
                                       data-type="adjective" 
                                       data-form="{{ $formKey }}"
                                       data-group="i_adjectives,na_adjectives">
                                <span class="ml-2 text-gray-300 font-semibold">Изучено</span>
                            </label>
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-pink-300 mb-2">{{ $form['name'] }}</h4>
                                <p class="text-gray-300 mb-3">{{ $form['description'] }}</p>
                                
                                <div class="bg-gray-800/50 rounded p-3 mb-3">
                                    <div class="text-sm text-gray-400 mb-1">Примеры использования:</div>
                                    <div class="text-gray-200" style="font-family: 'Noto Sans JP', sans-serif;">{{ $form['usage'] }}</div>
                                </div>
                                
                                <!-- Примеры спряжений -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @foreach($exampleAdjectives['i_adjectives'] as $adjective)
                                        @if(isset($adjective['conjugations'][$formKey]))
                                            <div class="bg-gray-800/30 rounded p-2 border border-gray-700">
                                                <div class="text-sm text-gray-400 mb-1">{{ $adjective['word'] }} ({{ $adjective['reading'] }})</div>
                                                <div class="text-lg font-semibold text-white" style="font-family: 'Noto Sans JP', sans-serif;">
                                                    {{ $adjective['conjugations'][$formKey] }}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $adjective['meaning'] }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Na-прилагательные -->
        <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h3 class="text-2xl font-bold text-cyan-400 mb-4">Na-прилагательные (な-прилагательные)</h3>
            <p class="text-gray-300 mb-4">
                Na-прилагательные не имеют окончания い и требуют частицы な при использовании с существительными. 
                Связываются со связкой だ/です.
                <br><span class="text-gray-400">Примеры: きれい (красивый), 静か (тихий), 元気 (здоровый)</span>
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($exampleAdjectives['na_adjectives'] as $adjective)
                    <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-600">
                        <div class="mb-3">
                            <span class="text-xl font-bold text-white" style="font-family: 'Noto Sans JP', sans-serif;">{{ $adjective['word'] }}</span>
                            <span class="text-gray-400 ml-2">{{ $adjective['reading'] }}</span>
                            <div class="text-gray-300 text-sm mt-1">{{ $adjective['meaning'] }}</div>
                        </div>
                        <div class="space-y-2">
                            @foreach($adjectiveForms as $formKey => $form)
                                @if(isset($adjective['conjugations'][$formKey]))
                                    <div class="text-sm">
                                        <span class="text-gray-400">{{ $form['name'] }}:</span>
                                        <span class="text-white font-semibold ml-2" style="font-family: 'Noto Sans JP', sans-serif;">
                                            {{ $adjective['conjugations'][$formKey] }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Полезные правила -->
    <div class="mb-12 bg-gradient-to-br from-purple-800/30 to-indigo-800/30 rounded-xl p-6 border border-purple-700/50">
        <h2 class="text-2xl font-bold text-purple-400 mb-4">💡 Полезные правила и советы</h2>
        
        <div class="space-y-4 text-gray-300">
            <div>
                <h4 class="font-bold text-purple-300 mb-2">Как определить группу глагола:</h4>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li><strong>Группа I (Godan):</strong> Если глагол оканчивается на слоги う, つ, る, ぬ, ぶ, む, く, ぐ, す (кроме глаголов группы II)</li>
                    <li><strong>Группа II (Ichidan):</strong> Если глагол оканчивается на る, который предшествует i или e (например, 食べる, 見る)</li>
                    <li><strong>Группа III:</strong> Только する и 来る — их нужно запомнить</li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-bold text-purple-300 mb-2">Специальные случаи:</h4>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li><strong>行く (идти):</strong> te форма → 行って, ta форма → 行った (не 行いて/行いた!)</li>
                    <li><strong>する:</strong> Потенциальная форма → できる (не しれる)</li>
                    <li>Некоторые глаголы могут выглядеть как группа II, но на самом деле группа I (например, 帰る, 切る)</li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-bold text-purple-300 mb-2">Прилагательные:</h4>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li><strong>I-прилагательные:</strong> Оканчиваются на い, могут стоять самостоятельно</li>
                    <li><strong>Na-прилагательные:</strong> Не оканчиваются на い, требуют связки だ/です</li>
                    <li><strong>Внимание:</strong> Не все слова, оканчивающиеся на い — это I-прилагательные! (например, きれい — это na-прилагательное)</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="text-center mt-8">
        <a href="{{ route('conjugation.index') }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition">
            Начать тренировку спряжений
        </a>
    </div>
</div>

<script>
// Сохранение выбранных форм в localStorage
const STORAGE_KEY = 'conjugation_selected_forms';

function saveSelection() {
    const selectedForms = {
        verb: [],
        adjective: []
    };
    
    document.querySelectorAll('input[type="checkbox"][data-type="verb"]:checked').forEach(cb => {
        selectedForms.verb.push(cb.dataset.form);
    });
    
    document.querySelectorAll('input[type="checkbox"][data-type="adjective"]:checked').forEach(cb => {
        selectedForms.adjective.push(cb.dataset.form);
    });
    
    localStorage.setItem(STORAGE_KEY, JSON.stringify(selectedForms));
    updatePracticeLink();
}

function loadSelection() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (!saved) return;
    
    try {
        const selectedForms = JSON.parse(saved);
        
        // Восстанавливаем выбор для глаголов
        selectedForms.verb.forEach(formKey => {
            const checkbox = document.querySelector(`input[data-type="verb"][data-form="${formKey}"]`);
            if (checkbox) checkbox.checked = true;
        });
        
        // Восстанавливаем выбор для прилагательных
        selectedForms.adjective.forEach(formKey => {
            const checkbox = document.querySelector(`input[data-type="adjective"][data-form="${formKey}"]`);
            if (checkbox) checkbox.checked = true;
        });
        
        updatePracticeLink();
    } catch (e) {
        console.error('Ошибка загрузки выбора:', e);
    }
}

function updatePracticeLink() {
    const practiceLink = document.getElementById('practice_link');
    const verbChecked = document.querySelectorAll('input[data-type="verb"]:checked').length > 0;
    const adjectiveChecked = document.querySelectorAll('input[data-type="adjective"]:checked').length > 0;
    
    if (verbChecked || adjectiveChecked) {
        practiceLink.classList.remove('hidden');
    } else {
        practiceLink.classList.add('hidden');
    }
}

// Обработчики событий
document.querySelectorAll('input[type="checkbox"][data-type]').forEach(checkbox => {
    checkbox.addEventListener('change', saveSelection);
});

// Сброс выбора
document.getElementById('clear_selection').addEventListener('click', function() {
    if (confirm('Сбросить весь выбор форм?')) {
        document.querySelectorAll('input[type="checkbox"][data-type]').forEach(cb => {
            cb.checked = false;
        });
        localStorage.removeItem(STORAGE_KEY);
        updatePracticeLink();
    }
});

// При переходе на страницу тренировки передаём выбранные формы через URL параметры
document.getElementById('practice_link')?.addEventListener('click', function(e) {
    e.preventDefault();
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
        const selectedForms = JSON.parse(saved);
        const params = new URLSearchParams();
        
        if (selectedForms.verb.length > 0) {
            params.append('verb_forms', selectedForms.verb.join(','));
        }
        if (selectedForms.adjective.length > 0) {
            params.append('adjective_forms', selectedForms.adjective.join(','));
        }
        
        const url = '{{ route('conjugation.index') }}' + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
    } else {
        window.location.href = '{{ route('conjugation.index') }}';
    }
});

// Загружаем сохранённый выбор при загрузке страницы
document.addEventListener('DOMContentLoaded', loadSelection);
</script>

<style>
/* Улучшаем отображение японского текста */
[style*="font-family: 'Noto Sans JP'"] {
    font-family: 'Noto Sans JP', sans-serif;
    letter-spacing: 0.05em;
}

.form-section {
    transition: background-color 0.2s;
}

.form-section:hover {
    background-color: rgba(17, 24, 39, 0.8);
}

/* Скролл для таблиц на мобильных устройствах */
@media (max-width: 768px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>
@endsection
