<?php

namespace App\Http\Controllers;

use App\Models\WordRepetition;
use App\Models\KanjiStudyProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function welcome()
    {
        return view('welcome');
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // Получаем выбранный год из запроса, по умолчанию текущий год
        $selectedYear = $request->get('year', $today->year);
        $selectedYear = (int)$selectedYear;
        
        // Начало и конец выбранного года
        $startDate = Carbon::create($selectedYear, 1, 1)->startOfDay();
        $endDate = Carbon::create($selectedYear, 12, 31)->startOfDay(); // Используем startOfDay для корректного сравнения
        
        // Получаем даты, когда пользователь занимался словами в выбранном году
        $wordRepetitions = WordRepetition::where('user_id', $user->id)
            ->whereBetween('repetition_date', [$startDate, $endDate])
            ->select('repetition_date', DB::raw('COUNT(*) as count'))
            ->groupBy('repetition_date')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->repetition_date->format('Y-m-d') => (object)[
                    'repetition_date' => $item->repetition_date,
                    'count' => $item->count
                ]];
            });
        
        // Получаем даты, когда пользователь изучал кандзи в выбранном году
        // Используем last_reviewed_at как дату активности
        // Считаем количество уникальных кандзи, изученных в каждый день
        $kanjiRepetitions = KanjiStudyProgress::where('user_id', $user->id)
            ->whereNotNull('last_reviewed_at')
            ->whereRaw('DATE(last_reviewed_at) >= ?', [$startDate->format('Y-m-d')])
            ->whereRaw('DATE(last_reviewed_at) <= ?', [$endDate->format('Y-m-d')])
            ->selectRaw('DATE(last_reviewed_at) as activity_date, COUNT(*) as count')
            ->groupBy(DB::raw('DATE(last_reviewed_at)'))
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->activity_date => (object)[
                    'repetition_date' => Carbon::parse($item->activity_date),
                    'count' => $item->count
                ]];
            });
        
        // Объединяем данные из слов и кандзи
        // Создаем коллекцию со всеми уникальными датами
        $allDateKeys = $wordRepetitions->keys()->merge($kanjiRepetitions->keys())->unique();
        
        $repetitionDates = $allDateKeys->mapWithKeys(function($dateKey) use ($wordRepetitions, $kanjiRepetitions) {
            $wordCount = isset($wordRepetitions[$dateKey]) ? $wordRepetitions[$dateKey]->count : 0;
            $kanjiCount = isset($kanjiRepetitions[$dateKey]) ? $kanjiRepetitions[$dateKey]->count : 0;
            
            // Используем дату из слова или кандзи (они должны совпадать для одного дня)
            $date = isset($wordRepetitions[$dateKey]) 
                ? $wordRepetitions[$dateKey]->repetition_date 
                : $kanjiRepetitions[$dateKey]->repetition_date;
            
            return [$dateKey => (object)[
                'repetition_date' => $date,
                'count' => $wordCount + $kanjiCount
            ]];
        });
        
        // Получаем первый день года и определяем, с какого дня недели начинается год
        $firstDayOfYear = $startDate->copy();
        $firstDayWeekday = $firstDayOfYear->dayOfWeek; // 0 = воскресенье, 1 = понедельник и т.д.
        
        // Получаем последний день года
        $lastDayOfYear = $endDate->copy();
        $lastDayWeekday = $lastDayOfYear->dayOfWeek; // 0 = воскресенье, 1 = понедельник и т.д.
        
        // Вычисляем количество недель в году
        // Нужно учесть дни до начала года (если год начинается не с воскресенья)
        // и дни после конца года (если год заканчивается не в субботу)
        $daysInYear = $firstDayOfYear->diffInDays($lastDayOfYear) + 1; // Исправлен порядок
        // Количество недель = (дни в году + дни до начала первой недели + дни после конца последней недели) / 7
        // Дни после конца года = 6 - день недели последнего дня (чтобы дополнить до субботы)
        $weeksInYear = ceil(($daysInYear + $firstDayWeekday + (6 - $lastDayWeekday)) / 7);
        // Обычно 52-53 недели, но может быть 54
        if ($weeksInYear < 52) $weeksInYear = 52;
        if ($weeksInYear > 54) $weeksInYear = 54;
        
        return view('dashboard', compact('repetitionDates', 'today', 'selectedYear', 'startDate', 'endDate', 'firstDayWeekday', 'weeksInYear'));
    }

    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'daily_words_quota' => ['required', 'integer', 'min:1', 'max:100'],
            ]);

            $user = Auth::user();
            $user->daily_words_quota = (int)$validated['daily_words_quota'];
            $user->save();

            return response()->json(['success' => true, 'message' => 'Настройки обновлены']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при сохранении настроек: ' . $e->getMessage()
            ], 500);
        }
    }
}
