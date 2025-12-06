@extends('layouts.app')

@section('title', $story->title . ' - Obake')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('stories.index') }}" class="text-purple-400 hover:text-purple-300">← Назад к рассказам</a>
    </div>
    
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <span class="inline-block bg-purple-600 text-white px-3 py-1 rounded text-sm font-semibold">
                    {{ $story->level }}
                </span>
                @if($isRead)
                    <span class="inline-block bg-green-600 text-white px-3 py-1 rounded text-sm font-semibold ml-2">
                        ✓ Прочитано
                    </span>
                @endif
            </div>
            <div class="flex items-center space-x-4">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="furigana-toggle" class="rounded bg-gray-700 border-gray-600 text-purple-600">
                    <span class="ml-2 text-gray-300">Фуригана</span>
                </label>
                <button id="speak-story-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition text-sm flex items-center">
                    <span id="speak-story-icon">🔊</span>
                    <span id="speak-story-text" class="ml-2">Озвучить</span>
                </button>
                @if(!$isRead)
                    <button id="mark-as-read-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-sm">
                        ✓ Отметить как прочитанное
                    </button>
                @else
                    <span class="text-green-400 text-sm">✓ Прочитано</span>
                @endif
            </div>
        </div>
        
        <h1 class="text-3xl font-bold mb-4 text-purple-400">{{ $story->title }}</h1>
        
        @if($story->description)
            <p class="text-gray-400 mb-6">{{ $story->description }}</p>
        @endif
        
        <div id="story-content" class="text-lg leading-relaxed japanese-font mb-8" 
             data-content="{{ htmlspecialchars($story->content, ENT_QUOTES, 'UTF-8') }}"
             data-story-id="{{ $story->id }}"
             data-story-audio-path="{{ $story->audio_path }}"
             data-words="{{ json_encode($words->keyBy('id')->map(function($word) {
                 return [
                     'id' => $word->id,
                     'japanese' => $word->japanese_word,
                     'reading' => $word->reading,
                     'translation_ru' => $word->translation_ru,
                     'translation_en' => $word->translation_en,
                     'word_type' => $word->word_type,
                     'audio_path' => $word->audio_path,
                 ];
             })) }}"
             data-user-words="{{ json_encode($userWordIds) }}"
             data-word-progress="{{ json_encode($wordProgress) }}">
        </div>
        
        <!-- Аудио проигрыватель для рассказа (под текстом) -->
        <div id="story-audio-player" class="hidden mt-8 mb-6 bg-gradient-to-br from-gray-800 via-gray-800 to-gray-900 rounded-2xl p-5 shadow-2xl border border-gray-700/50 backdrop-blur-sm">
            <div class="flex items-center gap-5">
                <!-- Кнопка Play/Pause -->
                <button id="audio-play-pause-btn" class="group relative bg-gradient-to-br from-purple-600 via-purple-600 to-indigo-600 hover:from-purple-500 hover:via-purple-500 hover:to-indigo-500 active:from-purple-700 active:via-purple-700 active:to-indigo-700 text-white w-16 h-16 rounded-2xl flex items-center justify-center transition-all duration-300 flex-shrink-0 shadow-lg hover:shadow-purple-500/50 hover:scale-105 active:scale-95" title="Воспроизвести/Пауза">
                    <span id="audio-play-icon" class="text-2xl ml-0.5 transition-transform duration-200 group-hover:scale-110">▶</span>
                    <div class="absolute inset-0 rounded-2xl bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>
                
                <!-- Прогресс-бар и время -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-4">
                        <div class="flex-1 relative group">
                            <input type="range" id="audio-progress" min="0" max="100" value="0" 
                                   class="w-full h-2.5 bg-gray-700/60 rounded-full appearance-none cursor-pointer audio-slider hover:h-3 transition-all duration-200 outline-none focus:outline-none focus:ring-2 focus:ring-purple-500/50">
                            <div id="audio-progress-buffer" class="absolute top-0 left-0 h-2.5 bg-gray-600/40 rounded-full pointer-events-none transition-all duration-200" style="width: 0%"></div>
                        </div>
                        <span id="audio-time" class="text-gray-200 text-sm font-mono whitespace-nowrap min-w-[120px] text-right font-medium tracking-wide">0:00 / 0:00</span>
                    </div>
                </div>
                
                <!-- Кнопка Stop -->
                <button id="audio-stop-btn" class="group bg-gray-700/80 hover:bg-gray-600 active:bg-gray-500 text-gray-200 hover:text-white w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-200 flex-shrink-0 shadow-md hover:shadow-lg hover:scale-105 active:scale-95 border border-gray-600/50 hover:border-gray-500" title="Остановить">
                    <span class="text-xl transition-transform duration-200 group-hover:scale-110">⏹</span>
                </button>
            </div>
        </div>
        
        <div id="word-tooltip" class="fixed bg-gray-900 border border-gray-700 rounded-lg p-4 shadow-xl z-50 hidden">
            <div id="tooltip-content"></div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .japanese-font {
        font-family: 'Noto Sans JP', sans-serif;
        font-size: 1.25rem;
        line-height: 2;
    }
    /* Подсветка для слов не в словаре - без подсветки */
    
    /* Подсветка для слов в словаре, но не начатых (0 дней) */
    .word-highlight-not-started {
        background-color: rgba(156, 163, 175, 0.3); /* серый */
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .word-highlight-not-started:hover {
        background-color: rgba(156, 163, 175, 0.5);
    }
    
    /* Подсветка для начального уровня (0-3 дня) */
    .word-highlight-beginner {
        background-color: rgba(239, 68, 68, 0.3); /* красный */
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .word-highlight-beginner:hover {
        background-color: rgba(239, 68, 68, 0.5);
    }
    
    /* Подсветка для среднего уровня (4-7 дней) */
    .word-highlight-intermediate {
        background-color: rgba(251, 191, 36, 0.3); /* желтый */
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .word-highlight-intermediate:hover {
        background-color: rgba(251, 191, 36, 0.5);
    }
    
    /* Подсветка для продвинутого уровня (8-9 дней) */
    .word-highlight-advanced {
        background-color: rgba(59, 130, 246, 0.3); /* синий */
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .word-highlight-advanced:hover {
        background-color: rgba(59, 130, 246, 0.5);
    }
    
    /* Подсветка для изученных слов (10 дней) */
    .word-highlight-completed {
        background-color: rgba(34, 197, 94, 0.3); /* зеленый */
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .word-highlight-completed:hover {
        background-color: rgba(34, 197, 94, 0.5);
    }
    .furigana {
        font-size: 0.6em;
        position: relative;
        top: -0.5em;
    }
    .speaking {
        background-color: rgba(59, 130, 246, 0.3) !important;
        animation: pulse 1.5s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }
    
    /* Улучшенные стили для аудио слайдера */
    .audio-slider {
        -webkit-appearance: none;
        appearance: none;
        background: transparent;
        cursor: pointer;
        outline: none;
        position: relative;
    }
    
    .audio-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: linear-gradient(135deg, #a855f7, #6366f1);
        cursor: pointer;
        border: 3px solid #ffffff;
        box-shadow: 0 2px 8px rgba(168, 85, 247, 0.5), 0 0 0 0 rgba(168, 85, 247, 0.4);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 2;
    }
    
    .audio-slider::-webkit-slider-thumb:hover {
        transform: scale(1.3);
        box-shadow: 0 4px 12px rgba(168, 85, 247, 0.7), 0 0 0 4px rgba(168, 85, 247, 0.2);
        background: linear-gradient(135deg, #9333ea, #4f46e5);
    }
    
    .audio-slider::-webkit-slider-thumb:active {
        transform: scale(1.15);
        box-shadow: 0 2px 8px rgba(168, 85, 247, 0.6), 0 0 0 2px rgba(168, 85, 247, 0.3);
    }
    
    .audio-slider::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: linear-gradient(135deg, #a855f7, #6366f1);
        cursor: pointer;
        border: 3px solid #ffffff;
        box-shadow: 0 2px 8px rgba(168, 85, 247, 0.5);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .audio-slider::-moz-range-thumb:hover {
        transform: scale(1.3);
        box-shadow: 0 4px 12px rgba(168, 85, 247, 0.7);
        background: linear-gradient(135deg, #9333ea, #4f46e5);
    }
    
    .audio-slider::-webkit-slider-runnable-track {
        background: linear-gradient(to right, #a855f7 0%, #6366f1 100%);
        height: 10px;
        border-radius: 5px;
        position: relative;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    
    .audio-slider::-moz-range-track {
        background: linear-gradient(to right, #a855f7 0%, #6366f1 100%);
        height: 10px;
        border-radius: 5px;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    
    /* Стили для проигрывателя */
    #story-audio-player {
        display: block;
        animation: fadeInUp 0.4s ease-out;
    }
    
    #story-audio-player.hidden {
        display: none;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Анимация для кнопки play/pause */
    #audio-play-pause-btn {
        position: relative;
        overflow: hidden;
    }
    
    #audio-play-pause-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    #audio-play-pause-btn:active::before {
        width: 200px;
        height: 200px;
    }
    
    /* Улучшенные стили для слайдера при наведении */
    .audio-slider:hover {
        height: 12px;
    }
    
    .audio-slider:hover::-webkit-slider-runnable-track {
        height: 12px;
    }
    
    .audio-slider:hover::-moz-range-track {
        height: 12px;
    }
    
    /* Стили для буфера прогресса */
    #audio-progress-buffer {
        transition: width 0.1s linear;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const storyContent = document.getElementById('story-content');
    if (!storyContent) {
        console.error('Элемент story-content не найден');
        return;
    }
    
    let wordsData = {};
    let userWords = [];
    let rawContent = '';
    let wordProgress = {};
    
    try {
        wordsData = JSON.parse(storyContent.dataset.words || '{}');
        userWords = JSON.parse(storyContent.dataset.userWords || '[]');
        wordProgress = JSON.parse(storyContent.dataset.wordProgress || '{}');
        rawContent = storyContent.dataset.content || '';
    } catch (error) {
        console.error('Ошибка при парсинге данных:', error);
        return;
    }
    
    if (!rawContent) {
        console.error('Содержимое рассказа пустое');
        storyContent.innerHTML = '<p class="text-gray-400">Текст рассказа не найден</p>';
        return;
    }
    
    let furiganaEnabled = false;
    const furiganaToggle = document.getElementById('furigana-toggle');
    const tooltip = document.getElementById('word-tooltip');
    const tooltipContent = document.getElementById('tooltip-content');
    
    // Переменные для озвучки
    let isSpeaking = false;
    let currentUtterance = null;
    let speechSynthesis = window.speechSynthesis;
    let currentSpeakingElement = null;
    let selectedVoice = null;
    
    // Функция для выбора лучшего японского голоса
    function selectBestJapaneseVoice() {
        if (!speechSynthesis) return null;
        
        const voices = speechSynthesis.getVoices();
        if (voices.length === 0) return null;
        
        // Приоритет: Neural voices > Premium voices > Standard voices
        // Ищем голоса с "Neural" или "Premium" в названии
        let neuralVoice = voices.find(v => 
            v.lang.startsWith('ja') && 
            (v.name.includes('Neural') || v.name.includes('Premium') || v.name.includes('Enhanced'))
        );
        
        if (neuralVoice) return neuralVoice;
        
        // Ищем любой японский голос женского пола (обычно звучат лучше)
        let femaleVoice = voices.find(v => 
            v.lang.startsWith('ja') && 
            (v.name.includes('Female') || v.name.includes('女') || v.name.includes('F'))
        );
        
        if (femaleVoice) return femaleVoice;
        
        // Ищем любой японский голос
        let japaneseVoice = voices.find(v => v.lang.startsWith('ja'));
        
        return japaneseVoice || null;
    }
    
    // Загружаем голоса (может потребоваться время)
    function loadVoices() {
        const voices = speechSynthesis.getVoices();
        if (voices.length > 0) {
            selectedVoice = selectBestJapaneseVoice();
            if (selectedVoice) {
                console.log('Выбран голос:', selectedVoice.name, selectedVoice.lang);
            } else {
                console.warn('Японский голос не найден, будет использован голос по умолчанию');
            }
        }
    }
    
    // Загружаем голоса сразу и при их загрузке
    loadVoices();
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = loadVoices;
    }
    
    // Функция для определения класса подсветки на основе прогресса изучения
    function getHighlightClass(wordId, userWordIds, progress) {
        if (!userWordIds.includes(wordId)) {
            return ''; // Слово не в словаре
        }
        
        const wordProg = progress[wordId];
        if (!wordProg) {
            return 'word-highlight-not-started'; // Слово в словаре, но не начато изучение
        }
        
        if (wordProg.is_completed) {
            return 'word-highlight-completed'; // Изучено (10 дней)
        }
        
        const daysStudied = wordProg.days_studied || 0;
        if (daysStudied >= 8) {
            return 'word-highlight-advanced'; // 8-9 дней
        } else if (daysStudied >= 4) {
            return 'word-highlight-intermediate'; // 4-7 дней
        } else {
            return 'word-highlight-beginner'; // 0-3 дня
        }
    }
    
    // Функция для разметки текста
    function processStoryContent(content, words, userWordIds, showFurigana, progress) {
        if (!content) return '';
        if (!words || Object.keys(words).length === 0) return content;
        
        let processed = content;
        const wordPatterns = [];
        const processedPositions = new Set(); // Отслеживаем уже обработанные позиции
        
        // Создаем паттерны для всех слов, включая формы спряжения
        Object.values(words).forEach(word => {
            if (!word || !word.japanese) return; // Пропускаем некорректные слова
            
            const baseWord = word.japanese;
            const reading = word.reading || '';
            
            // Генерируем все возможные формы для глаголов и прилагательных
            // Базовая форма всегда использует полное чтение
            let wordForms = [{form: baseWord, reading: reading || ''}];
            
            // Проверяем тип слова (может быть "Глагол", "Глагол (う-глагол)", "verb" и т.д.)
            const wordTypeLower = (word.word_type || '').toLowerCase();
            if (wordTypeLower.includes('глагол') || wordTypeLower.includes('verb')) {
                try {
                    const forms = generateVerbForms(baseWord, reading || '');
                    if (forms && forms.length > 1) {
                        // Добавляем все формы глагола
                        wordForms = forms;
                    }
                    
                    // Если есть чтение и оно отличается от самого слова, генерируем формы и для хираганы
                    // Это позволяет находить слова, написанные хираганой, даже если в словаре они записаны кандзи
                    if (reading && reading !== baseWord) {
                        const readingForms = generateVerbForms(reading, reading);
                        // Добавляем формы на основе чтения (хираганы), если они еще не добавлены
                        readingForms.forEach(readingForm => {
                            const rfForm = typeof readingForm === 'string' ? readingForm : readingForm.form;
                            // Проверяем, нет ли уже такой формы
                            const exists = wordForms.some(f => {
                                const fForm = typeof f === 'string' ? f : f.form;
                                return fForm === rfForm;
                            });
                            if (!exists) {
                                wordForms.push(readingForm);
                            }
                        });
                    }
                } catch (error) {
                    console.error(`Ошибка при генерации форм глагола ${baseWord}:`, error);
                }
            } else if (wordTypeLower.includes('прилагательное') || wordTypeLower.includes('adjective') || 
                      wordTypeLower.includes('形容詞') || baseWord.endsWith('い')) {
                try {
                    const forms = generateAdjectiveForms(baseWord, reading || '', word.word_type);
                    if (forms && forms.length > 1) {
                        // Добавляем все формы прилагательного
                        wordForms = forms;
                    }
                    
                    // Если есть чтение и оно отличается от самого слова, генерируем формы и для хираганы
                    // Это позволяет находить слова, написанные хираганой, даже если в словаре они записаны кандзи
                    if (reading && reading !== baseWord) {
                        const readingForms = generateAdjectiveForms(reading, reading, word.word_type);
                        // Добавляем формы на основе чтения (хираганы), если они еще не добавлены
                        readingForms.forEach(readingForm => {
                            const rfForm = typeof readingForm === 'string' ? readingForm : readingForm.form;
                            // Проверяем, нет ли уже такой формы
                            const exists = wordForms.some(f => {
                                const fForm = typeof f === 'string' ? f : f.form;
                                return fForm === rfForm;
                            });
                            if (!exists) {
                                wordForms.push(readingForm);
                            }
                        });
                    }
                } catch (error) {
                    console.error(`Ошибка при генерации форм прилагательного ${baseWord}:`, error);
                }
            } else {
                // Для существительных и других слов: если есть чтение и оно отличается, добавляем его как альтернативу
                // Это позволяет находить слова, написанные хираганой, даже если в словаре они записаны кандзи
                if (reading && reading !== baseWord) {
                    wordForms.push({form: reading, reading: reading});
                }
            }
            
            // Создаем паттерны для всех форм
            // wordForms может быть массивом строк или объектов с формой и чтением
            wordForms.forEach(formData => {
                let form, formReading;
                if (typeof formData === 'string') {
                    // Если это просто строка (базовая форма)
                    form = formData;
                    formReading = reading; // Используем полное чтение
                } else {
                    // Если это объект с формой и чтением
                    form = formData.form;
                    formReading = formData.reading || reading;
                }
                
                const escapedWord = form.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                try {
                    const pattern = new RegExp(escapedWord, 'g');
                    wordPatterns.push({
                        pattern: pattern,
                        word: word,
                        length: form.length,
                        form: form,
                        reading: formReading // Сохраняем чтение для этой формы
                    });
                } catch (error) {
                    console.error(`Ошибка при создании паттерна для ${form}:`, error);
                }
            });
        });
        
        // Сортируем по длине (длиннее сначала), чтобы не заменять части слов
        wordPatterns.sort((a, b) => b.length - a.length);
        
        // Обрабатываем текст, избегая перекрытий
        const matches = [];
        wordPatterns.forEach(({pattern, word, form, reading: formReading}) => {
            // Сбрасываем регулярное выражение
            pattern.lastIndex = 0;
            let match;
            const allMatches = [];
            
            // Собираем все совпадения
            while ((match = pattern.exec(content)) !== null) {
                allMatches.push({
                    start: match.index,
                    end: match.index + match[0].length,
                    match: match[0],
                    word: word,
                    form: form,
                    reading: formReading // Сохраняем чтение для этой формы
                });
            }
            
            // Проверяем каждое совпадение на перекрытия
            allMatches.forEach(({start, end, match: matchText, word: wordObj, form: formText, reading: readingForForm}) => {
                const key = `${start}-${end}`;
                
                // Проверяем, не перекрывается ли с уже обработанным
                let overlaps = false;
                for (const pos of processedPositions) {
                    const [posStart, posEnd] = pos.split('-').map(Number);
                    if (!(end <= posStart || start >= posEnd)) {
                        overlaps = true;
                        break;
                    }
                }
                
                if (!overlaps) {
                    matches.push({
                        start: start,
                        end: end,
                        match: matchText,
                        word: wordObj,
                        form: formText,
                        reading: readingForForm, // Сохраняем чтение для этой формы
                        key: key
                    });
                    processedPositions.add(key);
                }
            });
        });
        
        // Сортируем совпадения по позиции (с конца, чтобы не сбить индексы)
        matches.sort((a, b) => b.start - a.start);
        
        // Заменяем совпадения
        matches.forEach(({start, end, match, word, reading: formReading, key}) => {
            const highlightClass = getHighlightClass(word.id, userWordIds, progress);
            
            // Используем чтение для конкретной формы, если оно есть
            const readingToUse = formReading || word.reading || '';
            
            let replacement = `<span class="word-item ${highlightClass}" 
                data-word-id="${word.id}"
                data-reading="${escapeHtml(readingToUse)}"
                data-translation-ru="${escapeHtml(word.translation_ru)}"
                data-translation-en="${escapeHtml(word.translation_en)}"
                data-japanese="${escapeHtml(word.japanese)}">${match}</span>`;
            
            // Добавляем фуригану если включена
            if (showFurigana && readingToUse) {
                replacement = `<ruby>${match}<rt class="furigana">${readingToUse}</rt></ruby>`;
                replacement = replacement.replace(match, `<span class="word-item ${highlightClass}" 
                    data-word-id="${word.id}"
                    data-reading="${escapeHtml(readingToUse)}"
                    data-translation-ru="${escapeHtml(word.translation_ru)}"
                    data-translation-en="${escapeHtml(word.translation_en)}"
                    data-japanese="${escapeHtml(word.japanese)}">${match}</span>`);
            }
            
            processed = processed.substring(0, start) + replacement + processed.substring(end);
            processedPositions.add(key);
        });
        
        // Убеждаемся, что возвращаем строку
        return processed || content;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Функция для генерации возможных форм спряжения японского глагола
    function generateVerbForms(baseWord, reading) {
        const forms = [{form: baseWord, reading: reading || ''}]; // Всегда включаем базовую форму
        
        if (!reading) return forms;
        
        // Определяем тип глагола по последнему слогу чтения
        const lastChar = reading[reading.length - 1];
        const isUVerb = lastChar === 'う' || lastChar === 'つ' || 
                       lastChar === 'ぬ' || lastChar === 'む' || lastChar === 'く' || 
                       lastChar === 'ぐ' || lastChar === 'す' || lastChar === 'ぶ';
        
        // Для глаголов на -る проверяем, не иру/еру ли это (исключения)
        const isRuVerb = reading.endsWith('る') && 
                        (reading.endsWith('いる') || reading.endsWith('える') ||
                         reading.endsWith('きる') || reading.endsWith('ぎる') ||
                         reading.endsWith('しる') || reading.endsWith('じる') ||
                         reading.endsWith('ちる') || reading.endsWith('ぢる') ||
                         reading.endsWith('にる') || reading.endsWith('ひる') ||
                         reading.endsWith('びる') || reading.endsWith('みる') ||
                         reading.endsWith('りる'));
        
        if (isRuVerb) {
            // Глаголы типа 見る, 食べる
            const readingStem = reading.slice(0, -1); // Убираем る из чтения
            const baseStem = baseWord.slice(0, -1); // Убираем последний символ из базовой формы
            
            // Масу-форма - используем чтение основы
            forms.push({form: baseStem + 'ます', reading: readingStem});
            forms.push({form: baseStem + 'ました', reading: readingStem});
            forms.push({form: baseStem + 'ません', reading: readingStem});
            forms.push({form: baseStem + 'ませんでした', reading: readingStem});
            
            // Те-форма - используем чтение основы
            forms.push({form: baseStem + 'て', reading: readingStem});
            forms.push({form: baseStem + 'で', reading: readingStem});
            
            // Прошедшее время - используем чтение основы
            forms.push({form: baseStem + 'た', reading: readingStem});
            forms.push({form: baseStem + 'だ', reading: readingStem});
            
            // Отрицательная форма - используем чтение основы
            forms.push({form: baseStem + 'ない', reading: readingStem});
            forms.push({form: baseStem + 'なかった', reading: readingStem});
        } else if (isUVerb && lastChar !== 'る') {
            // Глаголы типа 読む, 行く, 話す
            const readingStem = reading.slice(0, -1); // Чтение основы
            const baseStem = baseWord.slice(0, -1);
            
            // Определяем последний символ базовой формы
            const lastBaseChar = baseWord[baseWord.length - 1];
            
            // Масу-форма (и-форма) - меняем последний символ на и-форму
            let masuStem = '';
            let masuReadingStem = '';
            if (lastChar === 'う') {
                // う -> い (例: 買う -> 買い)
                masuStem = baseStem + 'い';
                masuReadingStem = readingStem + 'い';
            } else if (lastChar === 'つ') {
                // つ -> ち (例: 待つ -> 待ち)
                masuStem = baseStem + 'ち';
                masuReadingStem = readingStem + 'ち';
            } else if (lastChar === 'ぬ') {
                // ぬ -> に (例: 死ぬ -> 死に)
                masuStem = baseStem + 'に';
                masuReadingStem = readingStem + 'に';
            } else if (lastChar === 'む') {
                // む -> み (例: 読む -> 読み)
                masuStem = baseStem + 'み';
                masuReadingStem = readingStem + 'み';
            } else if (lastChar === 'く') {
                // く -> き (例: 書く -> 書き)
                masuStem = baseStem + 'き';
                masuReadingStem = readingStem + 'き';
            } else if (lastChar === 'ぐ') {
                // ぐ -> ぎ (例: 泳ぐ -> 泳ぎ)
                masuStem = baseStem + 'ぎ';
                masuReadingStem = readingStem + 'ぎ';
            } else if (lastChar === 'す') {
                // す -> し (例: 話す -> 話し)
                masuStem = baseStem + 'し';
                masuReadingStem = readingStem + 'し';
            } else if (lastChar === 'ぶ') {
                // ぶ -> び (例: 遊ぶ -> 遊び)
                masuStem = baseStem + 'び';
                masuReadingStem = readingStem + 'び';
            }
            
            if (masuStem) {
                forms.push({form: masuStem + 'ます', reading: masuReadingStem});
                forms.push({form: masuStem + 'ました', reading: masuReadingStem});
                forms.push({form: masuStem + 'ません', reading: masuReadingStem});
                forms.push({form: masuStem + 'ませんでした', reading: masuReadingStem});
            }
            
            // Те-форма
            let teStem = '';
            let teReadingStem = '';
            if (lastChar === 'う' || lastChar === 'つ' || lastChar === 'る') {
                // う, つ, る -> っ (例: 買う -> 買って, 待つ -> 待って)
                teStem = baseStem + 'っ';
                teReadingStem = readingStem + 'っ';
            } else if (lastChar === 'ぬ' || lastChar === 'む' || lastChar === 'ぶ') {
                // ぬ, む, ぶ -> ん (例: 死ぬ -> 死んで, 読む -> 読んで, 遊ぶ -> 遊んで)
                teStem = baseStem + 'ん';
                teReadingStem = readingStem + 'ん';
            } else if (lastChar === 'く') {
                // く -> い (例: 書く -> 書いて)
                teStem = baseStem + 'い';
                teReadingStem = readingStem + 'い';
            } else if (lastChar === 'ぐ') {
                // ぐ -> い (例: 泳ぐ -> 泳いで)
                teStem = baseStem + 'い';
                teReadingStem = readingStem + 'い';
            } else if (lastChar === 'す') {
                // す -> し (例: 話す -> 話して)
                teStem = baseStem + 'し';
                teReadingStem = readingStem + 'し';
            }
            
            if (teStem) {
                if (lastChar === 'ぬ' || lastChar === 'む' || lastChar === 'ぶ' || lastChar === 'ぐ') {
                    // Для ぬ, む, ぶ, ぐ используется で
                    forms.push({form: teStem + 'で', reading: teReadingStem});
                } else {
                    // Для остальных используется て
                    forms.push({form: teStem + 'て', reading: teReadingStem});
                }
            }
            
            // Прошедшее время (та-форма)
            let taStem = '';
            let taReadingStem = '';
            if (lastChar === 'う' || lastChar === 'つ' || lastChar === 'る') {
                // う, つ, る -> っ (例: 買う -> 買った, 待つ -> 待った)
                taStem = baseStem + 'っ';
                taReadingStem = readingStem + 'っ';
            } else if (lastChar === 'ぬ' || lastChar === 'む' || lastChar === 'ぶ') {
                // ぬ, む, ぶ -> ん (例: 死ぬ -> 死んだ, 読む -> 読んだ, 遊ぶ -> 遊んだ)
                taStem = baseStem + 'ん';
                taReadingStem = readingStem + 'ん';
            } else if (lastChar === 'く') {
                // く -> い (例: 書く -> 書いた)
                taStem = baseStem + 'い';
                taReadingStem = readingStem + 'い';
            } else if (lastChar === 'ぐ') {
                // ぐ -> い (例: 泳ぐ -> 泳いだ)
                taStem = baseStem + 'い';
                taReadingStem = readingStem + 'い';
            } else if (lastChar === 'す') {
                // す -> し (例: 話す -> 話した)
                taStem = baseStem + 'し';
                taReadingStem = readingStem + 'し';
            }
            
            if (taStem) {
                if (lastChar === 'ぬ' || lastChar === 'む' || lastChar === 'ぶ' || lastChar === 'ぐ') {
                    // Для ぬ, む, ぶ, ぐ используется だ
                    forms.push({form: taStem + 'だ', reading: taReadingStem});
                } else {
                    // Для остальных используется た
                    forms.push({form: taStem + 'た', reading: taReadingStem});
                }
            }
            
            // Отрицательная форма (а-форма + ない)
            let naiStem = '';
            let naiReadingStem = '';
            if (lastChar === 'う') {
                // う -> わ (例: 買う -> 買わない)
                naiStem = baseStem + 'わ';
                naiReadingStem = readingStem + 'わ';
            } else if (lastChar === 'つ') {
                // つ -> た (例: 待つ -> 待たない)
                naiStem = baseStem + 'た';
                naiReadingStem = readingStem + 'た';
            } else if (lastChar === 'ぬ') {
                // ぬ -> な (例: 死ぬ -> 死なない)
                naiStem = baseStem + 'な';
                naiReadingStem = readingStem + 'な';
            } else if (lastChar === 'む') {
                // む -> ま (例: 読む -> 読まない)
                naiStem = baseStem + 'ま';
                naiReadingStem = readingStem + 'ま';
            } else if (lastChar === 'く') {
                // く -> か (例: 書く -> 書かない)
                naiStem = baseStem + 'か';
                naiReadingStem = readingStem + 'か';
            } else if (lastChar === 'ぐ') {
                // ぐ -> が (例: 泳ぐ -> 泳がない)
                naiStem = baseStem + 'が';
                naiReadingStem = readingStem + 'が';
            } else if (lastChar === 'す') {
                // す -> さ (例: 話す -> 話さない)
                naiStem = baseStem + 'さ';
                naiReadingStem = readingStem + 'さ';
            } else if (lastChar === 'ぶ') {
                // ぶ -> ば (例: 遊ぶ -> 遊ばない)
                naiStem = baseStem + 'ば';
                naiReadingStem = readingStem + 'ば';
            }
            
            if (naiStem) {
                forms.push({form: naiStem + 'ない', reading: naiReadingStem});
                forms.push({form: naiStem + 'なかった', reading: naiReadingStem});
            }
        } else if (baseWord === 'する' || baseWord === '来る' || baseWord === 'くる') {
            // Нерегулярные глаголы
            if (baseWord === 'する') {
                forms.push(
                    {form: 'します', reading: 'し'},
                    {form: 'しました', reading: 'し'},
                    {form: 'しない', reading: 'し'},
                    {form: 'しなかった', reading: 'し'},
                    {form: 'して', reading: 'し'},
                    {form: 'した', reading: 'し'}
                );
            } else if (baseWord === '来る' || baseWord === 'くる') {
                forms.push(
                    {form: '来ます', reading: 'き'},
                    {form: '来ました', reading: 'き'},
                    {form: '来ない', reading: 'こ'},
                    {form: '来なかった', reading: 'こ'},
                    {form: '来て', reading: 'き'},
                    {form: '来た', reading: 'き'},
                    {form: 'きます', reading: 'き'},
                    {form: 'きました', reading: 'き'},
                    {form: 'こない', reading: 'こ'},
                    {form: 'こなかった', reading: 'こ'},
                    {form: 'きて', reading: 'き'},
                    {form: 'きた', reading: 'き'}
                );
            }
        }
        
        // Убираем дубликаты по форме
        const uniqueForms = [];
        const seenForms = new Set();
        forms.forEach(formData => {
            const form = typeof formData === 'string' ? formData : formData.form;
            if (!seenForms.has(form)) {
                seenForms.add(form);
                uniqueForms.push(formData);
            }
        });
        
        return uniqueForms;
    }
    
    // Функция для генерации возможных форм спряжения японских прилагательных
    function generateAdjectiveForms(baseWord, reading, wordType) {
        const forms = [{form: baseWord, reading: reading || ''}]; // Всегда включаем базовую форму
        
        if (!reading) return forms;
        
        const wordTypeLower = (wordType || '').toLowerCase();
        
        // Сначала проверяем явные типы
        let isIAdjective = wordTypeLower.includes('い-прилагательное') || 
                          wordTypeLower.includes('i-adjective') ||
                          wordTypeLower.includes('い形容詞');
        
        let isNaAdjective = wordTypeLower.includes('な-прилагательное') || 
                           wordTypeLower.includes('na-adjective') ||
                           wordTypeLower.includes('な形容詞');
        
        // Если тип не определен явно, пытаемся определить по форме слова
        if (!isIAdjective && !isNaAdjective) {
            if (wordTypeLower.includes('прилагательное') || wordTypeLower.includes('adjective')) {
                // Если указано просто "прилагательное", определяем по форме
                if (baseWord.endsWith('い')) {
                    // Слова, заканчивающиеся на い, обычно い-прилагательные
                    // Исключения: きれい (な-прилагательное), но это редкий случай
                    isIAdjective = true;
                } else {
                    // Остальные - な-прилагательные
                    isNaAdjective = true;
                }
            } else if (baseWord.endsWith('い')) {
                // Если тип не указан, но слово заканчивается на い, считаем い-прилагательным
                isIAdjective = true;
            }
        }
        
        if (isIAdjective && baseWord.endsWith('い')) {
            // い-прилагательные (например, 大きい, 小さい, 可愛い)
            const stem = baseWord.slice(0, -1); // Убираем い
            // Вычисляем чтение основы
            // Для большинства い-прилагательных чтение основы = чтение без последнего символа
            // Но для некоторых (например, 楽しい たのしい) нужно убрать 2 символа
            // Простое решение: убираем последний символ, если чтение длиннее слова
            let readingStem = reading;
            if (reading.length > baseWord.length) {
                // Если чтение длиннее, убираем разницу
                const diff = reading.length - baseWord.length;
                readingStem = reading.slice(0, -(1 + diff));
            } else {
                // Иначе просто убираем последний символ
                readingStem = reading.slice(0, -1);
            }
            
            // Вежливая форма - используем полное чтение
            forms.push({form: baseWord + 'です', reading: reading});
            
            // Прошедшее время - используем чтение основы
            forms.push({form: stem + 'かった', reading: readingStem});
            forms.push({form: stem + 'かったです', reading: readingStem});
            
            // Отрицательная форма - используем чтение основы
            forms.push({form: stem + 'くない', reading: readingStem});
            forms.push({form: stem + 'くないです', reading: readingStem});
            forms.push({form: stem + 'くありません', reading: readingStem});
            
            // Отрицательная прошедшая форма - используем чтение основы
            forms.push({form: stem + 'くなかった', reading: readingStem});
            forms.push({form: stem + 'くなかったです', reading: readingStem});
            forms.push({form: stem + 'くありませんでした', reading: readingStem});
            
            // Те-форма - используем чтение основы
            forms.push({form: stem + 'くて', reading: readingStem});
            
            // Наречие - используем чтение основы
            forms.push({form: stem + 'く', reading: readingStem});
            
            // Особые случаи для いい (хороший)
            if (baseWord === 'いい' || baseWord === '良い' || baseWord === 'よい') {
                forms.push(
                    {form: 'よい', reading: 'よい'},
                    {form: 'よかった', reading: 'よか'},
                    {form: 'よかったです', reading: 'よか'},
                    {form: 'よくない', reading: 'よか'},
                    {form: 'よくないです', reading: 'よか'},
                    {form: 'よくなかった', reading: 'よか'},
                    {form: 'よくなかったです', reading: 'よか'},
                    {form: 'よくて', reading: 'よか'},
                    {form: 'よく', reading: 'よか'}
                );
            }
        } else if (isNaAdjective) {
            // な-прилагательные (например, 静か, 元気, きれい)
            // Для な-прилагательных основа не меняется, используем полное чтение
            // Вежливая форма
            forms.push({form: baseWord + 'です', reading: reading});
            
            // Прошедшее время
            forms.push({form: baseWord + 'だった', reading: reading});
            forms.push({form: baseWord + 'でした', reading: reading});
            
            // Отрицательная форма
            forms.push({form: baseWord + 'じゃない', reading: reading});
            forms.push({form: baseWord + 'ではない', reading: reading});
            forms.push({form: baseWord + 'じゃないです', reading: reading});
            forms.push({form: baseWord + 'ではないです', reading: reading});
            forms.push({form: baseWord + 'ではありません', reading: reading});
            
            // Отрицательная прошедшая форма
            forms.push({form: baseWord + 'じゃなかった', reading: reading});
            forms.push({form: baseWord + 'ではなかった', reading: reading});
            forms.push({form: baseWord + 'じゃなかったです', reading: reading});
            forms.push({form: baseWord + 'ではなかったです', reading: reading});
            forms.push({form: baseWord + 'ではありませんでした', reading: reading});
            
            // Те-форма
            forms.push({form: baseWord + 'で', reading: reading});
            
            // Наречие (с に)
            forms.push({form: baseWord + 'に', reading: reading});
        }
        
        // Убираем дубликаты по форме
        const uniqueForms = [];
        const seenForms = new Set();
        forms.forEach(formData => {
            const form = typeof formData === 'string' ? formData : formData.form;
            if (!seenForms.has(form)) {
                seenForms.add(form);
                uniqueForms.push(formData);
            }
        });
        
        return uniqueForms;
    }
    
    // Таймер для скрытия tooltip
    let hideTooltipTimer = null;
    let isTooltipHovered = false;
    
    // Обработка наведения на сам tooltip (добавляем один раз)
    tooltip.addEventListener('mouseenter', function() {
        isTooltipHovered = true;
        // Отменяем таймер скрытия, если курсор на tooltip
        if (hideTooltipTimer) {
            clearTimeout(hideTooltipTimer);
            hideTooltipTimer = null;
        }
    });
    
    tooltip.addEventListener('mouseleave', function() {
        isTooltipHovered = false;
        // Скрываем tooltip при уходе курсора с него
        hideTooltip();
    });
    
    // Функция для скрытия tooltip
    function hideTooltip() {
        tooltip.classList.add('hidden');
        tooltip.style.visibility = '';
        tooltip.style.display = '';
        if (hideTooltipTimer) {
            clearTimeout(hideTooltipTimer);
            hideTooltipTimer = null;
        }
    }
    
    // Обработка переключения фуриганы
    furiganaToggle.addEventListener('change', function() {
        furiganaEnabled = this.checked;
        const content = rawContent;
        storyContent.innerHTML = processStoryContent(content, wordsData, userWords, furiganaEnabled, wordProgress);
        attachWordEvents();
    });
    
    // Обработка наведения на слова
    function attachWordEvents() {
        document.querySelectorAll('.word-item').forEach(item => {
            item.addEventListener('mouseenter', function(e) {
                // Сбрасываем состояние tooltip
                isTooltipHovered = false;
                // Отменяем таймер скрытия, если он есть
                if (hideTooltipTimer) {
                    clearTimeout(hideTooltipTimer);
                    hideTooltipTimer = null;
                }
                
                const wordId = this.dataset.wordId;
                const word = wordsData[wordId];
                
                if (word) {
                    tooltipContent.innerHTML = `
                        <div class="text-xl font-bold japanese-font mb-2">${word.japanese}</div>
                        ${word.reading ? `<div class="text-gray-400 mb-2">${word.reading}</div>` : ''}
                        <div class="text-gray-300 mb-1">${word.translation_ru}</div>
                        ${word.translation_en ? `<div class="text-gray-400 text-sm">${word.translation_en}</div>` : ''}
                        <div class="mt-3 flex gap-2 items-center">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm speak-word-btn flex items-center gap-1" data-word-id="${wordId}" data-word-text="${escapeHtml(word.japanese)}" data-word-audio-path="${word.audio_path || ''}" title="Озвучить слово">
                                <span class="word-play-icon">▶</span>
                                <span class="word-audio-time text-xs"></span>
                            </button>
                            <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm add-to-dict" data-word-id="${wordId}">
                                Добавить в словарь
                            </button>
                        </div>
                    `;
                    
                    // Показываем tooltip сначала невидимым, чтобы получить его размеры
                    tooltip.classList.remove('hidden');
                    tooltip.style.visibility = 'hidden';
                    tooltip.style.display = 'block';
                    
                    const rect = this.getBoundingClientRect();
                    const tooltipWidth = tooltip.offsetWidth;
                    const tooltipHeight = tooltip.offsetHeight;
                    
                    // Позиционируем tooltip над словом, ближе к нему
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltipWidth / 2) + 'px';
                    tooltip.style.top = (rect.top - tooltipHeight - 5) + 'px';
                    
                    // Делаем tooltip видимым
                    tooltip.style.visibility = 'visible';
                }
            });
            
            item.addEventListener('mouseleave', function() {
                // Устанавливаем задержку перед скрытием
                hideTooltipTimer = setTimeout(() => {
                    // Скрываем только если курсор не на tooltip
                    if (!isTooltipHovered) {
                        hideTooltip();
                    }
                }, 200);
            });
        });
    }
    
    // Обработка добавления в словарь (делегирование событий)
    document.addEventListener('click', async function(e) {
        if (e.target.classList.contains('add-to-dict') || e.target.closest('.add-to-dict')) {
            e.preventDefault();
            e.stopPropagation();
            
            const btn = e.target.classList.contains('add-to-dict') ? e.target : e.target.closest('.add-to-dict');
            const wordId = btn.dataset.wordId;
            
            // Проверяем, не добавлено ли уже слово
            if (userWords.includes(parseInt(wordId))) {
                return;
            }
            
            // Блокируем кнопку
            btn.disabled = true;
            btn.textContent = 'Добавление...';
            
            try {
                const response = await fetch('{{ route("dictionary.add") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ word_id: wordId })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Обновляем массив слов пользователя
                    if (!userWords.includes(wordId)) {
                        userWords.push(wordId);
                    }
                    
                    // Обновляем прогресс (слово только что добавлено, значит не начато)
                    wordProgress[wordId] = {
                        days_studied: 0,
                        is_completed: false
                    };
                    
                    // Добавляем подсветку всем экземплярам слова
                    document.querySelectorAll(`[data-word-id="${wordId}"]`).forEach(el => {
                        // Удаляем все возможные классы подсветки
                        el.classList.remove('word-highlight-not-started', 'word-highlight-beginner', 
                                          'word-highlight-intermediate', 'word-highlight-advanced', 
                                          'word-highlight-completed');
                        // Добавляем правильный класс
                        el.classList.add('word-highlight-not-started');
                    });
                    
                    // Обновляем кнопку
                    btn.textContent = 'В словаре';
                    btn.disabled = true;
                    btn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                    btn.classList.add('bg-gray-600', 'cursor-not-allowed');
                    
                    // Добавляем в массив пользовательских слов
                    if (!userWords.includes(parseInt(wordId))) {
                        userWords.push(parseInt(wordId));
                    }
                } else {
                    // Восстанавливаем кнопку при ошибке
                    btn.disabled = false;
                    btn.textContent = 'Добавить в словарь';
                    alert('Ошибка при добавлении слова в словарь');
                }
            } catch (error) {
                console.error('Error:', error);
                // Восстанавливаем кнопку при ошибке
                btn.disabled = false;
                btn.textContent = 'Добавить в словарь';
                alert('Ошибка при добавлении слова в словарь');
            }
        }
    });
    
    // Инициализация
    try {
        console.log('Начало обработки текста');
        console.log('rawContent length:', rawContent.length);
        console.log('wordsData keys:', Object.keys(wordsData).length);
        console.log('userWords:', userWords.length);
        
        const processedContent = processStoryContent(rawContent, wordsData, userWords, furiganaEnabled, wordProgress);
        console.log('Обработанный контент length:', processedContent.length);
        
        storyContent.innerHTML = processedContent;
        attachWordEvents();
    } catch (error) {
        console.error('Ошибка при обработке текста:', error);
        console.error('Stack trace:', error.stack);
        // В случае ошибки показываем исходный текст
        storyContent.innerHTML = rawContent || '<p class="text-purple-400">Ошибка при загрузке текста</p>';
    }
    
    // Функция для извлечения чистого текста из HTML
    function extractTextFromHTML(html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        return tempDiv.textContent || tempDiv.innerText || '';
    }
    
    // Функция для разбиения текста на предложения
    function splitIntoSentences(text) {
        // Разбиваем по японским знакам препинания: 。！？, а также по переносам строк
        // Сохраняем знаки препинания вместе с предложениями
        const sentences = [];
        const parts = text.split(/([。！？\n]+)/);
        
        for (let i = 0; i < parts.length; i++) {
            const part = parts[i].trim();
            if (!part) continue;
            
            // Если это знак препинания, добавляем к предыдущему предложению
            if (/^[。！？\n]+$/.test(part)) {
                if (sentences.length > 0) {
                    sentences[sentences.length - 1] += part;
                }
            } else {
                sentences.push(part);
            }
        }
        
        return sentences.filter(s => s.trim().length > 0);
    }
    
    // Функция для озвучки текста через браузерный API
    function speakTextBrowser(text) {
        return new Promise((resolve, reject) => {
            if (!speechSynthesis) {
                reject(new Error('Speech synthesis не поддерживается в этом браузере'));
                return;
            }
            
            // Обновляем список голосов перед каждой озвучкой
            loadVoices();
            
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'ja-JP';
            
            // Используем выбранный голос, если доступен
            if (selectedVoice) {
                utterance.voice = selectedVoice;
            }
            
            // Более естественные параметры для лучшего звучания
            utterance.rate = 0.95; // Немного медленнее для лучшей разборчивости
            utterance.pitch = 1.05; // Немного выше для более естественного звучания
            utterance.volume = 1.0;
            
            utterance.onend = () => {
                resolve();
            };
            
            utterance.onerror = (error) => {
                console.error('Ошибка озвучки:', error);
                reject(error);
            };
            
            currentUtterance = utterance;
            speechSynthesis.speak(utterance);
        });
    }
    
    // Функция для озвучки текста
    function speakText(text) {
        return speakTextBrowser(text);
    }
    
    // Функция для озвучки всего рассказа
    async function speakStory() {
        const storyId = storyContent.dataset.storyId;
        const audioPath = storyContent.dataset.storyAudioPath;
        
        // Если уже есть аудио, обрабатываем play/pause
        if (currentAudio) {
            if (currentAudio.paused) {
                await currentAudio.play();
            } else {
                currentAudio.pause();
            }
            updateAudioPlayer();
            return;
        }
        
        // Проверяем, есть ли сохраненное аудио
        if (audioPath) {
            // Используем сохраненное аудио
            try {
                // Используем специальный маршрут для аудио с поддержкой Range-запросов
                const audioUrl = '{{ url("/audio") }}/' + audioPath;
                currentAudio = new Audio(audioUrl);
                
                // Предзагружаем аудио для возможности перемотки
                currentAudio.preload = 'auto';
                
                // Настраиваем обработчики событий
                currentAudio.addEventListener('loadedmetadata', () => {
                    if (!isDraggingProgress) updateAudioPlayer();
                });
                currentAudio.addEventListener('canplay', () => {
                    // Аудио готово к воспроизведению
                    if (!isDraggingProgress) updateAudioPlayer();
                });
                currentAudio.addEventListener('canplaythrough', () => {
                    // Аудио полностью загружено и готово к перемотке
                    if (!isDraggingProgress) updateAudioPlayer();
                });
                currentAudio.addEventListener('timeupdate', () => {
                    // Не обновляем во время перетаскивания, чтобы не сбрасывать позицию
                    if (!isDraggingProgress && currentAudio) {
                        updateAudioPlayer();
                    }
                });
                currentAudio.addEventListener('seeking', () => {
                    // Во время перемотки не обновляем проигрыватель
                });
                currentAudio.addEventListener('seeked', () => {
                    // После завершения перемотки обновляем проигрыватель
                    if (!isDraggingProgress && currentAudio) {
                        updateAudioPlayer();
                    }
                });
                currentAudio.addEventListener('play', () => {
                    if (!isDraggingProgress && currentAudio) {
                        // Проверяем, что время не было сброшено на 0
                        // Если время 0, но мы не в начале, значит что-то пошло не так
                        if (currentAudio.currentTime === 0 && audioProgress && parseFloat(audioProgress.value) > 0) {
                            // Восстанавливаем время из прогресса
                            const progress = parseFloat(audioProgress.value) / 100;
                            currentAudio.currentTime = currentAudio.duration * progress;
                        }
                        updateAudioPlayer();
                    }
                });
                currentAudio.addEventListener('pause', () => {
                    if (!isDraggingProgress && currentAudio) {
                        updateAudioPlayer();
                    }
                });
                currentAudio.addEventListener('ended', () => {
                    isSpeaking = false;
                    currentAudio = null;
                    updateAudioPlayer();
                });
                currentAudio.addEventListener('error', (e) => {
                    console.error('Ошибка воспроизведения аудио:', e);
                    currentAudio = null;
                    updateAudioPlayer();
                });
                
                isSpeaking = true;
                
                // Ждем, пока аудио будет готово к воспроизведению
                await new Promise((resolve) => {
                    if (currentAudio.readyState >= 3) { // HAVE_FUTURE_DATA
                        resolve();
                    } else {
                        currentAudio.addEventListener('canplay', resolve, { once: true });
                    }
                });
                
                await currentAudio.play();
                updateAudioPlayer();
                return;
            } catch (error) {
                console.error('Ошибка воспроизведения аудио:', error);
                currentAudio = null;
                updateAudioPlayer();
            }
        }
        
        // Если аудио нет, используем браузерную озвучку
        await speakStoryBrowser();
    }
    
    let currentAudio = null;
    
    // Функция для браузерной озвучки (старая логика)
    async function speakStoryBrowser() {
        // Проверяем поддержку
        if (!speechSynthesis) {
            alert('Ваш браузер не поддерживает озвучку текста');
            isSpeaking = false;
            updateAudioPlayer();
            return;
        }
        
        // Получаем весь текст из story-content
        const storyText = storyContent.innerText || storyContent.textContent || '';
        
        if (!storyText.trim()) {
            alert('Текст для озвучки не найден');
            isSpeaking = false;
            updateAudioPlayer();
            return;
        }
        
        // Показываем проигрыватель при браузерной озвучке
        isSpeaking = true;
        updateAudioPlayer();
        
        // Разбиваем на предложения
        const sentences = splitIntoSentences(storyText);
        
        // Озвучиваем по предложениям
        try {
            // Получаем все текстовые элементы
            const allElements = Array.from(storyContent.querySelectorAll('.word-item'));
            
            // Собираем весь текст из элементов для точного сопоставления
            let accumulatedText = '';
            const elementTextMap = [];
            
            allElements.forEach((el, index) => {
                const elText = el.textContent || el.innerText || '';
                const startPos = accumulatedText.length;
                accumulatedText += elText;
                elementTextMap.push({
                    element: el,
                    start: startPos,
                    end: accumulatedText.length,
                    text: elText
                });
            });
            
            for (let i = 0; i < sentences.length; i++) {
                if (!isSpeaking) break; // Проверяем, не была ли остановлена озвучка
                
                const sentence = sentences[i].trim();
                if (!sentence || sentence.length < 1) continue;
                
                // Находим позицию предложения в накопленном тексте
                const sentenceStart = accumulatedText.indexOf(sentence);
                
                // Убираем подсветку с предыдущих элементов
                if (currentSpeakingElement) {
                    if (currentSpeakingElement.classList) {
                        currentSpeakingElement.classList.remove('speaking');
                    }
                }
                
                // Находим элементы, которые попадают в диапазон этого предложения
                let foundElements = [];
                
                if (sentenceStart !== -1) {
                    const sentenceEnd = sentenceStart + sentence.length;
                    
                    elementTextMap.forEach(item => {
                        // Проверяем, пересекается ли элемент с предложением
                        if (item.start < sentenceEnd && item.end > sentenceStart) {
                            foundElements.push(item.element);
                        }
                    });
                } else {
                    // Если не нашли точное совпадение, ищем по первым символам
                    const firstChars = sentence.substring(0, Math.min(10, sentence.length));
                    elementTextMap.forEach(item => {
                        if (item.text.includes(firstChars)) {
                            foundElements.push(item.element);
                        }
                    });
                }
                
                // Подсвечиваем найденные элементы
                if (foundElements.length > 0) {
                    foundElements.forEach(el => {
                        el.classList.add('speaking');
                    });
                    currentSpeakingElement = foundElements[0];
                    
                    // Прокручиваем к первому элементу
                    foundElements[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                // Озвучиваем предложение
                await speakText(sentence);
                
                // Убираем подсветку после озвучки
                foundElements.forEach(el => {
                    el.classList.remove('speaking');
                });
            }
        } catch (error) {
            console.error('Ошибка при озвучке:', error);
            alert('Произошла ошибка при озвучке текста');
        } finally {
            // Убираем подсветку
            if (currentSpeakingElement) {
                currentSpeakingElement.classList.remove('speaking');
                currentSpeakingElement = null;
            }
            isSpeaking = false;
            updateAudioPlayer();
        }
    }
    
    // Элементы аудио проигрывателя
    const audioPlayer = document.getElementById('story-audio-player');
    const audioPlayPauseBtn = document.getElementById('audio-play-pause-btn');
    const audioPlayIcon = document.getElementById('audio-play-icon');
    const audioProgress = document.getElementById('audio-progress');
    const audioTime = document.getElementById('audio-time');
    const audioStopBtn = document.getElementById('audio-stop-btn');
    
    // Функция для форматирования времени
    function formatTime(seconds) {
        if (isNaN(seconds) || !isFinite(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Функция для обновления проигрывателя
    function updateAudioPlayer() {
        if (!audioPlayer) return;
        
        // Не обновляем прогресс во время перетаскивания
        if (isDraggingProgress && currentAudio) {
            return;
        }
        
        // Показываем проигрыватель, если есть аудио или идет озвучка
        if (currentAudio || isSpeaking) {
            audioPlayer.classList.remove('hidden');
            
            // Если есть аудио, обновляем время и прогресс
            if (currentAudio) {
                const current = currentAudio.currentTime || 0;
                const duration = currentAudio.duration || 0;
                
                if (audioTime) {
                    if (duration > 0) {
                        audioTime.textContent = `${formatTime(current)} / ${formatTime(duration)}`;
                    } else {
                        audioTime.textContent = `${formatTime(current)} / --:--`;
                    }
                }
                
                if (audioProgress && !isDraggingProgress) {
                    const progress = duration > 0 ? (current / duration) * 100 : 0;
                    audioProgress.value = progress;
                }
                
                // Обновляем буфер прогресса
                const audioProgressBuffer = document.getElementById('audio-progress-buffer');
                if (audioProgressBuffer && currentAudio.buffered && currentAudio.buffered.length > 0) {
                    const bufferedEnd = currentAudio.buffered.end(currentAudio.buffered.length - 1);
                    const bufferedPercent = duration > 0 ? (bufferedEnd / duration) * 100 : 0;
                    audioProgressBuffer.style.width = bufferedPercent + '%';
                } else if (audioProgressBuffer) {
                    audioProgressBuffer.style.width = '0%';
                }
                
                if (audioPlayIcon) {
                    if (currentAudio.paused) {
                        audioPlayIcon.textContent = '▶';
                    } else {
                        audioPlayIcon.textContent = '⏸';
                    }
                }
            } else {
                // Если идет браузерная озвучка, показываем только время
                if (audioTime) {
                    audioTime.textContent = 'Озвучка...';
                }
                if (audioProgress) {
                    audioProgress.value = 0;
                }
                if (audioPlayIcon) {
                    audioPlayIcon.textContent = '⏸';
                }
            }
        } else {
            // Скрываем проигрыватель, если нет аудио и не идет озвучка
            audioPlayer.classList.add('hidden');
            if (audioTime) {
                audioTime.textContent = '0:00 / 0:00';
            }
            if (audioProgress) {
                audioProgress.value = 0;
            }
            if (audioPlayIcon) {
                audioPlayIcon.textContent = '▶';
            }
        }
        
        updateSpeakButton();
    }
    
    // Обновление состояния кнопки "Озвучить" при изменении состояния аудио
    function updateSpeakButton() {
        const speakStoryBtn = document.getElementById('speak-story-btn');
        const speakStoryIcon = document.getElementById('speak-story-icon');
        const speakStoryText = document.getElementById('speak-story-text');
        
        if (!speakStoryBtn || !speakStoryIcon || !speakStoryText) return;
        
        if (isSpeaking && currentAudio && !currentAudio.paused) {
            speakStoryIcon.textContent = '⏸️';
            speakStoryText.textContent = 'Пауза';
            speakStoryBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            speakStoryBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        } else {
            speakStoryIcon.textContent = '🔊';
            speakStoryText.textContent = 'Озвучить';
            speakStoryBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
            speakStoryBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }
    }
    
    // Обработка кнопки "Озвучить" в верхней части
    const speakStoryBtn = document.getElementById('speak-story-btn');
    
    if (speakStoryBtn) {
        speakStoryBtn.addEventListener('click', async function() {
            await speakStory();
        });
    }
    
    // Обработка кнопки play/pause в проигрывателе
    if (audioPlayPauseBtn) {
        audioPlayPauseBtn.addEventListener('click', async function() {
            if (!currentAudio) {
                await speakStory();
                return;
            }
            
            if (currentAudio.paused) {
                await currentAudio.play();
            } else {
                currentAudio.pause();
            }
            updateAudioPlayer();
        });
    }
    
    // Обработка кнопки остановки
    if (audioStopBtn) {
        audioStopBtn.addEventListener('click', function() {
            if (currentAudio) {
                currentAudio.pause();
                currentAudio.currentTime = 0;
                isSpeaking = false;
                currentAudio = null;
                updateAudioPlayer();
            }
            // Останавливаем браузерную озвучку
            if (speechSynthesis) {
                speechSynthesis.cancel();
            }
        });
    }
    
    // Переменные для отслеживания перетаскивания
    let isDraggingProgress = false;
    let wasPlayingBeforeDrag = false;
    
    // Обработка изменения прогресса
    if (audioProgress) {
        // Функция для установки времени воспроизведения
        function setAudioTime(progress) {
            if (!currentAudio || !currentAudio.duration) return;
            const newTime = currentAudio.duration * (progress / 100);
            currentAudio.currentTime = newTime;
            
            // Обновляем время вручную для мгновенной обратной связи
            if (audioTime) {
                const duration = currentAudio.duration || 0;
                audioTime.textContent = `${formatTime(newTime)} / ${formatTime(duration)}`;
            }
        }
        
        // Функция для завершения перетаскивания
        function finishDragging() {
            if (!currentAudio || !isDraggingProgress) return;
            
            const progress = parseFloat(audioProgress.value);
            const newTime = currentAudio.duration * (progress / 100);
            
            // Сохраняем состояние перед сбросом флагов
            const shouldResume = wasPlayingBeforeDrag;
            
            // Сбрасываем флаг перетаскивания ПЕРЕД установкой времени
            isDraggingProgress = false;
            wasPlayingBeforeDrag = false;
            
            // Обновляем время и прогресс вручную ПЕРЕД установкой времени
            if (audioTime) {
                const duration = currentAudio.duration || 0;
                audioTime.textContent = `${formatTime(newTime)} / ${formatTime(duration)}`;
            }
            if (audioProgress) {
                audioProgress.value = progress;
            }
            
            // Функция для установки времени с проверкой готовности аудио
            const setTimeAndPlay = (targetTime, shouldPlay) => {
                if (!currentAudio) return;
                
                console.log('setTimeAndPlay: targetTime =', targetTime, 'readyState =', currentAudio.readyState, 'duration =', currentAudio.duration);
                
                // Проверяем, что аудио готово к перемотке (readyState >= 2 означает HAVE_CURRENT_DATA)
                // readyState: 0=HAVE_NOTHING, 1=HAVE_METADATA, 2=HAVE_CURRENT_DATA, 3=HAVE_FUTURE_DATA, 4=HAVE_ENOUGH_DATA
                if (currentAudio.readyState < 2) {
                    console.log('Аудио не готово (readyState < 2), ждем canplay/loadeddata');
                    // Аудио еще не готово, ждем
                    const onCanPlay = () => {
                        if (currentAudio) {
                            console.log('Аудио готово, readyState =', currentAudio.readyState);
                            currentAudio.removeEventListener('canplay', onCanPlay);
                            currentAudio.removeEventListener('loadeddata', onCanPlay);
                            setTimeAndPlay(targetTime, shouldPlay);
                        }
                    };
                    currentAudio.addEventListener('canplay', onCanPlay, { once: true });
                    currentAudio.addEventListener('loadeddata', onCanPlay, { once: true });
                    // Принудительно загружаем данные
                    currentAudio.load();
                    return;
                }
                
                // Устанавливаем время
                try {
                    console.log('Устанавливаем currentTime =', targetTime, 'текущее значение =', currentAudio.currentTime);
                    currentAudio.currentTime = targetTime;
                    console.log('После установки currentTime =', currentAudio.currentTime);
                } catch (e) {
                    console.error('Ошибка установки времени:', e);
                    // Если не удалось установить время, ждем готовности
                    const onCanPlay = () => {
                        if (currentAudio) {
                            try {
                                console.log('Повторная попытка установки времени после canplay');
                                currentAudio.currentTime = targetTime;
                            } catch (e2) {
                                console.error('Ошибка установки времени после canplay:', e2);
                            }
                        }
                    };
                    currentAudio.addEventListener('canplay', onCanPlay, { once: true });
                    return;
                }
                
                // Определяем, является ли браузер Chrome
                const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
                
                // Ждем события seeked для подтверждения установки времени
                const onSeeked = () => {
                    if (!currentAudio) return;
                    
                    // Проверяем, что время установлено правильно
                    const actualTime = currentAudio.currentTime;
                    if (Math.abs(actualTime - targetTime) < 1.0) {
                        // Время установлено правильно
                        if (shouldPlay && currentAudio.paused) {
                            // Для Chrome нужна особая обработка
                            if (isChrome) {
                                // В Chrome устанавливаем время еще раз прямо перед play
                                currentAudio.currentTime = targetTime;
                                
                                // Ждем еще один seeked для Chrome
                                const onSeekedChrome = () => {
                                    if (!currentAudio) return;
                                    
                                    // Проверяем время еще раз
                                    const checkTime = currentAudio.currentTime;
                                    if (Math.abs(checkTime - targetTime) < 1.0) {
                                        // Время установлено, запускаем
                                        currentAudio.play().catch(e => console.error('Ошибка воспроизведения:', e));
                                        
                                        // Добавляем защиту от сброса времени в Chrome
                                        const onPlayChrome = () => {
                                            // Проверяем время сразу после play
                                            setTimeout(() => {
                                                if (currentAudio && currentAudio.currentTime === 0 && targetTime > 0) {
                                                    // Время сбросилось в Chrome, восстанавливаем
                                                    currentAudio.pause();
                                                    currentAudio.currentTime = targetTime;
                                                    const onSeekedRetry = () => {
                                                        currentAudio.play().catch(e => console.error('Ошибка воспроизведения:', e));
                                                    };
                                                    currentAudio.addEventListener('seeked', onSeekedRetry, { once: true });
                                                }
                                            }, 20);
                                            
                                            // Проверяем еще раз через 100мс
                                            setTimeout(() => {
                                                if (currentAudio && currentAudio.currentTime === 0 && targetTime > 0) {
                                                    // Время все еще 0, восстанавливаем и перезапускаем
                                                    currentAudio.pause();
                                                    currentAudio.currentTime = targetTime;
                                                    const onSeekedRetry = () => {
                                                        currentAudio.play().catch(e => console.error('Ошибка воспроизведения:', e));
                                                    };
                                                    currentAudio.addEventListener('seeked', onSeekedRetry, { once: true });
                                                }
                                            }, 100);
                                        };
                                        currentAudio.addEventListener('play', onPlayChrome, { once: true });
                                    }
                                };
                                currentAudio.addEventListener('seeked', onSeekedChrome, { once: true });
                            } else {
                                // Для других браузеров (Firefox и т.д.)
                                currentAudio.play().catch(e => console.error('Ошибка воспроизведения:', e));
                            }
                        }
                    } else {
                        // Время не установилось, пытаемся еще раз
                        console.log('Время не установилось. Повторная попытка. Ожидалось:', targetTime, 'Получено:', actualTime);
                        setTimeout(() => {
                            if (currentAudio) {
                                try {
                                    currentAudio.currentTime = targetTime;
                                } catch (e) {
                                    console.error('Ошибка повторной установки времени:', e);
                                }
                            }
                        }, 50);
                    }
                };
                
                currentAudio.addEventListener('seeked', onSeeked, { once: true });
                
                // Fallback - если seeked не сработает
                setTimeout(() => {
                    if (currentAudio && Math.abs(currentAudio.currentTime - targetTime) > 1.0) {
                        // Время все еще не установлено, пытаемся еще раз
                        try {
                            currentAudio.currentTime = targetTime;
                        } catch (e) {
                            console.error('Ошибка установки времени в fallback:', e);
                        }
                    }
                }, 100);
            };
            
            // Устанавливаем время и возобновляем воспроизведение, если нужно
            setTimeAndPlay(newTime, shouldResume);
            
            // Обновляем проигрыватель после небольшой задержки
            setTimeout(() => {
                updateAudioPlayer();
            }, 100);
        }
        
        audioProgress.addEventListener('mousedown', (e) => {
            if (currentAudio) {
                isDraggingProgress = true;
                wasPlayingBeforeDrag = !currentAudio.paused;
                // Паузируем во время перетаскивания для плавности
                if (wasPlayingBeforeDrag) {
                    currentAudio.pause();
                }
                // Устанавливаем время сразу при клике
                const rect = audioProgress.getBoundingClientRect();
                const percent = ((e.clientX - rect.left) / rect.width) * 100;
                setAudioTime(Math.max(0, Math.min(100, percent)));
            }
        });
        
        audioProgress.addEventListener('input', () => {
            if (currentAudio && isDraggingProgress) {
                const progress = parseFloat(audioProgress.value);
                setAudioTime(progress);
            }
        });
        
        audioProgress.addEventListener('mouseup', () => {
            finishDragging();
        });
        
        // Обработка случая, когда мышь уходит за пределы слайдера во время перетаскивания
        document.addEventListener('mouseup', () => {
            if (isDraggingProgress) {
                finishDragging();
            }
        });
        
        // Обработка для простого клика (без перетаскивания)
        audioProgress.addEventListener('change', () => {
            if (currentAudio && !isDraggingProgress) {
                const progress = parseFloat(audioProgress.value);
                setAudioTime(progress);
                updateAudioPlayer();
            }
        });
        
        // Обработка для touch устройств
        audioProgress.addEventListener('touchstart', (e) => {
            if (currentAudio) {
                isDraggingProgress = true;
                wasPlayingBeforeDrag = !currentAudio.paused;
                if (wasPlayingBeforeDrag) {
                    currentAudio.pause();
                }
                // Устанавливаем время сразу при касании
                const rect = audioProgress.getBoundingClientRect();
                const touch = e.touches[0];
                const percent = ((touch.clientX - rect.left) / rect.width) * 100;
                setAudioTime(Math.max(0, Math.min(100, percent)));
            }
        });
        
        audioProgress.addEventListener('touchmove', (e) => {
            if (currentAudio && isDraggingProgress) {
                e.preventDefault();
                const rect = audioProgress.getBoundingClientRect();
                const touch = e.touches[0];
                const percent = ((touch.clientX - rect.left) / rect.width) * 100;
                const progress = Math.max(0, Math.min(100, percent));
                audioProgress.value = progress;
                setAudioTime(progress);
            }
        });
        
        audioProgress.addEventListener('touchend', () => {
            finishDragging();
        });
    }
    
    // Останавливаем озвучку при уходе со страницы
    window.addEventListener('beforeunload', () => {
        if (speechSynthesis && isSpeaking) {
            speechSynthesis.cancel();
        }
        if (currentAudio) {
            currentAudio.pause();
        }
    });
    
    // Инициализация проигрывателя при загрузке страницы
    updateAudioPlayer();
    
    // Переменные для проигрывателя слов
    let currentWordAudio = null;
    let currentWordButton = null;
    
    // Функция для озвучки слова
    async function speakWord(wordId, wordText, buttonElement) {
        const word = wordsData[wordId];
        if (!word) return;
        
        // Если уже играет это же слово, ставим на паузу/возобновляем
        if (currentWordAudio && currentWordButton === buttonElement) {
            if (currentWordAudio.paused) {
                await currentWordAudio.play();
                const icon = buttonElement.querySelector('.word-play-icon');
                if (icon) icon.textContent = '⏸';
            } else {
                currentWordAudio.pause();
                const icon = buttonElement.querySelector('.word-play-icon');
                if (icon) icon.textContent = '▶';
            }
            return;
        }
        
        // Останавливаем предыдущее аудио
        if (currentWordAudio) {
            currentWordAudio.pause();
            currentWordAudio = null;
            if (currentWordButton) {
                const icon = currentWordButton.querySelector('.word-play-icon');
                if (icon) icon.textContent = '▶';
                const time = currentWordButton.querySelector('.word-audio-time');
                if (time) time.textContent = '';
            }
        }
        
        // Проверяем, есть ли сохраненное аудио
        if (word.audio_path) {
            try {
                // Используем специальный маршрут для аудио с поддержкой Range-запросов
                const audioUrl = '{{ url("/audio") }}/' + word.audio_path;
                currentWordAudio = new Audio(audioUrl);
                currentWordButton = buttonElement;
                
                // Обновляем иконку
                const icon = buttonElement.querySelector('.word-play-icon');
                if (icon) icon.textContent = '⏸';
                
                // Обработчики событий
                currentWordAudio.addEventListener('timeupdate', () => {
                    if (currentWordAudio && buttonElement) {
                        const current = currentWordAudio.currentTime || 0;
                        const duration = currentWordAudio.duration || 0;
                        const timeEl = buttonElement.querySelector('.word-audio-time');
                        if (timeEl && duration > 0) {
                            timeEl.textContent = formatTime(current);
                        }
                    }
                });
                
                currentWordAudio.addEventListener('ended', () => {
                    if (buttonElement) {
                        const icon = buttonElement.querySelector('.word-play-icon');
                        if (icon) icon.textContent = '▶';
                        const time = buttonElement.querySelector('.word-audio-time');
                        if (time) time.textContent = '';
                    }
                    currentWordAudio = null;
                    currentWordButton = null;
                });
                
                currentWordAudio.addEventListener('pause', () => {
                    if (buttonElement) {
                        const icon = buttonElement.querySelector('.word-play-icon');
                        if (icon) icon.textContent = '▶';
                    }
                });
                
                await currentWordAudio.play();
                return;
            } catch (error) {
                console.error('Ошибка воспроизведения аудио:', error);
            }
        }
        
        // Если аудио нет, используем браузерную озвучку
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(wordText || word.japanese);
            utterance.lang = 'ja-JP';
            if (selectedVoice) {
                utterance.voice = selectedVoice;
            }
            speechSynthesis.speak(utterance);
        } else {
            alert('Озвучка не поддерживается в вашем браузере');
        }
    }
    
    // Обработка кнопок озвучки слов (делегирование событий)
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.speak-word-btn');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            const wordId = parseInt(btn.dataset.wordId);
            const wordText = btn.dataset.wordText;
            await speakWord(wordId, wordText, btn);
        }
    });
    
    // Обработка кнопки "Отметить как прочитанное"
    const markAsReadBtn = document.getElementById('mark-as-read-btn');
    if (markAsReadBtn) {
        markAsReadBtn.addEventListener('click', async function() {
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Отмечаю...';
            
            try {
                const response = await fetch('{{ route("stories.mark-as-read", $story->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Обновляем UI
                    btn.remove();
                    const statusDiv = document.querySelector('.mb-4.flex.justify-between.items-center > div:first-child');
                    if (statusDiv) {
                        const readBadge = document.createElement('span');
                        readBadge.className = 'inline-block bg-green-600 text-white px-3 py-1 rounded text-sm font-semibold ml-2';
                        readBadge.textContent = '✓ Прочитано';
                        statusDiv.appendChild(readBadge);
                    }
                    
                    // Показываем сообщение об успехе
                    const successMsg = document.createElement('div');
                    successMsg.className = 'bg-green-600 text-white px-4 py-2 rounded-lg mb-4';
                    successMsg.textContent = '✓ Рассказ отмечен как прочитанный!';
                    storyContent.parentElement.insertBefore(successMsg, storyContent);
                    
                    // Убираем сообщение через 3 секунды
                    setTimeout(() => {
                        successMsg.remove();
                    }, 3000);
                } else {
                    alert('Ошибка при отметке рассказа');
                    btn.disabled = false;
                    btn.textContent = '✓ Отметить как прочитанное';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ошибка при отметке рассказа');
                btn.disabled = false;
                btn.textContent = '✓ Отметить как прочитанное';
            }
        });
    }
});
</script>
@endpush
@endsection
