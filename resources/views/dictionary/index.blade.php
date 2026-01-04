@extends('layouts.app')

@section('title', 'Мой словарь - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-purple-400">📖 Мой словарь</h1>
    
    <!-- Фильтры -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('dictionary.index', ['filter' => 'all']) }}" 
           class="px-4 py-2 rounded-lg {{ $filter === 'all' ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            Все
        </a>
        <a href="{{ route('dictionary.index', ['filter' => 'not_started']) }}" 
           class="px-4 py-2 rounded-lg {{ $filter === 'not_started' ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            Не начато
        </a>
        <a href="{{ route('dictionary.index', ['filter' => 'studying']) }}" 
           class="px-4 py-2 rounded-lg {{ $filter === 'studying' ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            В изучении
        </a>
        <a href="{{ route('dictionary.index', ['filter' => 'completed']) }}" 
           class="px-4 py-2 rounded-lg {{ $filter === 'completed' ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            Изучено
        </a>
    </div>
    
    @if($words->count() > 0)
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="grid gap-4">
                @foreach($words as $word)
                    @php
                        $progress = $wordProgresses->get($word->id);
                        $isCompleted = $progress && $progress->is_completed;
                        $isStudying = $progress && !$progress->is_completed;
                    @endphp
                    <div class="bg-gray-700 rounded-lg p-4 flex justify-between items-center">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="text-2xl font-bold japanese-font">{{ $word->japanese_word }}</div>
                                @if($isCompleted)
                                    <span class="bg-green-600 text-white px-3 py-1 rounded text-sm font-semibold">
                                        ✓ Изучено
                                    </span>
                                @elseif($isStudying)
                                    <span class="bg-blue-600 text-white px-3 py-1 rounded text-sm font-semibold">
                                        В изучении ({{ $progress->days_studied }}/10)
                                    </span>
                                @else
                                    <span class="bg-gray-600 text-white px-3 py-1 rounded text-sm font-semibold">
                                        Не начато
                                    </span>
                                @endif
                            </div>
                            @if($word->reading)
                                <div class="text-gray-400 text-sm mb-1">{{ $word->reading }}</div>
                            @endif
                            <div class="text-gray-300">{{ $word->translation_ru }}</div>
                            @if($word->translation_en)
                                <div class="text-gray-400 text-sm">{{ $word->translation_en }}</div>
                            @endif
                        </div>
                        <div class="flex gap-2 ml-4 items-center">
                            <button 
                                type="button"
                                class="speak-word-btn bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition text-sm flex items-center gap-1"
                                data-word-id="{{ $word->id }}"
                                data-word-text="{{ $word->japanese_word }}"
                                data-word-reading="{{ $word->reading ?? '' }}"
                                data-word-audio-path="{{ $word->audio_path ?? '' }}"
                                title="Озвучить слово">
                                <span class="word-play-icon">▶</span>
                                <span class="word-audio-time text-xs"></span>
                            </button>
                            <a href="{{ route('dictionary.edit', $word->id) }}" 
                               class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition text-sm">
                                Редактировать
                            </a>
                            @if(!$isCompleted)
                                <button 
                                    type="button"
                                    class="mark-completed-btn bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition text-sm"
                                    data-word-id="{{ $word->id }}">
                                    ✓ Изучено
                                </button>
                            @endif
                            <form method="POST" action="{{ route('dictionary.remove', $word->id) }}" class="inline" onsubmit="return confirm('Вы уверены, что хотите удалить это слово из словаря?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                                    Удалить
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $words->links() }}
            </div>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg shadow-lg p-8 text-center">
            <p class="text-xl text-gray-400 mb-4">Ваш словарь пуст</p>
            <p class="text-gray-500">Начните читать рассказы и добавляйте слова в свой словарь!</p>
            <a href="{{ route('stories.index') }}" class="inline-block mt-4 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition">
                Перейти к рассказам
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const markCompletedBtns = document.querySelectorAll('.mark-completed-btn');
    
    markCompletedBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const wordId = this.dataset.wordId;
            const originalText = this.textContent;
            
            this.disabled = true;
            this.textContent = 'Отмечаю...';
            
            fetch('{{ route("dictionary.mark-completed") }}', {
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
                    // Перезагружаем страницу для обновления статусов
                    location.reload();
                } else {
                    alert(data.error || 'Ошибка при отметке слова');
                    this.disabled = false;
                    this.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при отметке слова');
                this.disabled = false;
                this.textContent = originalText;
            });
        });
    });
    
    // Функция для получения японского голоса
    let selectedVoice = null;
    function selectBestJapaneseVoice() {
        const voices = window.speechSynthesis.getVoices();
        if (voices.length === 0) return null;
        
        // Ищем нейронный голос Google (обычно лучший)
        let neuralVoice = voices.find(v => 
            v.lang.startsWith('ja') && 
            (v.name.includes('Neural') || v.name.includes('Neural2'))
        );
        
        if (neuralVoice) return neuralVoice;
        
        // Ищем любой японский голос женского пола
        let femaleVoice = voices.find(v => 
            v.lang.startsWith('ja') && 
            (v.name.includes('Female') || v.name.includes('女') || v.name.includes('F'))
        );
        
        if (femaleVoice) return femaleVoice;
        
        // Ищем любой японский голос
        let japaneseVoice = voices.find(v => v.lang.startsWith('ja'));
        
        return japaneseVoice || null;
    }
    
    // Загружаем голоса
    function loadVoices() {
        const voices = window.speechSynthesis.getVoices();
        if (voices.length > 0) {
            selectedVoice = selectBestJapaneseVoice();
            if (selectedVoice) {
                console.log('Выбран голос:', selectedVoice.name, selectedVoice.lang);
            }
        }
    }
    
    loadVoices();
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = loadVoices;
    }
    
    // Обработка кнопок озвучки слов (делегирование событий для динамических кнопок)
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.speak-word-btn');
        if (!btn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const wordId = btn.dataset.wordId;
        const wordText = btn.dataset.wordText;
        const audioPath = btn.dataset.wordAudioPath;
        
        // Проверяем, есть ли сохраненное аудио
        if (audioPath) {
            try {
                // Используем специальный маршрут для аудио с поддержкой Range-запросов
                const audioUrl = '{{ url("/audio") }}/' + audioPath;
                const audio = new Audio(audioUrl);
                await audio.play();
                return;
            } catch (error) {
                console.error('Ошибка воспроизведения аудио:', error);
            }
        }
        
        // Если аудио нет, используем браузерную озвучку
        if ('speechSynthesis' in window) {
            // Используем reading если есть, иначе japanese_word
            const textToSpeak = btn.dataset.wordReading || wordText || '';
            
            if (!textToSpeak) {
                console.warn('Нет текста для озвучивания');
                return;
            }
            
            // Отменяем предыдущее озвучивание перед новым
            if (window.speechSynthesis.speaking || window.speechSynthesis.pending) {
                console.log('Уже идет озвучивание, отменяем для нового слова');
                window.speechSynthesis.cancel();
                // Даем время на отмену перед новым озвучиванием
                setTimeout(() => {
                    startSpeaking();
                }, 100);
            } else {
                startSpeaking();
            }
            
            function startSpeaking() {
            
                const utterance = new SpeechSynthesisUtterance(textToSpeak);
                utterance.lang = 'ja-JP';
                utterance.rate = 1.0;
                utterance.pitch = 1.0;
                utterance.volume = 1.0;
                
                // Функция для озвучивания с голосом
                function speakWithVoice() {
                    const voices = window.speechSynthesis.getVoices();
                    if (voices.length > 0) {
                        // Ищем японский голос
                        const japaneseVoice = voices.find(v => v.lang.startsWith('ja')) || null;
                        if (japaneseVoice) {
                            // Проверяем, что голос действительно доступен
                            if (japaneseVoice.localService === false) {
                                console.warn('ВНИМАНИЕ: Голос не является локальным (localService: false). Возможно, голосовые файлы отсутствуют или повреждены.');
                                console.warn('Попробуйте переустановить японские голоса Windows через:');
                                console.warn('Параметры Windows → Время и язык → Язык → Добавить язык → Японский → Речь');
                            }
                            utterance.voice = japaneseVoice;
                            console.log('Используем голос:', japaneseVoice.name, 'lang:', japaneseVoice.lang, 'localService:', japaneseVoice.localService);
                        } else {
                            console.error('Японский голос не найден!');
                            console.warn('Для установки японских голосов:');
                            console.warn('1. Параметры Windows → Время и язык → Язык');
                            console.warn('2. Добавить язык → Японский');
                            console.warn('3. После установки: Параметры → Речь → Добавить голос');
                        }
                    } else {
                        console.error('Голоса не загружены! Возможно, они были удалены.');
                        console.warn('Попробуйте перезагрузить страницу или переустановить голоса Windows.');
                    }
                        console.log('Начинаем озвучивание:', textToSpeak, 'volume:', utterance.volume);
                    
                    // Добавляем обработчики для отладки ПЕРЕД вызовом speak
                    utterance.onstart = function(event) {
                        console.log('Озвучивание началось', {
                            charIndex: event.charIndex,
                            elapsedTime: event.elapsedTime,
                            name: event.name,
                            voice: utterance.voice ? utterance.voice.name : 'не установлен'
                        });
                    };
                    utterance.onerror = function(event) {
                        console.error('Ошибка озвучивания:', {
                            error: event.error,
                            charIndex: event.charIndex,
                            type: event.type,
                            message: event.error === 'network' ? 'Проблема с сетью' : 
                                     event.error === 'synthesis' ? 'Проблема синтеза' :
                                     event.error === 'synthesis-unavailable' ? 'Синтез недоступен' :
                                     event.error === 'audio-busy' ? 'Аудио занято' :
                                     event.error === 'audio-hardware' ? 'Проблема с аудио-оборудованием' :
                                     'Неизвестная ошибка'
                        });
                    };
                    utterance.onend = function(event) {
                        console.log('Озвучивание завершено', {
                            charIndex: event.charIndex,
                            elapsedTime: event.elapsedTime,
                            name: event.name
                        });
                    };
                    utterance.onpause = function(event) {
                        console.log('Озвучивание приостановлено', event);
                    };
                    utterance.onresume = function(event) {
                        console.log('Озвучивание возобновлено', event);
                    };
                    
                    // Убеждаемся, что speechSynthesis не заблокирован
                    // Некоторые браузеры требуют user interaction
                    try {
                        // Отменяем любые текущие озвучивания перед новым
                        if (window.speechSynthesis.speaking) {
                            window.speechSynthesis.cancel();
                            // Ждем немного перед новым озвучиванием
                            setTimeout(() => {
                                console.log('Запускаем speechSynthesis.speak после отмены предыдущего');
                                window.speechSynthesis.speak(utterance);
                            }, 50);
                        } else {
                            console.log('Запускаем speechSynthesis.speak');
                            window.speechSynthesis.speak(utterance);
                        }
                    } catch (e) {
                        console.error('Ошибка при вызове speak:', e);
                        // Пробуем еще раз через небольшую задержку
                        setTimeout(() => {
                            try {
                                window.speechSynthesis.speak(utterance);
                            } catch (e2) {
                                console.error('Повторная ошибка:', e2);
                            }
                        }, 100);
                    }
                }
                
                // Проверяем, загружены ли голоса
                const voices = window.speechSynthesis.getVoices();
                if (voices.length > 0) {
                    speakWithVoice();
                } else {
                    console.log('Голоса еще не загружены, ждем...');
                    // Ждем загрузки голосов
                    const voicesHandler = function() {
                        console.log('Голоса загружены');
                        speakWithVoice();
                        // Удаляем обработчик после первого использования
                        window.speechSynthesis.onvoiceschanged = null;
                    };
                    window.speechSynthesis.onvoiceschanged = voicesHandler;
                    
                    // Таймаут на случай, если событие не сработает
                    setTimeout(function() {
                        if (window.speechSynthesis.getVoices().length > 0) {
                            speakWithVoice();
                        } else {
                            console.warn('Голоса не загрузились, пробуем без голоса');
                            try {
                                window.speechSynthesis.speak(utterance);
                            } catch (e) {
                                console.error('Ошибка при озвучивании без голоса:', e);
                            }
                        }
                    }, 1000);
                }
            }
        } else {
            alert('Озвучка не поддерживается в вашем браузере');
        }
    });
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
