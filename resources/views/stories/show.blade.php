@extends('layouts.app')

@section('title', $story->title . ' - Obake')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('stories.index') }}" class="text-red-400 hover:text-red-300">← Назад к рассказам</a>
    </div>
    
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <span class="inline-block bg-red-600 text-white px-3 py-1 rounded text-sm font-semibold">
                    {{ $story->level }}
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="furigana-toggle" class="rounded bg-gray-700 border-gray-600 text-red-600">
                    <span class="ml-2 text-gray-300">Фуригана</span>
                </label>
            </div>
        </div>
        
        <h1 class="text-3xl font-bold mb-4 text-red-400">{{ $story->title }}</h1>
        
        @if($story->description)
            <p class="text-gray-400 mb-6">{{ $story->description }}</p>
        @endif
        
        <div id="story-content" class="text-lg leading-relaxed japanese-font mb-8" 
             data-content="{{ htmlspecialchars($story->content, ENT_QUOTES, 'UTF-8') }}"
             data-words='@json($words->keyBy('id')->map(function($word) {
                 return [
                     'id' => $word->id,
                     'japanese' => $word->japanese_word,
                     'reading' => $word->reading,
                     'translation_ru' => $word->translation_ru,
                     'translation_en' => $word->translation_en,
                 ];
             }))'
             data-user-words='@json($userWordIds)'>
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
    const wordsData = JSON.parse(storyContent.dataset.words || '{}');
    const userWords = JSON.parse(storyContent.dataset.userWords || '[]');
    const rawContent = storyContent.dataset.content;
    
    let furiganaEnabled = false;
    const furiganaToggle = document.getElementById('furigana-toggle');
    const tooltip = document.getElementById('word-tooltip');
    const tooltipContent = document.getElementById('tooltip-content');
    
    // Функция для разметки текста
    function processStoryContent(content, words, userWordIds, showFurigana) {
        let processed = content;
        const wordPatterns = [];
        
        // Создаем паттерны для всех слов, сортируем по длине (длиннее сначала)
        Object.values(words).forEach(word => {
            const escapedWord = word.japanese.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            wordPatterns.push({
                pattern: new RegExp(escapedWord, 'g'),
                word: word,
                length: word.japanese.length
            });
        });
        
        // Сортируем по длине (длиннее сначала), чтобы не заменять части слов
        wordPatterns.sort((a, b) => b.length - a.length);
        
        wordPatterns.forEach(({pattern, word}) => {
            const isInDictionary = userWordIds.includes(word.id);
            const highlightClass = isInDictionary ? 'word-highlight' : '';
            
            processed = processed.replace(pattern, (match) => {
                let replacement = `<span class="word-item ${highlightClass}" 
                    data-word-id="${word.id}"
                    data-reading="${word.reading || ''}"
                    data-translation-ru="${escapeHtml(word.translation_ru)}"
                    data-translation-en="${escapeHtml(word.translation_en)}"
                    data-japanese="${escapeHtml(word.japanese)}">${match}</span>`;
                
                // Добавляем фуригану если включена
                if (showFurigana && word.reading) {
                    replacement = `<ruby>${match}<rt class="furigana">${word.reading}</rt></ruby>`;
                    replacement = replacement.replace(match, `<span class="word-item ${highlightClass}" 
                        data-word-id="${word.id}"
                        data-reading="${word.reading || ''}"
                        data-translation-ru="${escapeHtml(word.translation_ru)}"
                        data-translation-en="${escapeHtml(word.translation_en)}"
                        data-japanese="${escapeHtml(word.japanese)}">${match}</span>`);
                }
                
                return replacement;
            });
        });
        
        return processed;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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
                const wordId = this.dataset.wordId;
                const word = wordsData[wordId];
                
                if (word) {
                    tooltipContent.innerHTML = `
                        <div class="text-xl font-bold japanese-font mb-2">${word.japanese}</div>
                        ${word.reading ? `<div class="text-gray-400 mb-2">${word.reading}</div>` : ''}
                        <div class="text-gray-300 mb-1">${word.translation_ru}</div>
                        ${word.translation_en ? `<div class="text-gray-400 text-sm">${word.translation_en}</div>` : ''}
                        <button class="mt-3 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded add-to-dict" data-word-id="${wordId}">
                            Добавить в словарь
                        </button>
                    `;
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
                    tooltip.classList.remove('hidden');
                }
            });
            
            item.addEventListener('mouseleave', function() {
                tooltip.classList.add('hidden');
            });
        });
        
        // Обработка добавления в словарь
        document.querySelectorAll('.add-to-dict').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.stopPropagation();
                const wordId = this.dataset.wordId;
                
                try {
                    const response = await fetch('{{ route("dictionary.add") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ word_id: wordId })
                    });
                    
                    if (response.ok) {
                        // Добавляем подсветку
                        document.querySelectorAll(`[data-word-id="${wordId}"]`).forEach(el => {
                            el.classList.add('word-highlight');
                        });
                        this.textContent = 'В словаре';
                        this.disabled = true;
                        userWords.push(parseInt(wordId));
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });
    }
    
    // Инициализация
    storyContent.innerHTML = processStoryContent(rawContent, wordsData, userWords, furiganaEnabled);
    attachWordEvents();
});
</script>
@endpush
@endsection
