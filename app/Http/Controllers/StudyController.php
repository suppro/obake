<?php

namespace App\Http\Controllers;

use App\Models\GlobalDictionary;
use App\Models\WordRepetition;
use App\Models\WordStudyProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudyController extends Controller
{
    /**
     * Календарь активности
     */
    public function calendar()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // Получаем даты, когда пользователь занимался (за последний год)
        $startDate = $today->copy()->subYear();
        $repetitionDates = WordRepetition::where('user_id', $user->id)
            ->where('repetition_date', '>=', $startDate)
            ->select('repetition_date', DB::raw('COUNT(*) as count'))
            ->groupBy('repetition_date')
            ->get()
            ->keyBy(function($item) {
                return $item->repetition_date->format('Y-m-d');
            });
        
        return view('study.calendar', compact('repetitionDates', 'today'));
    }

    /**
     * Страница повторений
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // Получаем слова для повторения сегодня
        $wordsToReview = $this->getWordsToReview($user, $today);
        
        // Если слов нет, предлагаем начать изучение новых
        $availableWords = $user->dictionary()
            ->whereDoesntHave('studyProgress', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();
        
        return view('study.index', compact('wordsToReview', 'availableWords', 'today'));
    }

    /**
     * Начать изучение слова
     */
    public function startStudying(Request $request)
    {
        $validated = $request->validate([
            'word_id' => ['required', 'exists:global_dictionary,id'],
        ]);

        $user = Auth::user();
        
        // Проверяем, что слово в словаре пользователя
        if (!$user->dictionary()->where('global_dictionary.id', $validated['word_id'])->exists()) {
            return response()->json(['error' => 'Слово не найдено в вашем словаре'], 404);
        }

        // Проверяем, не начато ли уже изучение
        $progress = WordStudyProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'word_id' => $validated['word_id'],
            ],
            [
                'started_at' => Carbon::today(),
                'days_studied' => 0,
                'is_completed' => false,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Изучение слова начато']);
    }

    /**
     * Получить следующее слово для повторения
     */
    public function getNextWord()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        $wordsToReview = $this->getWordsToReview($user, $today);
        
        if ($wordsToReview->isEmpty()) {
            return response()->json([
                'has_words' => false,
                'message' => 'На сегодня слов для повторения нет. Хотите повторить уже изученные?'
            ]);
        }

        $wordData = $wordsToReview->first();
        $word = GlobalDictionary::find($wordData['word_id']);
        $progress = WordStudyProgress::where('user_id', $user->id)
            ->where('word_id', $word->id)
            ->first();

        // Определяем направление и формат
        $direction = $wordData['direction'];
        $daysStudied = $progress->days_studied;
        $showFurigana = $daysStudied < 6 && $direction === 'ru_to_jp';

        return response()->json([
            'has_words' => true,
            'word' => [
                'id' => $word->id,
                'japanese' => $word->japanese_word,
                'reading' => $word->reading,
                'translation_ru' => $word->translation_ru,
                'translation_en' => $word->translation_en,
                'direction' => $direction, // Добавляем направление в объект слова
            ],
            'direction' => $direction,
            'show_furigana' => $showFurigana,
            'days_studied' => $daysStudied,
            'progress_id' => $progress->id,
        ]);
    }

    /**
     * Проверить ответ
     */
    public function checkAnswer(Request $request)
    {
        $validated = $request->validate([
            'word_id' => ['required', 'exists:global_dictionary,id'],
            'direction' => ['required', 'in:ru_to_jp,jp_to_ru'],
            'user_answer' => ['required', 'string'],
            'progress_id' => ['required', 'exists:word_study_progress,id'],
        ]);

        $user = Auth::user();
        $word = GlobalDictionary::findOrFail($validated['word_id']);
        $today = Carbon::today();
        
        // Проверяем правильность ответа
        $isCorrect = false;
        $correctAnswer = '';
        
        if ($validated['direction'] === 'ru_to_jp') {
            // Проверяем чтение (хирагана)
            $correctAnswer = $word->reading ?? '';
            $userAnswer = mb_strtolower(trim($validated['user_answer']));
            $correctAnswerLower = mb_strtolower(trim($correctAnswer));
            $isCorrect = $userAnswer === $correctAnswerLower;
        } else {
            // Проверяем перевод на русский
            $correctAnswer = $word->translation_ru;
            $userAnswer = mb_strtolower(trim($validated['user_answer']));
            $correctAnswerLower = mb_strtolower(trim($correctAnswer));
            // Разрешаем частичное совпадение (содержит правильный ответ)
            $isCorrect = strpos($correctAnswerLower, $userAnswer) !== false || 
                        strpos($userAnswer, $correctAnswerLower) !== false;
        }

        // Сохраняем повторение
        WordRepetition::create([
            'user_id' => $user->id,
            'word_id' => $word->id,
            'repetition_date' => $today,
            'direction' => $validated['direction'],
            'is_correct' => $isCorrect,
            'user_answer' => $validated['user_answer'],
        ]);

        // Обновляем прогресс только если слово еще не изучено
        // Проверяем, что progress_id принадлежит текущему пользователю
        $progress = WordStudyProgress::where('id', $validated['progress_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        if (!$progress->is_completed) {
            // Проверяем, выполнены ли оба повторения за сегодня
            $todayRepetitions = WordRepetition::where('user_id', $user->id)
                ->where('word_id', $word->id)
                ->where('repetition_date', $today)
                ->count();

            if ($todayRepetitions >= 2) {
                // Оба повторения выполнены
                $progress->last_reviewed_at = $today;
                $progress->days_studied = $progress->days_studied + 1;
                
                // Проверяем, завершено ли изучение (10 дней)
                if ($progress->days_studied >= 10 && !$progress->is_completed) {
                    $progress->is_completed = true;
                    $progress->completed_at = $today;
                }
                
                $progress->save();
            }
        }

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_answer' => $correctAnswer,
            'user_answer' => $validated['user_answer'],
            'days_studied' => $progress->days_studied,
            'is_completed' => $progress->is_completed,
        ]);
    }

    /**
     * Получить слова для повторения на сегодня
     */
    private function getWordsToReview($user, $today)
    {
        // Получаем все слова в изучении
        $progresses = WordStudyProgress::where('user_id', $user->id)
            ->where('is_completed', false)
            ->get();

        $wordsToReview = collect();

        foreach ($progresses as $progress) {
            // Проверяем, нужно ли повторять сегодня
            $lastReviewed = $progress->last_reviewed_at;
            
            // Если еще не было повторений или последнее было не сегодня
            if (!$lastReviewed || $lastReviewed->lt($today)) {
                // Проверяем, какие повторения уже сделаны сегодня
                $todayRepetitions = WordRepetition::where('user_id', $user->id)
                    ->where('word_id', $progress->word_id)
                    ->where('repetition_date', $today)
                    ->pluck('direction')
                    ->toArray();

                // Добавляем недостающие направления
                if (!in_array('ru_to_jp', $todayRepetitions)) {
                    $wordsToReview->push([
                        'word_id' => $progress->word_id,
                        'direction' => 'ru_to_jp',
                        'progress_id' => $progress->id,
                    ]);
                }

                if (!in_array('jp_to_ru', $todayRepetitions)) {
                    $wordsToReview->push([
                        'word_id' => $progress->word_id,
                        'direction' => 'jp_to_ru',
                        'progress_id' => $progress->id,
                    ]);
                }
            }
        }

        return $wordsToReview;
    }

    /**
     * Получить слова для дополнительного повторения (уже изученные)
     */
    public function getExtraWords()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // Получаем изученные слова (можно повторить)
        $completedWords = WordStudyProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->with('word')
            ->get()
            ->pluck('word')
            ->filter();

        if ($completedWords->isEmpty()) {
            return response()->json([
                'has_words' => false,
                'message' => 'Нет изученных слов для повторения'
            ]);
        }

        // Выбираем случайное слово
        $randomWord = $completedWords->random();
        $progress = WordStudyProgress::where('user_id', $user->id)
            ->where('word_id', $randomWord->id)
            ->first();

        // Случайно выбираем направление
        $direction = rand(0, 1) === 0 ? 'ru_to_jp' : 'jp_to_ru';
        $showFurigana = $direction === 'ru_to_jp' && rand(0, 1) === 0; // Случайно показываем фуригану

        return response()->json([
            'has_words' => true,
            'word' => [
                'id' => $randomWord->id,
                'japanese' => $randomWord->japanese_word,
                'reading' => $randomWord->reading,
                'translation_ru' => $randomWord->translation_ru,
                'translation_en' => $randomWord->translation_en,
                'direction' => $direction,
            ],
            'direction' => $direction,
            'show_furigana' => $showFurigana,
            'days_studied' => $progress->days_studied,
            'progress_id' => $progress->id,
        ]);
    }
}
