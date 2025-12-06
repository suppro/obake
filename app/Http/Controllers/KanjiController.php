<?php

namespace App\Http\Controllers;

use App\Models\Kanji;
use App\Models\KanjiStudyProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $user = Auth::user();
        
        // Получаем все кандзи с прогрессом пользователя
        $allKanji = Kanji::where('is_active', true)->get();
        $userProgress = KanjiStudyProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('kanji');
        
        // Группируем кандзи по уровням JLPT
        $kanjiByLevel = $allKanji->groupBy(function ($kanji) {
            return $kanji->jlpt_level ? "N{$kanji->jlpt_level}" : 'Без уровня';
        })->map(function ($kanjiGroup, $level) use ($userProgress) {
            return $kanjiGroup->map(function ($kanji) use ($userProgress) {
                $progress = $userProgress->get($kanji->kanji);
                return [
                    'kanji' => $kanji->kanji,
                    'translation' => $kanji->translation_ru,
                    'jlpt_level' => $kanji->jlpt_level,
                    'level' => $progress ? $progress->level : 0,
                    'last_reviewed_at' => $progress ? $progress->last_reviewed_at : null,
                ];
            })->sortBy('level')->values();
        })->sortKeys();
        
        // Статистика
        $totalKanji = $allKanji->count();
        $studiedKanji = $userProgress->count();
        $completedKanji = $userProgress->where('level', '>=', 10)->count();
        
        return view('kanji.index', compact('kanjiByLevel', 'totalKanji', 'studiedKanji', 'completedKanji'));
    }

    /**
     * Страница квиза
     */
    public function quiz(Request $request)
    {
        $count = (int) $request->get('count', 10);
        $count = max(1, min(50, $count)); // Ограничиваем от 1 до 50
        $jlptLevel = $request->get('jlpt_level', 'any'); // any, 5, 4, 3, 2, 1
        
        return view('kanji.quiz', compact('count', 'jlptLevel'));
    }

    /**
     * Получить вопрос для квиза
     */
    public function getQuestion(Request $request)
    {
        $user = Auth::user();
        $count = (int) $request->get('count', 10);
        $jlptLevel = $request->get('jlpt_level', 'any');
        
        $allKanji = $this->getAllKanji($jlptLevel);
        if ($allKanji->isEmpty()) {
            $allKanji = collect(self::KANJI_LIST_FALLBACK);
        }
        
        // Получаем все кандзи с прогрессом пользователя
        $userProgress = KanjiStudyProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('kanji');
        
        // Создаем список всех кандзи с их уровнями
        $kanjiWithLevels = $allKanji->map(function ($kanji) use ($userProgress) {
            $progress = $userProgress->get($kanji['kanji']);
            return [
                'kanji' => $kanji['kanji'],
                'translation' => $kanji['translation'],
                'level' => $progress ? $progress->level : 0,
                'last_reviewed_at' => $progress ? $progress->last_reviewed_at : null,
            ];
        });
        
        // Сортируем по уровню (сначала те, что нужно изучить/повторить)
        $kanjiToStudy = $kanjiWithLevels
            ->sortBy([
                ['level', 'asc'],
                ['last_reviewed_at', 'asc'],
            ])
            ->take($count)
            ->shuffle()
            ->first();
        
        if (!$kanjiToStudy) {
            return response()->json(['error' => 'Нет доступных кандзи для изучения'], 404);
        }
        
        // Определяем тип вопроса (50/50)
        $questionType = rand(0, 1) === 0 ? 'kanji_to_ru' : 'ru_to_kanji';
        
        // Генерируем варианты ответов
        $allTranslations = $allKanji->pluck('translation')->unique()->shuffle();
        $correctAnswer = $kanjiToStudy['translation'];
        
        // Выбираем 4 неправильных ответа
        $wrongAnswers = $allTranslations->reject(function ($translation) use ($correctAnswer) {
            return $translation === $correctAnswer;
        })->take(4)->toArray();
        
        // Если вопрос типа kanji_to_ru
        if ($questionType === 'kanji_to_ru') {
            $answers = array_merge([$correctAnswer], $wrongAnswers);
            shuffle($answers);
            
            return response()->json([
                'question_type' => 'kanji_to_ru',
                'question' => $kanjiToStudy['kanji'],
                'answers' => $answers,
                'correct_answer' => $correctAnswer,
                'kanji' => $kanjiToStudy['kanji'],
            ]);
        } else {
            // Если вопрос типа ru_to_kanji
            $allKanjiList = $allKanji->shuffle();
            $wrongKanji = $allKanjiList->reject(function ($kanji) use ($kanjiToStudy) {
                return $kanji['kanji'] === $kanjiToStudy['kanji'];
            })->take(4)->pluck('kanji')->toArray();
            
            $answers = array_merge([$kanjiToStudy['kanji']], $wrongKanji);
            shuffle($answers);
            
            return response()->json([
                'question_type' => 'ru_to_kanji',
                'question' => $correctAnswer,
                'answers' => $answers,
                'correct_answer' => $kanjiToStudy['kanji'],
                'kanji' => $kanjiToStudy['kanji'],
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
            'correct_answer' => 'required|string',
        ]);
        
        $user = Auth::user();
        $kanji = $request->kanji;
        $answer = $request->answer;
        $correctAnswer = $request->correct_answer;
        
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
            ]
        );
        
        // Проверяем ответ
        $isCorrect = $answer === $correctAnswer;
        
        if ($isCorrect) {
            $progress->level = min(10, $progress->level + 1);
        } else {
            $progress->level = max(0, $progress->level - 1);
        }
        
        $progress->last_reviewed_at = now();
        $progress->save();
        
        return response()->json([
            'correct' => $isCorrect,
            'new_level' => $progress->level,
            'correct_answer' => $correctAnswer,
        ]);
    }
}
