@extends('layouts.app')

@section('title', 'Вход - Obake')

@section('content')
<div class="max-w-md mx-auto mt-16">
    <div class="bg-gray-800 rounded-lg shadow-xl p-8">
        <h2 class="text-3xl font-bold text-center mb-6 text-purple-400">Вход в Obake</h2>
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-gray-300 mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                @error('email')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-300 mb-2">Пароль</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-purple-500">
                @error('password')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded bg-gray-700 border-gray-600 text-purple-600">
                    <span class="ml-2 text-gray-300">Запомнить меня</span>
                </label>
            </div>
            
            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                Войти
            </button>
        </form>
        
        <p class="mt-6 text-center text-gray-400">
            Нет аккаунта? 
            <a href="{{ route('register') }}" class="text-purple-400 hover:text-purple-300">Зарегистрироваться</a>
        </p>
    </div>
</div>
@endsection
