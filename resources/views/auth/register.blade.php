@extends('layouts.app')

@section('title', 'Регистрация - Obake')

@section('content')
<div class="max-w-md mx-auto mt-16">
    <div class="bg-gray-800 rounded-lg shadow-xl p-8">
        <h2 class="text-3xl font-bold text-center mb-6 text-red-400">Регистрация в Obake</h2>
        
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="mb-4">
                <label for="name" class="block text-gray-300 mb-2">Имя</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-red-500">
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-300 mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-red-500">
                @error('email')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-300 mb-2">Пароль</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-red-500">
                @error('password')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-300 mb-2">Подтверждение пароля</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-red-500">
            </div>
            
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                Зарегистрироваться
            </button>
        </form>
        
        <p class="mt-6 text-center text-gray-400">
            Уже есть аккаунт? 
            <a href="{{ route('login') }}" class="text-red-400 hover:text-red-300">Войти</a>
        </p>
    </div>
</div>
@endsection
