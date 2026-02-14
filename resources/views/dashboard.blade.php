@extends('layouts.app')

@section('title', 'Главная - Obake')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-4xl font-bold mb-8 text-purple-400">Добро пожаловать, {{ auth()->user()->name }}! 👻</h1>
    
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        <a href="{{ route('kanji.index', ['tab' => 'words']) }}" class="bg-gray-800 rounded-lg p-6 hover:bg-gray-700 transition shadow-lg">
            <h2 class="text-2xl font-bold mb-2 text-purple-400">📖 Слова</h2>
            <p class="text-gray-400">Добавляйте и изучайте слова, проходите квиз</p>
        </a>
        
        <a href="{{ route('kanji.index') }}" class="bg-gray-800 rounded-lg p-6 hover:bg-gray-700 transition shadow-lg">
            <h2 class="text-2xl font-bold mb-2 text-purple-400">🎓 Кандзи</h2>
            <p class="text-gray-400">Изучайте кандзи, повторяйте через систему интервалов</p>
        </a>
        
        <a href="{{ route('reading-quiz.index') }}" class="bg-gray-800 rounded-lg p-6 hover:bg-gray-700 transition shadow-lg">
            <h2 class="text-2xl font-bold mb-2 text-blue-400">📚 Квиз на чтение</h2>
            <p class="text-gray-400">Практикуйте чтение слов с выбором ответов</p>
        </a>
    </div>
    
    <div class="mb-8">
        <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
            <h2 class="text-2xl font-bold mb-4">Ваша статистика</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-400">{{ auth()->user()->dictionary()->count() }}</div>
                    <div class="text-gray-400">Слов в словаре</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-400">{{ auth()->user()->wordStudyProgress()->where('is_completed', true)->count() }}</div>
                    <div class="text-gray-400">Слов изучено</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-400">{{ auth()->user()->readingQuizProgress()->where('is_completed', true)->count() }}</div>
                    <div class="text-gray-400">Квиз чтения: готово</div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-6">
            <h2 class="text-2xl font-bold mb-4">⚙️ Настройки</h2>
            <div class="flex items-center gap-4">
                <label class="text-gray-300">Дневная норма слов для повторения:</label>
                <input type="number" id="daily-words-quota" value="{{ auth()->user()->daily_words_quota ?? 10 }}" min="1" max="100" 
                       class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white w-24 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <button id="save-settings-btn" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                    Сохранить
                </button>
            </div>
        </div>
        
        <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold">📅 Календарь активности</h2>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard', ['year' => $selectedYear - 1]) }}" 
                       class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded transition">
                        ←
                    </a>
                    <span class="text-lg font-semibold text-purple-400 min-w-[80px] text-center">{{ $selectedYear }}</span>
                    <a href="{{ route('dashboard', ['year' => $selectedYear + 1]) }}" 
                       class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded transition">
                        →
                    </a>
                </div>
            </div>
            
            <div class="overflow-x-auto mb-4">
                <div id="calendar-grid" class="calendar-container" style="grid-template-columns: repeat({{ $weeksInYear }}, 1fr);">
                    @php
                        $currentDate = $startDate->copy();
                        $maxCount = $repetitionDates->max('count') ?? 1;
                        $endOfYear = $endDate->copy(); // 31 декабря выбранного года
                    @endphp
                    
                    @for($i = 0; $i < $weeksInYear; $i++)
                        <div class="calendar-week">
                            @for($j = 0; $j < 7; $j++)
                                @php
                                    // Пропускаем дни до начала года
                                    if ($i == 0 && $j < $firstDayWeekday) {
                                        $dateKey = null;
                                        $count = 0;
                                        $intensity = 0;
                                        $isToday = false;
                                        $displayDate = null;
                                    } else {
                                        // Проверяем, не вышли ли за пределы года
                                        // Показываем день, если он в пределах выбранного года (включая 31 декабря)
                                        if ($currentDate->year == $selectedYear && $currentDate->lte($endOfYear)) {
                                            // Показываем день года (включая последний день 31 декабря)
                                            $dateKey = $currentDate->format('Y-m-d');
                                            $repetition = $repetitionDates->get($dateKey);
                                            $count = $repetition ? $repetition->count : 0;
                                            $intensity = $maxCount > 0 ? min(4, floor(($count / $maxCount) * 4)) : 0;
                                            $isToday = $currentDate->isSameDay($today);
                                            $displayDate = $currentDate->copy();
                                            
                                            // Переходим к следующему дню
                                            $currentDate->addDay();
                                        } else {
                                            // Если вышли за пределы года, показываем пустую ячейку
                                            $dateKey = null;
                                            $count = 0;
                                            $intensity = 0;
                                            $isToday = false;
                                            $displayDate = null;
                                        }
                                    }
                                @endphp
                                @if($dateKey !== null)
                                    <div 
                                        class="calendar-day 
                                            @if($intensity == 0) bg-gray-700 
                                            @elseif($intensity == 1) bg-purple-600 
                                            @elseif($intensity == 2) bg-purple-500 
                                            @elseif($intensity == 3) bg-purple-400 
                                            @else bg-purple-300 
                                            @endif
                                            @if($isToday) calendar-today @endif"
                                        data-date="{{ $displayDate->format('Y-m-d') }}"
                                        data-count="{{ $count }}"
                                        title="{{ $displayDate->format('d.m.Y') }}: {{ $count }} повторений">
                                    </div>
                                @else
                                    <div class="calendar-day bg-transparent"></div>
                                @endif
                            @endfor
                        </div>
                    @endfor
                </div>
            </div>
            
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-xs">Меньше</span>
                    <div class="flex gap-1">
                        <div class="w-3 h-3 bg-gray-700 rounded-sm"></div>
                        <div class="w-3 h-3 bg-purple-600 rounded-sm"></div>
                        <div class="w-3 h-3 bg-purple-500 rounded-sm"></div>
                        <div class="w-3 h-3 bg-purple-400 rounded-sm"></div>
                        <div class="w-3 h-3 bg-purple-300 rounded-sm"></div>
                    </div>
                    <span class="text-gray-400 text-xs">Больше</span>
                </div>
                
                <div class="text-gray-400 text-xs">
                    Всего: <span class="text-purple-400 font-bold">{{ $repetitionDates->sum('count') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .calendar-container {
        display: grid;
        gap: 3px;
        padding: 2px;
    }
    
    .calendar-week {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    
    .calendar-day {
        width: 11px;
        height: 11px;
        border-radius: 2px;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    
    .calendar-day:hover {
        transform: scale(1.3);
        z-index: 10;
        position: relative;
    }
    
    .calendar-today {
        outline: 2px solid #a855f7;
        outline-offset: 1px;
    }
    
    @media (max-width: 1024px) {
        #calendar-grid {
            grid-template-columns: repeat(26, 1fr) !important;
        }
    }
    
    @media (max-width: 640px) {
        #calendar-grid {
            grid-template-columns: repeat(13, 1fr) !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveSettingsBtn = document.getElementById('save-settings-btn');
    const dailyWordsQuotaInput = document.getElementById('daily-words-quota');
    
    saveSettingsBtn.addEventListener('click', function() {
        const quota = parseInt(dailyWordsQuotaInput.value);
        if (quota < 1 || quota > 100) {
            alert('Дневная норма должна быть от 1 до 100');
            return;
        }
        
        this.disabled = true;
        this.textContent = 'Сохранение...';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
        
        fetch('{{ route("settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                daily_words_quota: quota
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Настройки сохранены');
            } else {
                alert(data.error || data.message || 'Ошибка при сохранении настроек');
            }
            this.disabled = false;
            this.textContent = 'Сохранить';
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'Ошибка при сохранении настроек';
            if (error.message) {
                errorMessage = error.message;
            } else if (error.errors && error.errors.daily_words_quota) {
                errorMessage = error.errors.daily_words_quota[0];
            }
            alert(errorMessage);
            this.disabled = false;
            this.textContent = 'Сохранить';
        });
    });
});
</script>
@endpush
@endsection
