@extends('layouts.app')

@section('title', 'Изучение кандзи')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-purple-400 mb-2">📚 Изучение кандзи</h1>
        <p class="text-gray-400">Изучайте кандзи в формате квиза</p>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-purple-800/50 to-indigo-800/50 rounded-xl p-6 border border-purple-700/50">
            <div class="text-3xl font-bold text-purple-300 mb-1">{{ $totalKanji }}</div>
            <div class="text-gray-300">Всего кандзи</div>
        </div>
        <div class="bg-gradient-to-br from-blue-800/50 to-cyan-800/50 rounded-xl p-6 border border-blue-700/50">
            <div class="text-3xl font-bold text-blue-300 mb-1">{{ $studiedKanji }}</div>
            <div class="text-gray-300">Изучается</div>
        </div>
        <div class="bg-gradient-to-br from-green-800/50 to-emerald-800/50 rounded-xl p-6 border border-green-700/50">
            <div class="text-3xl font-bold text-green-300 mb-1">{{ $completedKanji }}</div>
            <div class="text-gray-300">Изучено (10/10)</div>
        </div>
    </div>

    <!-- Кнопка начала квиза -->
    <div class="mb-8 bg-gray-800/50 rounded-xl p-6 border border-gray-700">
        <h3 class="text-xl font-bold text-purple-400 mb-4">Начать квиз</h3>
        <form action="{{ route('kanji.quiz') }}" method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-gray-300">Количество:</label>
                <input type="number" name="count" value="10" min="1" max="50" 
                       class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white w-24 focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-gray-300">Уровень JLPT:</label>
                <select name="jlpt_level" 
                       class="bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="any">Любой</option>
                    <option value="5">N5 (Начальный)</option>
                    <option value="4">N4 (Базовый)</option>
                    <option value="3">N3 (Средний)</option>
                    <option value="2">N2 (Выше среднего)</option>
                    <option value="1">N1 (Продвинутый)</option>
                </select>
            </div>
            <button type="submit" 
                    class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-lg hover:shadow-purple-500/50 transform hover:scale-105">
                Начать квиз 🎯
            </button>
        </form>
    </div>

    <!-- Список кандзи по уровням JLPT -->
    <div class="space-y-8">
        @foreach($kanjiByLevel as $level => $kanjiList)
            <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                <h2 class="text-2xl font-bold text-purple-400 mb-4 flex items-center gap-2">
                    @if($level === 'Без уровня')
                        📋 {{ $level }}
                    @else
                        🎓 {{ $level }}
                    @endif
                    <span class="text-sm font-normal text-gray-400">({{ $kanjiList->count() }} кандзи)</span>
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <tbody>
                            @php
                                $chunkSize = 10; // Количество кандзи в строке
                                $chunks = $kanjiList->chunk($chunkSize);
                            @endphp
                            @foreach($chunks as $chunk)
                                <tr>
                                    @foreach($chunk as $item)
                                        <td class="kanji-item bg-gray-700/50 border border-gray-600 hover:border-purple-500 transition-all hover:shadow-lg hover:shadow-purple-500/20 cursor-pointer text-center p-4 align-middle"
                                            data-kanji="{{ $item['kanji'] }}"
                                            data-translation="{{ htmlspecialchars($item['translation'], ENT_QUOTES, 'UTF-8') }}"
                                            data-level="{{ $item['level'] }}"
                                            data-last-reviewed="{{ $item['last_reviewed_at'] ? $item['last_reviewed_at']->format('d.m.Y') : '' }}"
                                            data-is-completed="{{ $item['is_completed'] ? '1' : '0' }}"
                                            data-image-path="{{ $item['image_path'] ?? '' }}"
                                            data-mnemonic="{{ htmlspecialchars($item['mnemonic'] ?? '', ENT_QUOTES, 'UTF-8') }}"
                                            onclick="openKanjiModal(this)">
                                            <div class="text-6xl font-bold text-white" style="font-family: 'Noto Sans JP', sans-serif;">{{ $item['kanji'] }}</div>
                                        </td>
                                    @endforeach
                                    @for($i = $chunk->count(); $i < $chunkSize; $i++)
                                        <td class="border border-transparent p-4"></td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Модальное окно с деталями кандзи -->
