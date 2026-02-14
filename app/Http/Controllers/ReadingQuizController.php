<?php

namespace App\Http\Controllers;

use App\Models\GlobalDictionary;
use App\Models\ReadingQuizProgress;
use App\Models\ReadingQuizList;
use App\Models\ReadingQuizWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReadingQuizController extends Controller
{
    /**
     * Получить список всех активных слов для чтения (только из reading_quiz_words)
     */
    private function getAllWords()
    {
        $user = Auth::user();
        return ReadingQuizWord::where('user_id', $user->id)
            ->get()
            ->map(function ($word) {
                return [
                    'id' => $word->id,
                    'japanese_word' => $word->japanese_word,
                    'reading' => $word->reading,
                    'translation_ru' => $word->translation_ru,
                    'translation_en' => $word->translation_en,
                    'word_type' => $word->word_type,
                    'example_ru' => $word->example_ru,
                    'example_jp' => $word->example_jp,
                    'audio_path' => $word->audio_path,
                ];
            });
    }

    /**
     * SRS интервалы в днях
     */
    private function getSrsIntervalsDays(): array
    {
        // Индексы = level (0..10)
        // 0 = сразу/сегодня, 10 = очень редко (или "завершено")
        return [
            0 => 0,
            1 => 1,
            2 => 2,
            3 => 4,
            4 => 7,
            5 => 14,
            6 => 30,
            7 => 60,
            8 => 120,
            9 => 240,
            10 => 365,
        ];
    }

    /**
     * Расчет следующего повторения
     */
    private function calcNextReviewAt(int $newLevel, bool $isCorrect): \Illuminate\Support\Carbon
    {
        $now = now();
        if (!$isCorrect) {
            // "Скорое" повторение, но не сразу
            return $now->copy()->addHours(6);
        }

        $intervals = $this->getSrsIntervalsDays();
        $days = $intervals[$newLevel] ?? 0;
        return $now->copy()->addDays((int) $days);
    }

    /**
     * Построить варианты ответов
     */
    private function buildAnswerOptions(string $correct, \Illuminate\Support\Collection $pool, int $maxOptions = 6): array
    {
        $options = collect([$correct]);
        $poolUnique = $pool
            ->filter(fn ($x) => is_string($x) && trim($x) !== '')
            ->unique()
            ->reject(fn ($x) => $x === $correct)
            ->shuffle()
            ->values();

        foreach ($poolUnique as $candidate) {
            if ($options->count() >= $maxOptions) break;
            $options->push($candidate);
        }

        return $options->shuffle()->values()->toArray();
    }

    /**
     * Нормализовать ответ на русском
     */
    private function normalizeRuAnswer(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Проверить ответ
     */
    private function isAnswerCorrect(string $questionType, string $answer, string $correctAnswer): bool
    {
        $answer = trim($answer);
        $correctAnswer = trim($correctAnswer);

        if ($questionType === 'word_to_reading') {
            // Сравниваем как есть (это чтение на хирагане/катакане)
            return $answer === $correctAnswer;
        } else {
            // question_type === 'reading_to_word'
            // Сравниваем как есть (это японское слово)
            return $answer === $correctAnswer;
        }
    }

    /**
     * Главная страница квиза чтения с поддержкой списков
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Получаем все слова для чтения пользователя с их прогрессом
        $allWords = ReadingQuizWord::where('user_id', $user->id)->get();
        
        $userProgress = ReadingQuizProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('word_id');
        
        $now = now();
        
        // Преобразуем слова в массив с прогрессом
        $wordsWithProgress = $allWords->map(function ($word) use ($userProgress, $now) {
            $progress = $userProgress->get($word->id);
            $level = $progress ? (int) ($progress->level ?? 0) : 0;
            $isCompleted = $level >= 10;
            
            return [
                'id' => $word->id,
                'japanese_word' => $word->japanese_word,
                'reading' => $word->reading,
                'translation_ru' => $word->translation_ru,
                'translation_en' => $word->translation_en ?? '',
                'word_type' => $word->word_type ?? '',
                'example_ru' => $word->example_ru ?? '',
                'example_jp' => $word->example_jp ?? '',
                'audio_path' => $word->audio_path ?? '',
                'level' => $level,
                'last_reviewed_at' => $progress ? $progress->last_reviewed_at : null,
                'next_review_at' => $progress ? $progress->next_review_at : null,
                'is_due' => $progress ? (!$isCompleted && (!$progress->next_review_at || $progress->next_review_at->lte($now))) : true,
                'is_completed' => $isCompleted,
                'progress_percent' => min(100, max(0, ($level / 10) * 100)),
            ];
        })
        ->sortBy('progress_percent')
        ->values();
        
        // Получаем типы слов для фильтра
        $wordTypes = $allWords
            ->whereNotNull('word_type')
            ->where('word_type', '!=', '')
            ->pluck('word_type')
            ->sort()
            ->unique()
            ->values();
        
        // Статистика
        $totalWords = $allWords->count();
        $studiedWords = $userProgress->count();
        $completedWords = $userProgress
            ->filter(fn ($p) => (($p->level ?? 0) >= 10))
            ->count();
        $dueWords = $userProgress
            ->filter(function ($p) use ($now) {
                return (($p->level ?? 0) < 10) && (!$p->next_review_at || $p->next_review_at->lte($now));
            })
            ->count();
        
        return view('reading-quiz.index', compact(
            'wordsWithProgress',
            'wordTypes',
            'totalWords',
            'studiedWords',
            'completedWords',
            'dueWords'
        ));
    }

    /**
     * Страница квиза чтения
     */
    public function quiz(Request $request)
    {
        $count = (int) $request->get('count', 10);
        $listId = $request->get('list_id', null);
        $quizId = (string) Str::uuid();

        return view('reading-quiz.quiz', compact('count', 'quizId', 'listId'));
    }

    /**
     * Получить вопрос
     */
    public function getQuestion(Request $request)
    {
        $user = Auth::user();
        $count = (int) $request->get('count', 10);
        $quizId = (string) $request->get('quiz_id', '');
        $listId = $request->get('list_id', null);

        if ($quizId === '') {
            return response()->json(['error' => 'quiz_id обязателен'], 422);
        }

        // Если указан список, берём слова только из него
        if ($listId) {
            $list = ReadingQuizList::where('id', $listId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$list) {
                return response()->json(['error' => 'Список не найден или доступ запрещён'], 403);
            }
            
            $listWordIds = $list->getWords();
            $allWords = ReadingQuizWord::whereIn('id', $listWordIds)
                ->where('user_id', $user->id)
                ->get()
                ->map(function ($word) {
                    return [
                        'id' => $word->id,
                        'japanese_word' => $word->japanese_word,
                        'reading' => $word->reading,
                        'translation_ru' => $word->translation_ru,
                        'translation_en' => $word->translation_en,
                        'word_type' => $word->word_type,
                        'example_ru' => $word->example_ru,
                        'example_jp' => $word->example_jp,
                        'audio_path' => $word->audio_path,
                    ];
                });
        } else {
            $allWords = $this->getAllWords();
        }

        if ($allWords->isEmpty()) {
            return response()->json([
                'error' => 'Нет доступных слов для квиза',
                'no_more_questions' => true,
            ], 404);
        }

        // Получаем прогресс пользователя до квиза чтения
        $userProgress = ReadingQuizProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('word_id');

        $sessionKey = "reading_quiz.$quizId.asked";
        $asked = collect(session()->get($sessionKey, []))
            ->filter(fn ($id) => is_int($id) || (is_string($id) && is_numeric($id)))
            ->map(fn ($id) => (int)$id)
            ->unique()
            ->values();

        // Создаем список слов с их уровнями
        $wordsWithLevels = $allWords->map(function ($word) use ($userProgress) {
            $progress = $userProgress->get($word['id']);
            $level = $progress ? (int) $progress->level : 0;
            return [
                'id' => $word['id'],
                'japanese_word' => $word['japanese_word'],
                'reading' => $word['reading'],
                'translation_ru' => $word['translation_ru'],
                'translation_en' => $word['translation_en'],
                'word_type' => $word['word_type'],
                'example_ru' => $word['example_ru'],
                'example_jp' => $word['example_jp'],
                'audio_path' => $word['audio_path'],
                'level' => $level,
                'last_reviewed_at' => $progress ? $progress->last_reviewed_at : null,
                'next_review_at' => $progress ? $progress->next_review_at : null,
                'is_completed' => $level >= 10,
            ];
        });

        $now = now();
        
        // Фильтруем уже спрошенные слова
        $candidates = $wordsWithLevels
            ->reject(fn ($w) => $asked->contains($w['id']));
        
        // Логика фильтрации завершённых элементов
        if ($listId) {
            // При квизе со списком НЕ фильтруем завершённые слова
            // Повторяем всё что в списке
        } else {
            // При обычном квизе исключаем завершённые слова
            $candidates = $candidates->reject(fn ($w) => (bool) ($w['is_completed'] ?? false));
        }

        // Приоритет: то, что "пора" повторять (next_review_at <= now) или новое (next_review_at null)
        $due = $candidates
            ->filter(function ($w) use ($now) {
                return empty($w['next_review_at']) || ($w['next_review_at'] && $w['next_review_at']->lte($now));
            })
            ->sortBy([
                ['level', 'asc'],
                ['next_review_at', 'asc'],
                ['last_reviewed_at', 'asc'],
            ])
            ->values();

        // Если это квиз со списком И $due пуст, просто используем все $candidates
        if ($listId && $due->isEmpty()) {
            $pool = $candidates
                ->sortBy([
                    ['level', 'asc'],
                    ['last_reviewed_at', 'asc'],
                ])
                ->values();
        } else {
            $pool = $due->isNotEmpty() ? $due : $candidates
                ->sortBy([
                    ['level', 'asc'],
                    ['last_reviewed_at', 'asc'],
                ])
                ->values();
        }

        // Берём топ-N и рандомим внутри
        $wordToStudy = $pool
            ->take(max(1, min($count, $pool->count())))
            ->shuffle()
            ->first();

        if (!$wordToStudy) {
            return response()->json([
                'error' => 'Нет доступных слов для квиза чтения',
                'no_more_questions' => true,
            ], 404);
        }

        // Запоминаем, что это слово уже задано в рамках квиза
        $asked->push($wordToStudy['id']);
        session()->put($sessionKey, $asked->unique()->values()->toArray());

        // Определяем тип вопроса (50/50)
        $questionType = rand(0, 1) === 0 ? 'word_to_reading' : 'reading_to_word';
        $answerMode = 'choices'; // Всегда выбор вариантов ответа

        $questionId = (string) Str::uuid();
        session()->put("reading_quiz.$quizId.questions.$questionId", [
            'word_id' => $wordToStudy['id'],
            'question_type' => $questionType,
            'correct_answer' => $questionType === 'word_to_reading' ? $wordToStudy['reading'] : $wordToStudy['japanese_word'],
            'expires_at' => now()->addMinutes(30)->toIso8601String(),
        ]);

        // Если вопрос типа word_to_reading
        if ($questionType === 'word_to_reading') {
            // Вариант ответа - список чтений
            $answers = $this->buildAnswerOptions(
                $wordToStudy['reading'],
                $allWords->pluck('reading'),
                6
            );

            return response()->json([
                'quiz_id' => $quizId,
                'question_id' => $questionId,
                'question_type' => 'word_to_reading',
                'answer_mode' => $answerMode,
                'question' => $wordToStudy['japanese_word'],
                'answers' => $answers,
                'word_id' => $wordToStudy['id'],
                'translation_ru' => $wordToStudy['translation_ru'],
                'word_type' => $wordToStudy['word_type'],
                'example_jp' => $wordToStudy['example_jp'],
                'example_ru' => $wordToStudy['example_ru'],
                'user_level' => (int) ($wordToStudy['level'] ?? 0),
            ]);
        } else {
            // Если вопрос типа reading_to_word
            $answers = $this->buildAnswerOptions(
                $wordToStudy['japanese_word'],
                $allWords->pluck('japanese_word'),
                6
            );

            return response()->json([
                'quiz_id' => $quizId,
                'question_id' => $questionId,
                'question_type' => 'reading_to_word',
                'answer_mode' => $answerMode,
                'question' => $wordToStudy['reading'],
                'answers' => $answers,
                'word_id' => $wordToStudy['id'],
                'translation_ru' => $wordToStudy['translation_ru'],
                'word_type' => $wordToStudy['word_type'],
                'example_jp' => $wordToStudy['example_jp'],
                'example_ru' => $wordToStudy['example_ru'],
                'user_level' => (int) ($wordToStudy['level'] ?? 0),
            ]);
        }
    }

    /**
     * Отправить ответ
     */
    public function submitAnswer(Request $request)
    {
        $request->validate([
            'word_id' => 'required|integer',
            'answer' => 'required|string',
            'quiz_id' => 'required|string',
            'question_id' => 'required|string',
        ]);

        $user = Auth::user();
        $wordId = (int) $request->word_id;
        $answer = (string) $request->answer;
        $quizId = (string) $request->quiz_id;
        $questionId = (string) $request->question_id;

        $stored = session()->get("reading_quiz.$quizId.questions.$questionId");
        if (!$stored || !is_array($stored) || ($stored['word_id'] ?? null) !== $wordId) {
            return response()->json(['error' => 'Вопрос не найден или устарел'], 422);
        }

        $expiresAt = $stored['expires_at'] ?? null;
        if (is_string($expiresAt) && now()->gt(\Illuminate\Support\Carbon::parse($expiresAt))) {
            session()->forget("reading_quiz.$quizId.questions.$questionId");
            return response()->json(['error' => 'Вопрос устарел'], 422);
        }

        $correctAnswer = (string) ($stored['correct_answer'] ?? '');
        $questionType = (string) ($stored['question_type'] ?? '');
        if ($correctAnswer === '') {
            return response()->json(['error' => 'Некорректный вопрос'], 422);
        }
        if ($questionType !== 'word_to_reading' && $questionType !== 'reading_to_word') {
            return response()->json(['error' => 'Некорректный тип вопроса'], 422);
        }

        // Находим слово в БД (может быть из reading_quiz_words или global_dictionary)
        $word = ReadingQuizWord::find($wordId);
        if (!$word) {
            $word = GlobalDictionary::find($wordId);
        }
        if (!$word) {
            return response()->json(['error' => 'Слово не найдено'], 404);
        }

        // Получаем или создаем прогресс квиза чтения
        $progress = ReadingQuizProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'word_id' => $wordId,
            ],
            [
                'level' => 0,
                'is_completed' => false,
            ]
        );

        // Проверяем ответ
        $isCorrect = $this->isAnswerCorrect($questionType, (string) $answer, (string) $correctAnswer);

        if ($isCorrect) {
            $progress->level = min(10, $progress->level + 1);
        } else {
            $progress->level = max(0, $progress->level - 1);
        }

        $progress->last_reviewed_at = now();
        $progress->next_review_at = $this->calcNextReviewAt((int) $progress->level, $isCorrect);
        if ($progress->level >= 10) {
            $progress->is_completed = true;
        } else {
            $progress->is_completed = false;
        }
        $progress->save();

        // Запрещаем повторное использование вопроса
        session()->forget("reading_quiz.$quizId.questions.$questionId");

        return response()->json([
            'correct' => $isCorrect,
            'new_level' => $progress->level,
            'correct_answer' => $correctAnswer,
            'next_review_at' => $progress->next_review_at?->toIso8601String(),
            'japanese_word' => $word->japanese_word,
            'reading' => $word->reading,
        ]);
    }

    /**
     * Отметить слово как изученное в квизе чтения
     */
    public function markAsCompleted(Request $request)
    {
        $validated = $request->validate([
            'word_id' => ['required', 'integer', 'exists:global_dictionary,id'],
        ]);

        $user = Auth::user();
        $wordId = $validated['word_id'];

        // Получаем или создаем прогресс
        $progress = ReadingQuizProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'word_id' => $wordId,
            ],
            [
                'level' => 0,
                'is_completed' => false,
            ]
        );

        // Отмечаем как изученное
        $progress->is_completed = true;
        $progress->level = 10;
        $progress->last_reviewed_at = now();
        $progress->next_review_at = null;
        $progress->save();

        return response()->json(['success' => true, 'message' => 'Слово отмечено как изученное']);
    }

    /**
     * Получить прогресс пользователя для всех слов
     */
    public function getProgress()
    {
        $user = Auth::user();

        // Получаем все слова
        $allWords = $this->getAllWords();
        
        // Получаем прогресс пользователя
        $userProgress = ReadingQuizProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('word_id');

        // Объединяем данные
        $wordsWithProgress = $allWords->map(function ($word) use ($userProgress) {
            $progress = $userProgress->get($word['id']);
            $level = $progress ? (int) $progress->level : 0;
            $progressPercent = min(100, max(0, ($level / 10) * 100));

            return [
                'word_id' => $word['id'],
                'level' => $level,
                'progress_percent' => $progressPercent,
                'is_completed' => $progress ? (bool) $progress->is_completed : false,
            ];
        });

        // Общая статистика
        $totalWords = $wordsWithProgress->count();
        $completedWords = $wordsWithProgress->filter(fn($w) => $w['is_completed'])->count();
        $avgProgress = $totalWords > 0 ? (int) round($wordsWithProgress->avg('progress_percent')) : 0;

        return response()->json([
            'success' => true,
            'total_words' => $totalWords,
            'completed_words' => $completedWords,
            'average_progress' => $avgProgress,
            'words' => $wordsWithProgress->toArray(),
        ]);
    }

    /**
     * Получить все доступные слова для добавления в список (только слова для чтения)
     */
    public function getAvailableWords()
    {
        $user = Auth::user();
        $words = ReadingQuizWord::where('user_id', $user->id)
            ->limit(1000)  // Ограничиваем для производительности
            ->get()
            ->map(function ($word) {
                return [
                    'id' => $word->id,
                    'japanese_word' => $word->japanese_word,
                    'reading' => $word->reading,
                    'translation_ru' => $word->translation_ru,
                    'translation_en' => $word->translation_en,
                    'word_type' => $word->word_type,
                ];
            });

        return response()->json(['success' => true, 'words' => $words]);
    }

    /**
     * Сбросить сессию квиза (очистить уже спрошенные слова и вопросы)
     */
    public function resetSession(Request $request)
    {
        $quizId = (string) $request->get('quiz_id', '');
        
        if (!$quizId) {
            return response()->json(['error' => 'quiz_id обязателен'], 422);
        }

        // Очищаем все данные сессии для этого квиза
        $sessionKey = "reading_quiz.$quizId";
        session()->forget($sessionKey);

        return response()->json(['success' => true, 'message' => 'Session reset']);
    }
    
}
