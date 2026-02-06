<?php

namespace App\Http\Controllers;

use App\Services\JapaneseConjugationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConjugationController extends Controller
{
    private $conjugationService;

    public function __construct(JapaneseConjugationService $conjugationService)
    {
        $this->conjugationService = $conjugationService;
    }

    /**
     * Главная страница тренировки спряжений
     */
    public function index()
    {
        return view('conjugation.index', [
            'verbForms' => $this->conjugationService->getVerbForms(),
            'adjectiveForms' => $this->conjugationService->getAdjectiveForms(),
        ]);
    }

    /**
     * Гайд по всем спряжениям
     */
    public function guide()
    {
        $verbForms = $this->conjugationService->getVerbForms();
        $adjectiveForms = $this->conjugationService->getAdjectiveForms();
        
        // Примеры глаголов для демонстрации
        $exampleVerbs = [
            'group1' => [
                ['word' => '書く', 'reading' => 'かく', 'meaning' => 'писать'],
                ['word' => '話す', 'reading' => 'はなす', 'meaning' => 'говорить'],
                ['word' => '読む', 'reading' => 'よむ', 'meaning' => 'читать'],
                ['word' => '買う', 'reading' => 'かう', 'meaning' => 'покупать'],
                ['word' => '行く', 'reading' => 'いく', 'meaning' => 'идти'],
            ],
            'group2' => [
                ['word' => '食べる', 'reading' => 'たべる', 'meaning' => 'есть'],
                ['word' => '見る', 'reading' => 'みる', 'meaning' => 'смотреть'],
                ['word' => '起きる', 'reading' => 'おきる', 'meaning' => 'вставать'],
            ],
            'group3' => [
                ['word' => 'する', 'reading' => 'する', 'meaning' => 'делать'],
                ['word' => '来る', 'reading' => 'くる', 'meaning' => 'приходить'],
            ],
        ];
        
        // Примеры прилагательных
        $exampleAdjectives = [
            'i_adjectives' => [
                ['word' => '高い', 'reading' => 'たかい', 'meaning' => 'высокий, дорогой'],
                ['word' => '大きい', 'reading' => 'おおきい', 'meaning' => 'большой'],
                ['word' => '面白い', 'reading' => 'おもしろい', 'meaning' => 'интересный'],
            ],
            'na_adjectives' => [
                ['word' => 'きれい', 'reading' => 'きれい', 'meaning' => 'красивый, чистый'],
                ['word' => '静か', 'reading' => 'しずか', 'meaning' => 'тихий'],
                ['word' => '元気', 'reading' => 'げんき', 'meaning' => 'здоровый, энергичный'],
            ],
        ];
        
        // Генерируем примеры спряжений для каждого примера
        foreach ($exampleVerbs as $group => $verbs) {
            foreach ($verbs as &$verb) {
                $verb['conjugations'] = [];
                foreach ($verbForms as $formKey => $form) {
                    $conjugated = $this->conjugationService->conjugateVerb($verb['word'], $group, $formKey);
                    if ($conjugated) {
                        $verb['conjugations'][$formKey] = $conjugated;
                    }
                }
            }
        }
        
        foreach ($exampleAdjectives as $type => $adjectives) {
            foreach ($adjectives as &$adjective) {
                $adjective['conjugations'] = [];
                foreach ($adjectiveForms as $formKey => $form) {
                    if ($type === 'i_adjectives') {
                        $conjugated = $this->conjugationService->conjugateIAdjective($adjective['word'], $formKey);
                    } else {
                        $conjugated = $this->conjugationService->conjugateNaAdjective($adjective['word'], $formKey);
                    }
                    if ($conjugated) {
                        $adjective['conjugations'][$formKey] = $conjugated;
                    }
                }
            }
        }
        
        return view('conjugation.guide', [
            'verbForms' => $verbForms,
            'adjectiveForms' => $adjectiveForms,
            'exampleVerbs' => $exampleVerbs,
            'exampleAdjectives' => $exampleAdjectives,
        ]);
    }

    /**
     * Получить случайный вопрос для тренировки
     */
    public function getQuestion(Request $request)
    {
        $type = $request->input('type'); // 'verb' or 'adjective'
        $group = $request->input('group'); // для глаголов: 'group1', 'group2', 'group3', null
        $adjectiveType = $request->input('adjective_type'); // для прилагательных: 'i_adjectives', 'na_adjectives', null
        $forms = $request->input('forms', []); // массив форм для тренировки
        
        if ($type === 'verb') {
            // Обработка группы: если передан пустой строкой или null, используем случайную
            $selectedGroup = ($group && $group !== '') ? $group : null;
            $word = $this->conjugationService->getRandomVerb($selectedGroup);
            $detectedGroup = $this->conjugationService->detectVerbGroup($word['word']);
            
            // Если не указаны конкретные формы, используем все
            if (empty($forms) || (is_string($forms) && $forms === '')) {
                $availableForms = array_keys($this->conjugationService->getVerbForms());
            } else {
                // Если формы переданы как строка через запятую, преобразуем в массив
                if (is_string($forms)) {
                    $availableForms = explode(',', $forms);
                    $availableForms = array_filter(array_map('trim', $availableForms));
                } else {
                    $availableForms = $forms;
                }
            }
            
            if (empty($availableForms)) {
                return response()->json(['error' => 'Не выбраны формы для тренировки'], 400);
            }
            
            $form = $availableForms[array_rand($availableForms)];
            $conjugated = $this->conjugationService->conjugateVerb($word['word'], $detectedGroup, $form);
            
            // Сохраняем правильный ответ в сессии
            session(['conjugation_correct_answer' => $conjugated]);
            session(['conjugation_word' => $word]);
            session(['conjugation_form' => $form]);
            session(['conjugation_type' => 'verb']);
            
            return response()->json([
                'word' => $word,
                'form' => $form,
                'formName' => $this->conjugationService->getVerbForms()[$form]['name'],
                'type' => 'verb',
            ]);
        } else {
            // Обработка типа прилагательного: если передан пустой строкой или null, используем случайный
            $selectedAdjectiveType = ($adjectiveType && $adjectiveType !== '') ? $adjectiveType : null;
            $word = $this->conjugationService->getRandomAdjective($selectedAdjectiveType);
            $detectedAdjectiveType = strpos($word['word'], 'い') === mb_strlen($word['word']) - 1 ? 'i_adjectives' : 'na_adjectives';
            
            // Если не указаны конкретные формы, используем все
            if (empty($forms) || (is_string($forms) && $forms === '')) {
                $availableForms = array_keys($this->conjugationService->getAdjectiveForms());
            } else {
                // Если формы переданы как строка через запятую, преобразуем в массив
                if (is_string($forms)) {
                    $availableForms = explode(',', $forms);
                    $availableForms = array_filter(array_map('trim', $availableForms));
                } else {
                    $availableForms = $forms;
                }
            }
            
            if (empty($availableForms)) {
                return response()->json(['error' => 'Не выбраны формы для тренировки'], 400);
            }
            
            $form = $availableForms[array_rand($availableForms)];
            
            if ($detectedAdjectiveType === 'i_adjectives') {
                $conjugated = $this->conjugationService->conjugateIAdjective($word['word'], $form);
            } else {
                $conjugated = $this->conjugationService->conjugateNaAdjective($word['word'], $form);
            }
            
            // Сохраняем правильный ответ в сессии
            session(['conjugation_correct_answer' => $conjugated]);
            session(['conjugation_word' => $word]);
            session(['conjugation_form' => $form]);
            session(['conjugation_type' => 'adjective']);
            session(['conjugation_adjective_type' => $detectedAdjectiveType]);
            
            return response()->json([
                'word' => $word,
                'form' => $form,
                'formName' => $this->conjugationService->getAdjectiveForms()[$form]['name'],
                'type' => 'adjective',
            ]);
        }
    }

    /**
     * Проверить ответ
     */
    public function checkAnswer(Request $request)
    {
        $userAnswer = trim($request->input('answer', ''));
        $correctAnswer = session('conjugation_correct_answer');
        $word = session('conjugation_word');
        $form = session('conjugation_form');
        $type = session('conjugation_type');
        
        // Нормализуем ответы для сравнения (убираем лишние пробелы)
        $normalizedUser = mb_convert_kana($userAnswer, 'a', 'UTF-8');
        $normalizedCorrect = mb_convert_kana($correctAnswer, 'a', 'UTF-8');
        
        // Проверяем точное совпадение (можно сделать более умную проверку)
        $isCorrect = $normalizedUser === $normalizedCorrect;
        
        $formName = $type === 'verb' 
            ? $this->conjugationService->getVerbForms()[$form]['name']
            : $this->conjugationService->getAdjectiveForms()[$form]['name'];
        
        return response()->json([
            'correct' => $isCorrect,
            'userAnswer' => $userAnswer,
            'correctAnswer' => $correctAnswer,
            'word' => $word,
            'form' => $form,
            'formName' => $formName,
        ]);
    }
}

