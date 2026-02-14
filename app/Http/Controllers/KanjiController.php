<?php

namespace App\Http\Controllers;

use App\Models\GlobalDictionary;
use App\Models\Kanji;
use App\Models\KanjiStudyList;
use App\Models\KanjiStudyProgress;
use App\Models\WordStudyProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KanjiController extends Controller
{
    /**
     * Получить список всех активных кандзи
     */
    private function getAllKanji($jlptLevel = null)
    {
        $query = Kanji::where('is_active', true);
        
        if ($jlptLevel && $jlptLevel !== 'any') {
            $query->where('jlpt_level', $jlptLevel);
        }
        
        return $query->get()
            ->map(function ($kanji) {
                return [
                    'kanji' => $kanji->kanji,
                    'translation' => $kanji->translation_ru,
                    'reading' => $kanji->reading,
                    'description' => $kanji->description,
                    'jlpt_level' => $kanji->jlpt_level,
                ];
            });
    }
    
    // Базовый список кандзи для изучения (fallback, если в БД нет данных)
    private const KANJI_LIST_FALLBACK = [
        ['kanji' => '今', 'translation' => 'сейчас'],
        ['kanji' => '本', 'translation' => 'книга'],
        ['kanji' => '日', 'translation' => 'день'],
        ['kanji' => '月', 'translation' => 'месяц'],
        ['kanji' => '年', 'translation' => 'год'],
        ['kanji' => '人', 'translation' => 'человек'],
        ['kanji' => '大', 'translation' => 'большой'],
        ['kanji' => '小', 'translation' => 'маленький'],
        ['kanji' => '中', 'translation' => 'середина'],
        ['kanji' => '上', 'translation' => 'верх'],
        ['kanji' => '下', 'translation' => 'низ'],
        ['kanji' => '水', 'translation' => 'вода'],
        ['kanji' => '火', 'translation' => 'огонь'],
        ['kanji' => '木', 'translation' => 'дерево'],
        ['kanji' => '金', 'translation' => 'золото'],
        ['kanji' => '土', 'translation' => 'земля'],
        ['kanji' => '山', 'translation' => 'гора'],
        ['kanji' => '川', 'translation' => 'река'],
        ['kanji' => '田', 'translation' => 'поле'],
        ['kanji' => '口', 'translation' => 'рот'],
        ['kanji' => '目', 'translation' => 'глаз'],
        ['kanji' => '耳', 'translation' => 'ухо'],
        ['kanji' => '手', 'translation' => 'рука'],
        ['kanji' => '足', 'translation' => 'нога'],
        ['kanji' => '心', 'translation' => 'сердце'],
        ['kanji' => '車', 'translation' => 'машина'],
        ['kanji' => '電', 'translation' => 'электричество'],
        ['kanji' => '話', 'translation' => 'разговор'],
        ['kanji' => '語', 'translation' => 'язык'],
        ['kanji' => '学', 'translation' => 'учение'],
        ['kanji' => '校', 'translation' => 'школа'],
        ['kanji' => '生', 'translation' => 'жизнь'],
        ['kanji' => '食', 'translation' => 'еда'],
        ['kanji' => '飲', 'translation' => 'пить'],
        ['kanji' => '見', 'translation' => 'видеть'],
        ['kanji' => '聞', 'translation' => 'слышать'],
        ['kanji' => '読', 'translation' => 'читать'],
        ['kanji' => '書', 'translation' => 'писать'],
        ['kanji' => '行', 'translation' => 'идти'],
        ['kanji' => '来', 'translation' => 'приходить'],
        ['kanji' => '帰', 'translation' => 'возвращаться'],
        ['kanji' => '買', 'translation' => 'покупать'],
        ['kanji' => '売', 'translation' => 'продавать'],
        ['kanji' => '新', 'translation' => 'новый'],
        ['kanji' => '古', 'translation' => 'старый'],
        ['kanji' => '高', 'translation' => 'высокий'],
        ['kanji' => '低', 'translation' => 'низкий'],
        ['kanji' => '長', 'translation' => 'длинный'],
        ['kanji' => '短', 'translation' => 'короткий'],
        ['kanji' => '多', 'translation' => 'много'],
        ['kanji' => '少', 'translation' => 'мало'],
    ];

    /**
     * Главная страница изучения кандзи
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $jlptLevel = (string) $request->get('jlpt_level', '5'); // default N5
        $useKanjiSelection = (bool) ($user->use_kanji_selection ?? false);
        
        // Получаем все кандзи с прогрессом пользователя
        $query = Kanji::where('is_active', true);

        if ($jlptLevel !== 'any' && $jlptLevel !== '') {
            $query->where('jlpt_level', $jlptLevel);
        }
        
        // Фильтр поиска
        $search = $request->get('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('translation_ru', 'like', "%{$search}%")
                  ->orWhere('reading', 'like', "%{$search}%");
            });
        }
        
        $allKanji = $query->get();
        $userProgress = KanjiStudyProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('kanji');

        $now = now();
        
        // Группируем кандзи по уровням JLPT
        $kanjiByLevel = $allKanji->groupBy(function ($kanji) {
            return $kanji->jlpt_level ? "N{$kanji->jlpt_level}" : 'Без уровня';
        })->map(function ($kanjiGroup, $level) use ($userProgress, $now) {
            return $kanjiGroup->map(function ($kanji) use ($userProgress, $now) {
                $progress = $userProgress->get($kanji->kanji);
                $kanjiLevel = $progress ? (int) $progress->level : 0;
                $isCompleted = $kanjiLevel >= 10;
                return [
                    'kanji' => $kanji->kanji,
                    'translation' => $kanji->translation_ru,
                    'reading' => $kanji->reading,
                    'jlpt_level' => $kanji->jlpt_level,
                    'level' => $kanjiLevel,
                    'last_reviewed_at' => $progress ? $progress->last_reviewed_at : null,
                    'next_review_at' => $progress ? $progress->next_review_at : null,
                    'is_due' => $progress ? (!$isCompleted && (!$progress->next_review_at || $progress->next_review_at->lte($now))) : true,
                    'is_completed' => $isCompleted,
                    'is_selected_for_study' => $progress ? ($progress->is_selected_for_study ?? false) : false,
                    'image_path' => $kanji->image_path,
                    'mnemonic' => $kanji->mnemonic,
                    'description' => $kanji->description, // Примеры слов
                ];
            })
            ->sortBy([
                ['is_completed', 'desc'],
                ['level', 'desc'],
                ['last_reviewed_at', 'desc'],
            ])
            ->values();
        });
        
        // Сортируем уровни: N5 -> N4 -> N3 -> N2 -> N1 -> Без уровня
        $sortedKanjiByLevel = collect();
        $levelOrder = ['N5', 'N4', 'N3', 'N2', 'N1', 'Без уровня'];
        foreach ($levelOrder as $level) {
            if ($kanjiByLevel->has($level)) {
                $sortedKanjiByLevel->put($level, $kanjiByLevel->get($level));
            }
        }
        // Добавляем остальные уровни, если есть
        foreach ($kanjiByLevel as $level => $kanjiList) {
            if (!in_array($level, $levelOrder)) {
                $sortedKanjiByLevel->put($level, $kanjiList);
            }
        }
        
        // Статистика
        $totalKanji = Kanji::where('is_active', true)->count();
        $studiedKanji = $userProgress->count();
        $completedKanji = $userProgress
            ->filter(fn ($p) => (($p->level ?? 0) >= 10))
            ->count();
        $dueKanji = $userProgress
            ->filter(function ($p) use ($now) {
                return (($p->level ?? 0) < 10) && (!$p->next_review_at || $p->next_review_at->lte($now));
            })
            ->count();
        
        $isAdmin = $user->isAdmin();

        // Слова из словаря пользователя для раздела «Слова»
        $wordsQuery = $user->dictionary();
        $wordSearch = $request->get('word_search');
        $wordTypeFilter = $request->get('word_type');
        if ($wordSearch) {
            $wordsQuery->where(function ($q) use ($wordSearch) {
                $q->where('japanese_word', 'like', "%{$wordSearch}%")
                    ->orWhere('reading', 'like', "%{$wordSearch}%")
                    ->orWhere('translation_ru', 'like', "%{$wordSearch}%");
            });
        }
        if ($wordTypeFilter && $wordTypeFilter !== '') {
            $wordsQuery->where('word_type', $wordTypeFilter);
        }
        $wordsCollection = $wordsQuery->get();

        $wordProgressMap = WordStudyProgress::where('user_id', $user->id)
            ->whereIn('word_id', $wordsCollection->pluck('id'))
            ->get()
            ->keyBy('word_id');

        $wordsList = $wordsCollection->map(function ($word) use ($wordProgressMap) {
            $progress = $wordProgressMap->get($word->id);
            $level = $progress ? (int) ($progress->level ?? 0) : 0;
            $daysStudied = $progress ? (int) ($progress->days_studied ?? 0) : 0;
            $progressPercent = min(100, max(0, (max($level, $daysStudied) / 10) * 100));
            return [
                'id' => $word->id,
                'japanese_word' => $word->japanese_word,
                'reading' => $word->reading ?? '',
                'translation_ru' => $word->translation_ru,
                'word_type' => $word->word_type ?? '',
                'level' => $level,
                'days_studied' => $daysStudied,
                'progress_percent' => $progressPercent,
                'is_completed' => $progress ? (bool) ($progress->is_completed ?? false) : false,
                'next_review_at' => $progress ? $progress->next_review_at : null,
                'in_user_dictionary' => true,
            ];
        })
        // Сортировка: от меньшего процента изучения к большему
        ->sortBy('progress_percent')
        ->values();
        $wordTypes = $user->dictionary()->whereNotNull('word_type')->where('word_type', '!=', '')->distinct()->pluck('word_type')->sort()->values();

        return view('kanji.index', compact(
            'sortedKanjiByLevel',
            'totalKanji',
            'studiedKanji',
            'completedKanji',
            'dueKanji',
            'search',
            'isAdmin',
            'jlptLevel',
            'useKanjiSelection',
            'wordsList',
            'wordTypes',
            'wordSearch',
            'wordTypeFilter'
        ));
    }

    /**
     * Страница квиза по кандзи
     */
    public function quiz(Request $request)
    {
        $count = (int) $request->get('count', 10);
        $count = max(1, min(50, $count)); // Ограничиваем от 1 до 50
        $jlptLevel = $request->get('jlpt_level', 'any'); // any, 5, 4, 3, 2, 1
        $forceInputMode = (bool) $request->get('force_input_mode', false); // Принудительный режим ввода
        $listId = $request->get('list_id', null); // ID списка для фильтрации кандзи
        $quizId = (string) Str::uuid();
        
        return view('kanji.quiz', compact('count', 'jlptLevel', 'forceInputMode', 'listId', 'quizId'));
    }

    /**
     * Страница квиза по словам (тот же формат, что и квиз по кандзи)
     */
    public function wordQuiz(Request $request)
    {
        $count = (int) $request->get('count', 10);
        $count = max(1, min(50, $count));
        $wordType = (string) $request->get('word_type', '');
        $listId = $request->get('list_id', null);
        $quizId = (string) Str::uuid();
        return view('kanji.word-quiz', compact('count', 'wordType', 'quizId', 'listId'));
    }

    private function getSrsIntervalsDays(): array
    {
        // Индексы = level (0..10)
        // 0 = сразу/сегодня, 10 = очень редко (или “завершено”)
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

    private function calcNextReviewAt(int $newLevel, bool $isCorrect): \Illuminate\Support\Carbon
    {
        $now = now();
        if (!$isCorrect) {
            // “Скорое” повторение, но не сразу (чтобы не было петли на одном кандзи)
            return $now->copy()->addHours(6);
        }

        $intervals = $this->getSrsIntervalsDays();
        $days = $intervals[$newLevel] ?? 0;
        return $now->copy()->addDays((int) $days);
    }

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

    private function normalizeRuAnswer(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return mb_strtolower($value, 'UTF-8');
    }

    private function isAnswerCorrect(string $questionType, string $answer, string $correctAnswer, ?string $alternativeTranslations = null): bool
    {
        $answer = trim($answer);
        $correctAnswer = trim($correctAnswer);

        if ($questionType === 'kanji_to_ru') {
            $a = $this->normalizeRuAnswer($answer);
            $c = $this->normalizeRuAnswer($correctAnswer);
            if ($a === $c) return true;

            // Если в переводе указано несколько вариантов — принимаем любой
            $parts = preg_split('/[,;\/]/u', $correctAnswer) ?: [];
            foreach ($parts as $p) {
                if ($this->normalizeRuAnswer($p) === $a) return true;
            }
            
            // Проверяем альтернативные переводы из БД
            if ($alternativeTranslations) {
                $alternatives = preg_split('/[,;\/\n]/u', $alternativeTranslations) ?: [];
                foreach ($alternatives as $alt) {
                    $alt = trim($alt);
                    if ($alt !== '' && $this->normalizeRuAnswer($alt) === $a) {
                        return true;
                    }
                }
            }
            
            return false;
        }

        // ru_to_kanji: требуем точное совпадение символов (после trim)
        return $answer === $correctAnswer;
    }

    /**
     * Получить вопрос для квиза
     */
    public function getQuestion(Request $request)
    {
        $user = Auth::user();
        $count = (int) $request->get('count', 10);
        $jlptLevel = $request->get('jlpt_level', 'any');
        $forceInputMode = (bool) $request->get('force_input_mode', false);
        $quizId = (string) $request->get('quiz_id', '');
        $listId = $request->get('list_id', null);
        if ($quizId === '') {
            return response()->json(['error' => 'quiz_id обязателен'], 422);
        }
        
        // Если указан список, берём кандзи только из него
        if ($listId) {
            $list = KanjiStudyList::where('id', $listId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$list) {
                return response()->json(['error' => 'Список не найден или доступ запрещён'], 403);
            }
            
            $listKanjis = $list->getKanjis();
            $allKanji = Kanji::whereIn('kanji', $listKanjis)
                ->where('is_active', true)
                ->get()
                ->map(function ($kanji) {
                    return [
                        'kanji' => $kanji->kanji,
                        'translation' => $kanji->translation_ru,
                        'reading' => $kanji->reading,
                        'description' => $kanji->description,
                        'jlpt_level' => $kanji->jlpt_level,
                    ];
                });
        } else {
            $allKanji = $this->getAllKanji($jlptLevel);
        }
        
        // For answer options, always use ALL kanji (regardless of list)
        $allKanjiForAnswers = $this->getAllKanji($jlptLevel);
        
        if ($allKanji->isEmpty()) {
            $allKanji = collect(self::KANJI_LIST_FALLBACK);
        }
        if ($allKanjiForAnswers->isEmpty()) {
            $allKanjiForAnswers = collect(self::KANJI_LIST_FALLBACK);
        }
        
        // Получаем все кандзи с прогрессом пользователя
        $userProgress = KanjiStudyProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('kanji');

        $sessionKey = "kanji_quiz.$quizId.asked";
        $asked = collect(session()->get($sessionKey, []))
            ->filter(fn ($k) => is_string($k) && $k !== '')
            ->unique()
            ->values();
        
        $useKanjiSelection = (bool) ($user->use_kanji_selection ?? false);
        // Проверяем, есть ли выбранные для изучения кандзи
        $selectedForStudy = $useKanjiSelection
            ? $userProgress->filter(fn ($p) => (bool) ($p->is_selected_for_study ?? false))
            : collect();
        
        // Создаем список всех кандзи с их уровнями
        $kanjiWithLevels = $allKanji->map(function ($kanji) use ($userProgress) {
            $progress = $userProgress->get($kanji['kanji']);
            $level = $progress ? (int) $progress->level : 0;
            return [
                'kanji' => $kanji['kanji'],
                'translation' => $kanji['translation'],
                'reading' => $kanji['reading'] ?? null,
                'description' => $kanji['description'] ?? null,
                'level' => $level,
                'last_reviewed_at' => $progress ? $progress->last_reviewed_at : null,
                'next_review_at' => $progress ? $progress->next_review_at : null,
                'is_completed' => $level >= 10,
                'is_selected_for_study' => $progress ? (bool) $progress->is_selected_for_study : false,
            ];
        });

        $now = now();
        $candidates = $kanjiWithLevels
            ->reject(fn ($k) => $asked->contains($k['kanji']));
        
        // Логика фильтрации завершённых элементов
        if ($listId) {
            // При квизе со списком: исключаем 100% элементы только если сам список не на 100%
            $totalInList = $kanjiWithLevels->count();
            $completedInList = $kanjiWithLevels->filter(fn ($k) => (bool) ($k['is_completed'] ?? false))->count();
            $listIsFullyComplete = ($totalInList > 0 && $totalInList === $completedInList);
            
            // Если список НЕ полностью завершен, исключаем 100% элементы
            if (!$listIsFullyComplete) {
                $candidates = $candidates->reject(fn ($k) => (bool) ($k['is_completed'] ?? false));
            }
        } else {
            // При обычном квизе исключаем завершённые кандзи
            $candidates = $candidates->reject(fn ($k) => (bool) ($k['is_completed'] ?? false));
        }
        
        // Если включен режим выбора и есть выбранные кандзи — фильтруем только по ним
        if ($useKanjiSelection && $selectedForStudy->isNotEmpty()) {
            $candidates = $candidates->filter(fn ($k) => (bool) ($k['is_selected_for_study'] ?? false));
        }

        // Приоритет: то, что “пора” повторять (next_review_at <= now) или новое (next_review_at null)
        $due = $candidates
            ->filter(function ($k) use ($now) {
                return empty($k['next_review_at']) || ($k['next_review_at'] && $k['next_review_at']->lte($now));
            })
            ->sortBy([
                ['level', 'asc'],
                ['next_review_at', 'asc'],
                ['last_reviewed_at', 'asc'],
            ])
            ->values();

        $pool = $due->isNotEmpty() ? $due : $candidates
            ->sortBy([
                ['level', 'asc'],
                ['last_reviewed_at', 'asc'],
            ])
            ->values();

        // Немного разнообразия: берём топ-N и рандомим внутри
        $kanjiToStudy = $pool
            ->take(max(1, min($count, $pool->count())))
            ->shuffle()
            ->first();
        
        if (!$kanjiToStudy) {
            return response()->json([
                'error' => 'Нет доступных кандзи для изучения',
                'no_more_questions' => true,
            ], 404);
        }

        // Запоминаем, что этот кандзи уже задан в рамках квиза
        $asked->push($kanjiToStudy['kanji']);
        session()->put($sessionKey, $asked->unique()->values()->toArray());
        
        // Определяем тип вопроса (50/50)
        $questionType = rand(0, 1) === 0 ? 'kanji_to_ru' : 'ru_to_kanji';
        
        // Определяем режим ответа
        // Если список установлен на "только множественный выбор", всегда используем 'choices'
        $multipleChoiceOnly = false;
        if ($listId) {
            $list = KanjiStudyList::where('id', $listId)
                ->where('user_id', $user->id)
                ->first();
            if ($list) {
                $multipleChoiceOnly = (bool) $list->multiple_choice_only;
            }
        }
        
        $answerMode = $multipleChoiceOnly 
            ? 'choices' 
            : ($forceInputMode || ((int) ($kanjiToStudy['level'] ?? 0)) >= 5 ? 'input' : 'choices');
        
        // Генерируем варианты ответов
        $correctAnswer = $kanjiToStudy['translation'];
        
        // Получаем модель кандзи для получения изображения, мнемоники и альтернативных переводов
        $kanjiModel = Kanji::where('kanji', $kanjiToStudy['kanji'])->where('is_active', true)->first();
        $imagePath = $kanjiModel ? $kanjiModel->image_path : null;
        $mnemonic = $kanjiModel ? $kanjiModel->mnemonic : null;
        $reading = $kanjiModel ? $kanjiModel->reading : ($kanjiToStudy['reading'] ?? null);
        $description = $kanjiModel ? $kanjiModel->description : ($kanjiToStudy['description'] ?? null);
        $alternativeTranslations = $kanjiModel ? $kanjiModel->alternative_translations : null;
        
        $questionId = (string) Str::uuid();
        session()->put("kanji_quiz.$quizId.questions.$questionId", [
            'kanji' => $kanjiToStudy['kanji'],
            'question_type' => $questionType,
            'correct_answer' => $questionType === 'kanji_to_ru' ? $correctAnswer : $kanjiToStudy['kanji'],
            'alternative_translations' => $alternativeTranslations,
            'expires_at' => now()->addMinutes(30)->toIso8601String(),
        ]);
        
        // Если вопрос типа kanji_to_ru
        if ($questionType === 'kanji_to_ru') {
            $answers = $answerMode === 'choices'
                ? $this->buildAnswerOptions($correctAnswer, $allKanjiForAnswers->pluck('translation'), 6)
                : [];
            
            return response()->json([
                'quiz_id' => $quizId,
                'question_id' => $questionId,
                'question_type' => 'kanji_to_ru',
                'answer_mode' => $answerMode,
                'question' => $kanjiToStudy['kanji'],
                'answers' => $answers,
                'kanji' => $kanjiToStudy['kanji'],
                'image_path' => $imagePath,
                'mnemonic' => $mnemonic,
                'reading' => $reading,
                'description' => $description,
                'user_level' => (int) ($kanjiToStudy['level'] ?? 0),
            ]);
        } else {
            // Если вопрос типа ru_to_kanji
            $answers = $answerMode === 'choices'
                ? $this->buildAnswerOptions($kanjiToStudy['kanji'], $allKanjiForAnswers->pluck('kanji'), 6)
                : [];
            
            return response()->json([
                'quiz_id' => $quizId,
                'question_id' => $questionId,
                'question_type' => 'ru_to_kanji',
                'answer_mode' => $answerMode,
                'question' => $correctAnswer,
                'answers' => $answers,
                'kanji' => $kanjiToStudy['kanji'],
                'image_path' => null, // Не показываем изображение для ru_to_kanji
                'mnemonic' => $mnemonic,
                'reading' => $reading,
                'description' => $description,
                'user_level' => (int) ($kanjiToStudy['level'] ?? 0),
            ]);
        }
    }

    /**
     * Отправить ответ
     */
    public function submitAnswer(Request $request)
    {
        $request->validate([
            'kanji' => 'required|string',
            'answer' => 'required|string',
            'quiz_id' => 'required|string',
            'question_id' => 'required|string',
        ]);
        
        $user = Auth::user();
        $kanji = $request->kanji;
        $answer = $request->answer;
        $quizId = (string) $request->quiz_id;
        $questionId = (string) $request->question_id;

        $stored = session()->get("kanji_quiz.$quizId.questions.$questionId");
        if (!$stored || !is_array($stored) || ($stored['kanji'] ?? null) !== $kanji) {
            return response()->json(['error' => 'Вопрос не найден или устарел'], 422);
        }

        $expiresAt = $stored['expires_at'] ?? null;
        if (is_string($expiresAt) && now()->gt(\Illuminate\Support\Carbon::parse($expiresAt))) {
            session()->forget("kanji_quiz.$quizId.questions.$questionId");
            return response()->json(['error' => 'Вопрос устарел'], 422);
        }

        $correctAnswer = (string) ($stored['correct_answer'] ?? '');
        $questionType = (string) ($stored['question_type'] ?? '');
        $alternativeTranslations = $stored['alternative_translations'] ?? null;
        if ($correctAnswer === '') {
            return response()->json(['error' => 'Некорректный вопрос'], 422);
        }
        if ($questionType !== 'kanji_to_ru' && $questionType !== 'ru_to_kanji') {
            return response()->json(['error' => 'Некорректный тип вопроса'], 422);
        }
        
        // Находим кандзи в БД или в fallback списке
        $kanjiModel = Kanji::where('kanji', $kanji)->where('is_active', true)->first();
        if (!$kanjiModel) {
            $allKanji = $this->getAllKanji();
            if ($allKanji->isEmpty()) {
                $allKanji = collect(self::KANJI_LIST_FALLBACK);
            }
            $kanjiData = $allKanji->firstWhere('kanji', $kanji);
            if (!$kanjiData) {
                return response()->json(['error' => 'Кандзи не найден'], 404);
            }
            $translation = $kanjiData['translation'];
        } else {
            $translation = $kanjiModel->translation_ru;
        }
        
        // Получаем или создаем прогресс
        $progress = KanjiStudyProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'kanji' => $kanji,
            ],
            [
                'translation_ru' => $translation,
                'level' => 0,
                'is_completed' => false,
            ]
        );
        
        // Проверяем ответ (с учетом альтернативных переводов)
        $isCorrect = $this->isAnswerCorrect($questionType, (string) $answer, (string) $correctAnswer, $alternativeTranslations);
        
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
        session()->forget("kanji_quiz.$quizId.questions.$questionId");
        
        return response()->json([
            'correct' => $isCorrect,
            'new_level' => $progress->level,
            'correct_answer' => $correctAnswer, // возвращаем только после ответа (для UI)
            'next_review_at' => $progress->next_review_at?->toIso8601String(),
        ]);
    }

    /**
     * Отметить кандзи как изученное
     */
    public function markAsCompleted(Request $request)
    {
        $validated = $request->validate([
            'kanji' => ['required', 'string'],
        ]);

        $user = Auth::user();
        
        // Получаем или создаем прогресс
        $progress = KanjiStudyProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'kanji' => $validated['kanji'],
            ],
            [
                'translation_ru' => Kanji::where('kanji', $validated['kanji'])->value('translation_ru') ?? '',
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

        return response()->json(['success' => true, 'message' => 'Кандзи отмечен как изученный']);
    }

    /**
     * Переключить статус выбора кандзи для изучения
     */
    public function toggleStudySelection(Request $request)
    {
        $validated = $request->validate([
            'kanji' => ['required', 'string'],
        ]);

        $user = Auth::user();
        
        // Получаем или создаем прогресс
        $progress = KanjiStudyProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'kanji' => $validated['kanji'],
            ],
            [
                'translation_ru' => Kanji::where('kanji', $validated['kanji'])->value('translation_ru') ?? '',
                'level' => 0,
                'is_completed' => false,
                'is_selected_for_study' => false,
            ]
        );

        // Переключаем статус
        $progress->is_selected_for_study = !$progress->is_selected_for_study;
        $progress->save();

        return response()->json([
            'success' => true, 
            'is_selected' => $progress->is_selected_for_study,
            'message' => $progress->is_selected_for_study ? 'Кандзи добавлен в изучение' : 'Кандзи убран из изучения'
        ]);
    }

    /**
     * Получить данные о кандзи для отображения в модальном окне
     */
    public function getKanji(Request $request)
    {
        $user = Auth::user();
        $kanjiChar = (string) $request->get('kanji', '');
        
        if ($kanjiChar === '') {
            return response()->json(['error' => 'Кандзи не указано'], 422);
        }
        
        $kanji = Kanji::where('kanji', $kanjiChar)->where('is_active', true)->first();
        
        if (!$kanji) {
            return response()->json(['error' => 'Кандзи не найдено'], 404);
        }
        
        // Получаем прогресс пользователя для этого кандзи
        $progress = KanjiStudyProgress::where('user_id', $user->id)
            ->where('kanji', $kanjiChar)
            ->first();
        
        return response()->json([
            'kanji' => $kanjiChar,
            'translation' => $kanji->translation_ru,
            'reading' => $kanji->reading,
            'jlpt_level' => $kanji->jlpt_level,
            'image_path' => $kanji->image_path,
            'mnemonic' => $kanji->mnemonic,
            'description' => $kanji->description,
            'level' => $progress ? (int) ($progress->level ?? 0) : 0,
            'last_reviewed_at' => $progress ? ($progress->last_reviewed_at ? $progress->last_reviewed_at->format('d.m.Y') : '') : '',
            'next_review_at' => $progress ? ($progress->next_review_at ? $progress->next_review_at->format('d.m.Y H:i') : '') : '',
            'is_completed' => $progress ? (bool) ($progress->is_completed ?? false) : false,
        ]);
    }

    /**
     * Настройки страницы/логики кандзи (например, включить режим выбора кандзи для квиза)
     */
    public function updateKanjiSettings(Request $request)
    {
        $validated = $request->validate([
            'use_kanji_selection' => ['required', 'boolean'],
        ]);

        $user = Auth::user();
        $user->use_kanji_selection = (bool) $validated['use_kanji_selection'];
        $user->save();

        return response()->json([
            'success' => true,
            'use_kanji_selection' => $user->use_kanji_selection,
        ]);
    }

    /**
     * Квиз по словам: получить вопрос (формат как у кандзи)
     */
    public function getWordQuestion(Request $request)
    {
        $user = Auth::user();
        $count = (int) $request->get('count', 10);
        $count = max(1, min(50, $count));
        $wordType = $request->get('word_type', '');
        $quizId = (string) $request->get('quiz_id', '');
        if ($quizId === '') {
            return response()->json(['error' => 'quiz_id обязателен'], 422);
        }

        $listId = $request->get('list_id', null);

        // Load ALL words for answer options (before filtering by list)
        $allWordsForAnswersQuery = $user->dictionary();
        if ($wordType !== '') {
            $allWordsForAnswersQuery->where('word_type', $wordType);
        }
        $allWordsForAnswers = $allWordsForAnswersQuery->get();

        // Base: Get word IDs from user's dictionary
        $wordIdsFromDictionary = $user->dictionary()
            ->when($wordType !== '', fn($q) => $q->where('word_type', $wordType))
            ->pluck('global_dictionary.id')
            ->toArray();

        // If list_id provided, narrow to words from that list
        if ($listId) {
            $list = \App\Models\WordStudyList::where('id', $listId)->where('user_id', $user->id)->first();
            if (!$list) {
                return response()->json(['error' => 'Список не найден или доступ запрещён'], 404);
            }
            $listWordIds = $list->getWords();
            if (empty($listWordIds)) {
                return response()->json([
                    'error' => 'В выбранном списке нет слов',
                    'no_more_questions' => true,
                ], 404);
            }
            // Intersect: только слова которые и в словаре пользователя И в списке
            $wordIdsToUse = array_intersect($wordIdsFromDictionary, $listWordIds);
        } else {
            $wordIdsToUse = $wordIdsFromDictionary;
        }

        if (empty($wordIdsToUse)) {
            return response()->json([
                'error' => 'Нет слов для квиза. Добавьте слова в словарь или снимите фильтр по типу.',
                'no_more_questions' => true,
            ], 404);
        }

        // Get all words by their IDs
        $allWords = \App\Models\GlobalDictionary::whereIn('id', $wordIdsToUse)->get();

        $userProgress = WordStudyProgress::where('user_id', $user->id)
            ->whereIn('word_id', $allWords->pluck('id'))
            ->get()
            ->keyBy('word_id');

        $sessionKey = "word_quiz.{$quizId}.asked";
        $asked = collect(session()->get($sessionKey, []))
            ->filter(fn ($id) => is_numeric($id))
            ->unique()
            ->values();

        $wordsWithLevels = $allWords->map(function ($word) use ($userProgress) {
            $progress = $userProgress->get($word->id);
            return [
                'word_id' => $word->id,
                'japanese_word' => $word->japanese_word,
                'reading' => $word->reading ?? '',
                'translation_ru' => $word->translation_ru,
                'level' => $progress ? (int) ($progress->level ?? 0) : 0,
                'next_review_at' => $progress ? $progress->next_review_at : null,
                'is_completed' => $progress ? (bool) ($progress->is_completed ?? false) : false,
            ];
        });

        $now = now();
        $candidates = $wordsWithLevels
            ->reject(fn ($w) => $asked->contains($w['word_id']));
        
        // Логика фильтрации завершённых элементов
        if ($listId) {
            // При квизе со списком: исключаем 100% элементы только если сам список не на 100%
            $totalInList = $wordsWithLevels->count();
            $completedInList = $wordsWithLevels->filter(fn ($w) => $w['is_completed'] ?? false)->count();
            $listIsFullyComplete = ($totalInList > 0 && $totalInList === $completedInList);
            
            // Если список НЕ полностью завершен, исключаем 100% элементы
            if (!$listIsFullyComplete) {
                $candidates = $candidates->reject(fn ($w) => $w['is_completed'] ?? false);
            }
        } else {
            // При обычном квизе исключаем завершённые слова
            $candidates = $candidates->reject(fn ($w) => $w['is_completed'] ?? false);
        }

        $due = $candidates
            ->filter(fn ($w) => empty($w['next_review_at']) || ($w['next_review_at'] && $w['next_review_at']->lte($now)))
            ->sortBy([
                ['level', 'asc'],
                ['next_review_at', 'asc'],
            ])
            ->values();

        $pool = $due->isNotEmpty() ? $due : $candidates->sortBy('level')->values();
        $wordToStudy = $pool->take(max(1, min($count, $pool->count())))->shuffle()->first();

        if (!$wordToStudy) {
            return response()->json([
                'error' => 'Нет доступных слов для изучения',
                'no_more_questions' => true,
            ], 404);
        }

        $asked->push($wordToStudy['word_id']);
        session()->put($sessionKey, $asked->unique()->values()->toArray());

        // В квизе всегда: показываем русский, выбираем/пишем японское (формат 私（わたし）)
        $questionType = 'ru_to_word';
        $userLevel = (int) ($wordToStudy['level'] ?? 0);
        
        // Определяем режим ответа
        // Если список установлен на "только множественный выбор", всегда используем 'choices'
        $multipleChoiceOnly = false;
        if ($listId) {
            $list = \App\Models\WordStudyList::where('id', $listId)
                ->where('user_id', $user->id)
                ->first();
            if ($list) {
                $multipleChoiceOnly = (bool) $list->multiple_choice_only;
            }
        }
        
        $answerMode = $multipleChoiceOnly 
            ? 'choices' 
            : ($userLevel >= 5 ? 'input' : 'choices');

        $displayForm = trim($wordToStudy['reading'] ?? '') !== ''
            ? $wordToStudy['japanese_word'] . '（' . $wordToStudy['reading'] . '）'
            : $wordToStudy['japanese_word'];

        $allWordsDisplay = $allWordsForAnswers->map(function ($w) {
            $r = $w->reading ?? '';
            return trim($r) !== '' ? $w->japanese_word . '（' . $r . '）' : $w->japanese_word;
        });
        $answers = $answerMode === 'choices'
            ? $this->buildAnswerOptions($displayForm, $allWordsDisplay, 6)
            : [];

        $questionId = (string) Str::uuid();
        session()->put("word_quiz.{$quizId}.questions.{$questionId}", [
            'word_id' => $wordToStudy['word_id'],
            'question_type' => $questionType,
            'correct_answer' => $wordToStudy['japanese_word'],
            'reading' => $wordToStudy['reading'] ?? '',
            'expires_at' => now()->addMinutes(30)->toIso8601String(),
        ]);

        return response()->json([
            'quiz_id' => $quizId,
            'question_id' => $questionId,
            'word_id' => $wordToStudy['word_id'],
            'question_type' => $questionType,
            'answer_mode' => $answerMode,
            'question' => $wordToStudy['translation_ru'],
            'reading' => $wordToStudy['reading'] ?? '',
            'answers' => $answers,
            'correct_display' => $displayForm,
            'user_level' => $userLevel,
        ]);
    }

    /**
     * Квиз по словам: отправить ответ
     */
    public function submitWordAnswer(Request $request)
    {
        $request->validate([
            'word_id' => 'required|integer',
            'answer' => 'required|string',
            'quiz_id' => 'required|string',
            'question_id' => 'required|string',
        ]);

        $user = Auth::user();
        $wordId = (int) $request->word_id;
        $answer = trim((string) $request->answer);
        $quizId = (string) $request->quiz_id;
        $questionId = (string) $request->question_id;

        if (!$user->dictionary()->where('global_dictionary.id', $wordId)->exists()) {
            return response()->json(['error' => 'Слово не в вашем словаре'], 403);
        }

        $stored = session()->get("word_quiz.{$quizId}.questions.{$questionId}");
        if (!$stored || ($stored['word_id'] ?? null) != $wordId) {
            return response()->json(['error' => 'Вопрос не найден или устарел'], 422);
        }

        $expiresAt = $stored['expires_at'] ?? null;
        if (is_string($expiresAt) && now()->gt(\Illuminate\Support\Carbon::parse($expiresAt))) {
            session()->forget("word_quiz.{$quizId}.questions.{$questionId}");
            return response()->json(['error' => 'Вопрос устарел'], 422);
        }

        $correctWord = (string) ($stored['correct_answer'] ?? '');
        $correctReading = (string) ($stored['reading'] ?? '');
        $questionType = (string) ($stored['question_type'] ?? '');
        if ($correctWord === '' || !in_array($questionType, ['word_to_ru', 'ru_to_word'], true)) {
            return response()->json(['error' => 'Некорректный вопрос'], 422);
        }

        $word = GlobalDictionary::find($wordId);
        if (!$word) {
            return response()->json(['error' => 'Слово не найдено'], 404);
        }

        $isCorrect = false;
        if ($questionType === 'word_to_ru') {
            $isCorrect = $this->normalizeRuAnswer($answer) === $this->normalizeRuAnswer($correctWord);
        } else {
            // ru_to_word: засчитываем и кандзи, и фуригану, и формат 私（わたし）
            $a = trim($answer);
            $isCorrect = $a === $correctWord
                || $a === $correctReading
                || ($correctReading !== '' && $a === $correctWord . '（' . $correctReading . '）');
            if (!$isCorrect && $correctReading !== '' && preg_match('/^(.+?)[\s]*[（(]([^）)]+)[）)]\s*$/u', $a, $m)) {
                $isCorrect = (trim($m[1]) === $correctWord && trim($m[2]) === $correctReading);
            }
        }

        $progress = WordStudyProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'word_id' => $wordId,
            ],
            [
                'started_at' => now(),
                'days_studied' => 0,
                'level' => 0,
                'is_completed' => false,
            ]
        );

        if ($isCorrect) {
            $progress->level = min(10, $progress->level + 1);
        } else {
            $progress->level = max(0, $progress->level - 1);
        }
        $progress->last_reviewed_at = now();
        $progress->next_review_at = $this->calcNextReviewAt((int) $progress->level, $isCorrect);
        if ($progress->level >= 10) {
            $progress->is_completed = true;
        }
        $progress->save();

        session()->forget("word_quiz.{$quizId}.questions.{$questionId}");

        // Для отображения правильного ответа в формате 私（わたし）
        $correctDisplay = trim($correctReading ?? '') !== ''
            ? $correctWord . '（' . $correctReading . '）'
            : $correctWord;

        return response()->json([
            'correct' => $isCorrect,
            'new_level' => $progress->level,
            'correct_answer' => $correctDisplay,
            'next_review_at' => $progress->next_review_at?->toIso8601String(),
        ]);
    }

    /**
     * Быстрое обновление кандзи из модального окна (для админа)
     */
    public function quickUpdate(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }
        
        $validated = $request->validate([
            'kanji' => ['required', 'string'],
            'translation_ru' => ['nullable', 'string', 'max:255'],
            'reading' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'mnemonic' => ['nullable', 'string'],
            'jlpt_level' => ['nullable', 'integer', 'in:1,2,3,4,5'],
        ]);

        $kanji = Kanji::where('kanji', $validated['kanji'])->firstOrFail();
        
        // Обновляем только переданные поля
        if (isset($validated['translation_ru'])) {
            $kanji->translation_ru = $validated['translation_ru'];
        }
        if (isset($validated['reading'])) {
            $kanji->reading = $validated['reading'];
        }
        if (isset($validated['description'])) {
            $kanji->description = $validated['description'];
        }
        if (isset($validated['mnemonic'])) {
            $kanji->mnemonic = $validated['mnemonic'];
        }
        if (isset($validated['jlpt_level'])) {
            $kanji->jlpt_level = $validated['jlpt_level'] ?: null;
        }
        
        $kanji->save();

        return response()->json([
            'success' => true,
            'message' => 'Кандзи успешно обновлен',
            'kanji' => [
                'kanji' => $kanji->kanji,
                'translation_ru' => $kanji->translation_ru,
                'reading' => $kanji->reading,
                'description' => $kanji->description,
                'mnemonic' => $kanji->mnemonic,
                'jlpt_level' => $kanji->jlpt_level,
            ]
        ]);
    }
}
