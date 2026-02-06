<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\KanjiController;
use App\Http\Controllers\AudioController;
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

// Обслуживание аудио файлов с поддержкой Range-запросов
Route::get('/audio/{path}', [AudioController::class, 'serve'])
    ->where('path', '.*')
    ->name('audio.serve');

// Защищенные маршруты (только для авторизованных)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    
    // Словарь: функционал на странице «Слова» (Кандзи), редирект старой страницы
    Route::get('/dictionary', function () {
        return redirect()->route('kanji.index', ['tab' => 'words']);
    })->name('dictionary.index');
    Route::post('/dictionary/add', [DictionaryController::class, 'addWord'])->name('dictionary.add');
    Route::get('/dictionary/lookup', [DictionaryController::class, 'lookupWord'])->name('dictionary.lookup');
    Route::get('/dictionary/{wordId}/data', [DictionaryController::class, 'getWordData'])->name('dictionary.data');
    Route::post('/dictionary/mark-completed', [DictionaryController::class, 'markAsCompleted'])->name('dictionary.mark-completed');
    Route::get('/dictionary/{wordId}/edit', [DictionaryController::class, 'edit'])->name('dictionary.edit');
    Route::put('/dictionary/{wordId}', [DictionaryController::class, 'update'])->name('dictionary.update');
    Route::delete('/dictionary/{wordId}', [DictionaryController::class, 'removeWord'])->name('dictionary.remove');
    
    // Изучение слов
    Route::get('/study', [StudyController::class, 'index'])->name('study.index');
    Route::get('/study/calendar', [StudyController::class, 'calendar'])->name('study.calendar');
    Route::post('/study/start', [StudyController::class, 'startStudying'])->name('study.start');
    Route::get('/study/get-next-word', [StudyController::class, 'getNextWord'])->name('study.get-next-word');
    Route::post('/study/check-answer', [StudyController::class, 'checkAnswer'])->name('study.check-answer');
    Route::get('/study/get-extra-words', [StudyController::class, 'getExtraWords'])->name('study.get-extra-words');
    
    // Настройки
    Route::post('/settings/update', [HomeController::class, 'updateSettings'])->name('settings.update');
    
    // Изучение кандзи
    Route::get('/kanji', [KanjiController::class, 'index'])->name('kanji.index');
    Route::get('/kanji/quiz', [KanjiController::class, 'quiz'])->name('kanji.quiz');
    Route::get('/kanji/word-quiz', [KanjiController::class, 'wordQuiz'])->name('kanji.word-quiz');
    Route::get('/kanji/get-question', [KanjiController::class, 'getQuestion'])->name('kanji.get-question');
    Route::get('/kanji/get-word-question', [KanjiController::class, 'getWordQuestion'])->name('kanji.get-word-question');
    Route::post('/kanji/submit-answer', [KanjiController::class, 'submitAnswer'])->name('kanji.submit-answer');
    Route::post('/kanji/submit-word-answer', [KanjiController::class, 'submitWordAnswer'])->name('kanji.submit-word-answer');
    Route::post('/kanji/mark-completed', [KanjiController::class, 'markAsCompleted'])->name('kanji.mark-completed');
    Route::post('/kanji/toggle-study-selection', [KanjiController::class, 'toggleStudySelection'])->name('kanji.toggle-study-selection');
    Route::post('/kanji/update-settings', [KanjiController::class, 'updateKanjiSettings'])->name('kanji.update-settings');
    Route::post('/kanji/quick-update', [KanjiController::class, 'quickUpdate'])->name('kanji.quick-update');
});