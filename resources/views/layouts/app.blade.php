<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Obake - Изучение японского языка')</title>

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
                            <a href="{{ route('stories.index') }}" class="px-4 py-2 text-gray-300 hover:text-white transition">Рассказы</a>
                            <a href="{{ route('dictionary.index') }}" class="px-4 py-2 text-gray-300 hover:text-white transition">Мой словарь</a>
                            
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-purple-400 hover:text-purple-300 transition">Админ</a>
                            @endif

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

    @stack('scripts')
</body>
</html>
