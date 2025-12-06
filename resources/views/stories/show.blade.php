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
             data-words="{{ json_encode($words->keyBy('id')->map(function($word) {
                 return [
                     'id' => $word->id,
                     'japanese' => $word->japanese_word,
                     'reading' => $word->reading,
                     'translation_ru' => $word->translation_ru,
                     'translation_en' => $word->translation_en,
                     'word_type' => $word->word_type,
                 ];
             })) }}"
             data-user-words="{{ json_encode($userWordIds) }}">
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
    .word-highlight {
        background-color: rgba(239, 68, 68, 0.3);
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .word-highlight:hover {
        background-color: rgba(239, 68, 68, 0.5);
    }
    .furigana {
        font-size: 0.6em;
        position: relative;
        top: -0.5em;
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
    
    try {
        wordsData = JSON.parse(storyContent.dataset.words || '{}');
        userWords = JSON.parse(storyContent.dataset.userWords || '[]');
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
    
    // Функция для разметки текста
    function processStoryContent(content, words, userWordIds, showFurigana) {
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
            const isInDictionary = userWordIds.includes(word.id);
            const highlightClass = isInDictionary ? 'word-highlight' : '';
            
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
        storyContent.innerHTML = processStoryContent(content, wordsData, userWords, furiganaEnabled);
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
                        <button class="mt-3 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded add-to-dict" data-word-id="${wordId}">
                            Добавить в словарь
                        </button>
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
                    // Добавляем подсветку всем экземплярам слова
                    document.querySelectorAll(`[data-word-id="${wordId}"]`).forEach(el => {
                        el.classList.add('word-highlight');
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
        
        const processedContent = processStoryContent(rawContent, wordsData, userWords, furiganaEnabled);
        console.log('Обработанный контент length:', processedContent.length);
        
        storyContent.innerHTML = processedContent;
        attachWordEvents();
    } catch (error) {
        console.error('Ошибка при обработке текста:', error);
        console.error('Stack trace:', error.stack);
        // В случае ошибки показываем исходный текст
        storyContent.innerHTML = rawContent || '<p class="text-purple-400">Ошибка при загрузке текста</p>';
    }
    
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
