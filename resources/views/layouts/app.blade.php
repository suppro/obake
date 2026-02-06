<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#9333ea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Obake">

    <title>@yield('title', 'Obake - Изучение японского языка')</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-900 text-gray-100 font-sans antialiased">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-gray-800 border-b border-gray-700 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-2xl font-bold text-purple-400 hover:text-purple-300 transition">
                            <span>👻</span>
                            <span>Obake</span>
                        </a>
                    </div>

                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-gray-300 hover:text-white transition">Главная</a>
                            <a href="{{ route('study.index') }}" class="px-4 py-2 text-gray-300 hover:text-white transition">Изучение</a>
                            <a href="{{ route('kanji.index') }}" class="px-4 py-2 text-gray-300 hover:text-white transition">Кандзи и слова</a>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-gray-300 hover:text-white transition">Выход</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-gray-300 hover:text-white transition">Вход</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">Регистрация</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            @if(session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="bg-green-600 text-white px-4 py-3 rounded-lg">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="bg-purple-600 text-white px-4 py-3 rounded-lg">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 border-t border-gray-700 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <p class="text-center text-gray-400 text-sm">
                    &copy; {{ date('Y') }} Obake. Изучение японского языка.
                </p>
            </div>
        </footer>
    </div>

    <!-- Глобальная функция выбора японского TTS голоса (приоритет Google Japanese) -->
    <script>
        // Глобальная переменная для хранения выбранного японского голоса
        window.japaneseVoice = null;
        
        /**
         * Выбирает лучший японский голос из доступных
         * Приоритет: Google Japanese (ja-JP) > любой ja-JP neural/premium > любой японский
         */
        window.selectBestJapaneseVoice = function() {
            if (!window.speechSynthesis) return null;
            
            const voices = window.speechSynthesis.getVoices();
            if (!voices || voices.length === 0) return null;
            
            // 1. Приоритет: Google Japanese (точное совпадение)
            let googleVoice = voices.find(v => 
                v.lang === 'ja-JP' && 
                (v.name.includes('Google') || v.name.includes('google'))
            );
            if (googleVoice) {
                console.log('✅ Выбран голос:', googleVoice.name, googleVoice.lang);
                return googleVoice;
            }
            
            // 2. Ищем японский голос с "neural", "premium" или "enhanced" (обычно качественнее)
            let neuralVoice = voices.find(v => 
                v.lang.startsWith('ja') && 
                (v.name.toLowerCase().includes('neural') || 
                 v.name.toLowerCase().includes('premium') || 
                 v.name.toLowerCase().includes('enhanced') ||
                 v.name.includes('Microsoft'))
            );
            if (neuralVoice) {
                console.log('✅ Выбран голос:', neuralVoice.name, neuralVoice.lang);
                return neuralVoice;
            }
            
            // 3. Ищем любой японский голос женского пола (обычно звучат лучше)
            let femaleVoice = voices.find(v => 
                v.lang.startsWith('ja') && 
                (v.name.includes('Female') || v.name.includes('女') || v.name.includes('F'))
            );
            if (femaleVoice) {
                console.log('✅ Выбран голос:', femaleVoice.name, femaleVoice.lang);
                return femaleVoice;
            }
            
            // 4. Ищем любой японский голос
            let japaneseVoice = voices.find(v => v.lang.startsWith('ja'));
            if (japaneseVoice) {
                console.log('✅ Выбран голос:', japaneseVoice.name, japaneseVoice.lang);
                return japaneseVoice;
            }
            
            console.warn('⚠️ Японский голос не найден, будет использован голос по умолчанию');
            return null;
        };
        
        /**
         * Универсальная функция озвучки текста на японском
         * @param {string} text - текст для озвучки
         * @param {function} onEnd - колбэк после завершения (опционально)
         */
        window.speakJapanese = function(text, onEnd) {
            if (!window.speechSynthesis || !text) return;
            
            // Отменяем предыдущую озвучку
            window.speechSynthesis.cancel();
            
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'ja-JP';
            utterance.rate = 0.9; // Немного медленнее для лучшего восприятия
            
            // Используем выбранный голос
            if (window.japaneseVoice) {
                utterance.voice = window.japaneseVoice;
            }
            
            if (onEnd && typeof onEnd === 'function') {
                utterance.onend = onEnd;
            }
            
            window.speechSynthesis.speak(utterance);
        };
        
        // Загружаем голоса при загрузке страницы
        function loadJapaneseVoice() {
            window.japaneseVoice = window.selectBestJapaneseVoice();
        }
        
        // Загружаем сразу
        loadJapaneseVoice();
        
        // И при изменении списка голосов (нужно для некоторых браузеров)
        if (window.speechSynthesis.onvoiceschanged !== undefined) {
            window.speechSynthesis.onvoiceschanged = loadJapaneseVoice;
        }
    </script>
    
    @stack('scripts')
    
    <!-- Service Worker Registration for PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset("sw.js") }}')
                    .then((registration) => {
                        console.log('Service Worker registered: ', registration);
                    })
                    .catch((registrationError) => {
                        console.log('Service Worker registration failed: ', registrationError);
                    });
            });
        }
    </script>
</body>
</html>