<div id="kanji-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999;">
    <div class="flex items-center justify-center min-h-screen p-4" style="position: relative; z-index: 10000;">
    <div class="bg-gray-800 rounded-xl p-6 max-w-md w-full mx-4 border border-gray-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-purple-400" id="modal-kanji" style="font-family: 'Noto Sans JP', sans-serif;"></h3>
            <button id="close-modal" class="text-gray-400 hover:text-white text-2xl">&times;</button>
        </div>
        
        <div id="modal-image" class="mb-4 hidden">
            <img src="" alt="Kanji image" class="w-full rounded-lg" id="modal-image-src">
        </div>
        
        <div class="mb-4">
            <div class="text-gray-300 mb-2">
                <span class="text-gray-400">Перевод:</span> <span id="modal-translation" class="font-semibold"></span>
            </div>
            <div class="text-gray-300 mb-2">
                <span class="text-gray-400">Уровень знания:</span> <span id="modal-level" class="font-semibold text-purple-300"></span>
            </div>
            <div class="text-gray-300 mb-2" id="modal-last-reviewed-container">
                <span class="text-gray-400">Последнее повторение:</span> <span id="modal-last-reviewed" class="font-semibold"></span>
            </div>
            <div class="text-gray-300 mb-2" id="modal-mnemonic-container">
                <span class="text-gray-400">Мнемоническая подсказка:</span>
                <div id="modal-mnemonic" class="mt-1 text-sm"></div>
            </div>
        </div>
        
        <div class="flex gap-2">
            <button id="mark-completed-btn" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                Отметить как изученное
            </button>
        </div>
    </div>
    </div>
</div>

@push('scripts')
<script>
let currentKanji = null;

