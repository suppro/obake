<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Obake - Изучение японского языка</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
            @vite(['resources/css/app.css', 'resources/js/app.js'])
            <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .japanese-font {
            font-family: 'Noto Sans JP', sans-serif;
        }
            </style>
    </head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 text-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-6xl font-bold mb-4 japanese-font">
                <span class="text-purple-400">👻</span> Obake
            </h1>
            <p class="text-2xl mb-8 text-gray-300">
                お化け - Изучение японского языка
            </p>
            
            <div class="bg-gray-800 bg-opacity-80 backdrop-blur-sm rounded-lg p-8 mb-8 shadow-2xl">
                <h2 class="text-3xl font-bold mb-4 text-purple-400">Добро пожаловать в Obake!</h2>
                <p class="text-lg mb-4 text-gray-300">
                    Obake — это платформа для изучения японского языка через чтение рассказов и изучение новых слов.
                </p>
                <p class="text-lg mb-6 text-gray-300">
                    Здесь вы найдете рассказы разных уровней сложности (N5-N1), сможете создать свой личный словарь и изучать японский язык эффективно и интересно.
                </p>
                
                <div class="flex justify-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                            Перейти в приложение
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                            Зарегистрироваться
                        </a>
                        <a href="{{ route('login') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition">
                            Войти
                        </a>
                    @endauth
                </div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6 mt-12">
                <div class="bg-gray-800 bg-opacity-60 rounded-lg p-6">
                    <div class="text-4xl mb-4">📚</div>
                    <h3 class="text-xl font-bold mb-2">Рассказы</h3>
                    <p class="text-gray-400">Читайте рассказы на японском разных уровней сложности</p>
                </div>
                <div class="bg-gray-800 bg-opacity-60 rounded-lg p-6">
                    <div class="text-4xl mb-4">📖</div>
                    <h3 class="text-xl font-bold mb-2">Словарь</h3>
                    <p class="text-gray-400">Создавайте свой личный словарь изучаемых слов</p>
                </div>
                <div class="bg-gray-800 bg-opacity-60 rounded-lg p-6">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-xl font-bold mb-2">Уровни</h3>
                    <p class="text-gray-400">От N5 до N1 - изучайте язык поэтапно</p>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>