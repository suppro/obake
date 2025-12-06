<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDictionaryController;
use App\Http\Controllers\Admin\AdminStoryController;
use Illuminate\Support\Facades\Route;

// Welcome страница (доступна всем)
Route::get('/', [HomeController::class, 'welcome'])->name('welcome');

// Аутентификация
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Защищенные маршруты (только для авторизованных)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    
    // Словарь пользователя
    Route::get('/dictionary', [DictionaryController::class, 'index'])->name('dictionary.index');
    Route::post('/dictionary/add', [DictionaryController::class, 'addWord'])->name('dictionary.add');
    Route::delete('/dictionary/{wordId}', [DictionaryController::class, 'removeWord'])->name('dictionary.remove');
    
    // Рассказы
    Route::get('/stories', [StoryController::class, 'index'])->name('stories.index');
    Route::get('/stories/{id}', [StoryController::class, 'show'])->name('stories.show');
    Route::post('/stories/{id}/mark-as-read', [StoryController::class, 'markAsRead'])->name('stories.mark-as-read');
});

// Админ панель
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // CRUD для рассказов
    Route::resource('stories', AdminStoryController::class);
    
    // CRUD для словаря
    Route::resource('dictionary', AdminDictionaryController::class);
});