function openKanjiModal(kanjiItem) {
    console.log('openKanjiModal вызвана', kanjiItem);
    const modal = document.getElementById('kanji-modal');
    const markCompletedBtn = document.getElementById('mark-completed-btn');
    
    if (!kanjiItem) {
        console.error('kanjiItem не передан');
        return;
    }
    
    if (!modal) {
        console.error('Модальное окно не найдено в DOM');
        return;
    }
    
    currentKanji = kanjiItem.getAttribute('data-kanji') || kanjiItem.dataset.kanji;
    
    if (!currentKanji) {
        console.error('Кандзи не найден в data-атрибуте', kanjiItem, kanjiItem.dataset);
        return;
    }
    
    console.log('Открываем модальное окно для кандзи:', currentKanji);
        
        // Заполняем модальное окно
        const kanjiEl = document.getElementById('modal-kanji');
        const translationEl = document.getElementById('modal-translation');
        const levelEl = document.getElementById('modal-level');
        const lastReviewedEl = document.getElementById('modal-last-reviewed');
        const lastReviewedContainer = document.getElementById('modal-last-reviewed-container');
        const imageEl = document.getElementById('modal-image');
        const imageSrcEl = document.getElementById('modal-image-src');
        const mnemonicEl = document.getElementById('modal-mnemonic');
        const mnemonicContainer = document.getElementById('modal-mnemonic-container');
        
        // Получаем данные из атрибутов
        const translation = kanjiItem.getAttribute('data-translation') || kanjiItem.dataset.translation || '';
        const level = kanjiItem.getAttribute('data-level') || kanjiItem.dataset.level || '0';
        const lastReviewed = kanjiItem.getAttribute('data-last-reviewed') || kanjiItem.dataset.lastReviewed || '';
        const imagePath = kanjiItem.getAttribute('data-image-path') || kanjiItem.dataset.imagePath || '';
        const mnemonic = kanjiItem.getAttribute('data-mnemonic') || kanjiItem.dataset.mnemonic || '';
        const isCompleted = kanjiItem.getAttribute('data-is-completed') || kanjiItem.dataset.isCompleted || '0';
        
        if (kanjiEl) kanjiEl.textContent = currentKanji;
        if (translationEl) translationEl.textContent = translation;
        if (levelEl) levelEl.textContent = level + '/10';
        
        // Последнее повторение
        if (lastReviewed && lastReviewed.trim() !== '') {
            if (lastReviewedEl) lastReviewedEl.textContent = lastReviewed;
            if (lastReviewedContainer) lastReviewedContainer.classList.remove('hidden');
        } else {
            if (lastReviewedContainer) lastReviewedContainer.classList.add('hidden');
        }
        
        // Изображение
        if (imagePath && imagePath.trim() !== '') {
            if (imageSrcEl) imageSrcEl.src = '{{ asset("storage") }}/' + imagePath;
            if (imageEl) imageEl.classList.remove('hidden');
        } else {
            if (imageEl) imageEl.classList.add('hidden');
        }
        
        // Мнемоника
        if (mnemonic && mnemonic.trim() !== '') {
            if (mnemonicEl) mnemonicEl.textContent = mnemonic;
            if (mnemonicContainer) mnemonicContainer.classList.remove('hidden');
        } else {
            if (mnemonicContainer) mnemonicContainer.classList.add('hidden');
        }
        
        // Кнопка отметки
        if (isCompleted === '1') {
            if (markCompletedBtn) {
                markCompletedBtn.textContent = 'Изучено';
                markCompletedBtn.disabled = true;
                markCompletedBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        } else {
            if (markCompletedBtn) {
                markCompletedBtn.textContent = 'Отметить как изученное';
                markCompletedBtn.disabled = false;
                markCompletedBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        
    // Показываем модальное окно
    if (modal) {
        // Принудительно устанавливаем все стили через setProperty с important
        modal.style.setProperty('display', 'flex', 'important');
        modal.style.setProperty('visibility', 'visible', 'important');
        modal.style.setProperty('opacity', '1', 'important');
        modal.style.setProperty('z-index', '9999', 'important');
        modal.style.setProperty('position', 'fixed', 'important');
        modal.style.setProperty('top', '0', 'important');
        modal.style.setProperty('left', '0', 'important');
        modal.style.setProperty('right', '0', 'important');
        modal.style.setProperty('bottom', '0', 'important');
        modal.style.setProperty('background-color', 'rgba(0, 0, 0, 0.5)', 'important');
        
        console.log('Модальное окно должно быть видимым', {
            display: modal.style.display,
            computed: window.getComputedStyle(modal).display,
            visibility: modal.style.visibility,
            zIndex: modal.style.zIndex
        });
    } else {
        console.error('Модальное окно не найдено');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('kanji-modal');
    const closeModal = document.getElementById('close-modal');
    const markCompletedBtn = document.getElementById('mark-completed-btn');
    
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            if (modal) {
                modal.style.setProperty('display', 'none', 'important');
            }
        });
    }
    
    if (modal) {
        // Закрываем при клике на фон
        modal.addEventListener('click', function(e) {
            // Проверяем, что клик был именно на фон, а не на содержимое
            if (e.target === modal) {
                modal.style.setProperty('display', 'none', 'important');
            }
        });
        
        // Предотвращаем закрытие при клике на содержимое
        const modalContent = modal.querySelector('.bg-gray-800');
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
    
    if (markCompletedBtn) {
        markCompletedBtn.addEventListener('click', function() {
            if (!currentKanji) return;
            
            this.disabled = true;
            this.textContent = 'Отмечаю...';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
            
            fetch('{{ route("kanji.mark-completed") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    kanji: currentKanji
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Ошибка при отметке кандзи');
                    this.disabled = false;
                    this.textContent = 'Отметить как изученное';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при отметке кандзи');
                this.disabled = false;
                this.textContent = 'Отметить как изученное';
            });
        });
    }
});
</script>
@endpush
@endsection
