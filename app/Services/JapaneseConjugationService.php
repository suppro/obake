<?php

namespace App\Services;

class JapaneseConjugationService
{
    // Базовые глаголы для тренировки
    private const VERBS = [
        // Группа I (Godan/у-глаголы)
        'group1' => [
            ['word' => '書く', 'reading' => 'かく', 'meaning' => 'писать'],
            ['word' => '話す', 'reading' => 'はなす', 'meaning' => 'говорить'],
            ['word' => '読む', 'reading' => 'よむ', 'meaning' => 'читать'],
            ['word' => '帰る', 'reading' => 'かえる', 'meaning' => 'возвращаться'],
            ['word' => '死ぬ', 'reading' => 'しぬ', 'meaning' => 'умирать'],
            ['word' => '待つ', 'reading' => 'まつ', 'meaning' => 'ждать'],
            ['word' => '遊ぶ', 'reading' => 'あそぶ', 'meaning' => 'играть'],
            ['word' => '飲む', 'reading' => 'のむ', 'meaning' => 'пить'],
            ['word' => '買う', 'reading' => 'かう', 'meaning' => 'покупать'],
            ['word' => '行く', 'reading' => 'いく', 'meaning' => 'идти'],
        ],
        // Группа II (Ichidan/ру-глаголы)
        'group2' => [
            ['word' => '食べる', 'reading' => 'たべる', 'meaning' => 'есть'],
            ['word' => '見る', 'reading' => 'みる', 'meaning' => 'смотреть'],
            ['word' => '起きる', 'reading' => 'おきる', 'meaning' => 'вставать'],
            ['word' => '寝る', 'reading' => 'ねる', 'meaning' => 'спать'],
            ['word' => '着る', 'reading' => 'きる', 'meaning' => 'носить (одежду)'],
            ['word' => '開ける', 'reading' => 'あける', 'meaning' => 'открывать'],
            ['word' => '閉める', 'reading' => 'しめる', 'meaning' => 'закрывать'],
            ['word' => '教える', 'reading' => 'おしえる', 'meaning' => 'учить'],
        ],
        // Группа III (неправильные глаголы)
        'group3' => [
            ['word' => 'する', 'reading' => 'する', 'meaning' => 'делать'],
            ['word' => '来る', 'reading' => 'くる', 'meaning' => 'приходить'],
        ],
    ];

    // Прилагательные для тренировки
    private const ADJECTIVES = [
        // I-прилагательные (い-прилагательные)
        'i_adjectives' => [
            ['word' => '高い', 'reading' => 'たかい', 'meaning' => 'высокий, дорогой'],
            ['word' => '大きい', 'reading' => 'おおきい', 'meaning' => 'большой'],
            ['word' => '小さい', 'reading' => 'ちいさい', 'meaning' => 'маленький'],
            ['word' => '面白い', 'reading' => 'おもしろい', 'meaning' => 'интересный'],
            ['word' => '楽しい', 'reading' => 'たのしい', 'meaning' => 'весёлый'],
            ['word' => '暑い', 'reading' => 'あつい', 'meaning' => 'жаркий'],
            ['word' => '寒い', 'reading' => 'さむい', 'meaning' => 'холодный'],
            ['word' => '新しい', 'reading' => 'あたらしい', 'meaning' => 'новый'],
            ['word' => '古い', 'reading' => 'ふるい', 'meaning' => 'старый'],
            ['word' => '難しい', 'reading' => 'むずかしい', 'meaning' => 'сложный'],
        ],
        // Na-прилагательные
        'na_adjectives' => [
            ['word' => 'きれい', 'reading' => 'きれい', 'meaning' => 'красивый, чистый'],
            ['word' => '静か', 'reading' => 'しずか', 'meaning' => 'тихий'],
            ['word' => '元気', 'reading' => 'げんき', 'meaning' => 'здоровый, энергичный'],
            ['word' => '便利', 'reading' => 'べんり', 'meaning' => 'удобный'],
            ['word' => '有名', 'reading' => 'ゆうめい', 'meaning' => 'известный'],
            ['word' => '親切', 'reading' => 'しんせつ', 'meaning' => 'добрый'],
            ['word' => '大切', 'reading' => 'たいせつ', 'meaning' => 'важный'],
            ['word' => '好き', 'reading' => 'すき', 'meaning' => 'любимый'],
        ],
    ];

    // Формы спряжения для глаголов
    private const VERB_FORMS = [
        'masu' => [
            'name' => 'Masu форма (вежливая)',
            'example' => '書きます',
            'description' => 'Вежливая форма настоящего времени. Используется в формальных ситуациях, при общении с незнакомыми людьми, на работе.',
            'usage' => '本を読みます。 (Читаю книгу.) / 学校へ行きます。 (Иду в школу.)'
        ],
        'masen' => [
            'name' => 'Отрицательная (вежливая)',
            'example' => '書きません',
            'description' => 'Отрицательная форма вежливого стиля. Выражает отсутствие действия в настоящем.',
            'usage' => '本を読みません。 (Не читаю книгу.) / 学校へ行きません。 (Не иду в школу.)'
        ],
        'mashita' => [
            'name' => 'Прошедшее (вежливая)',
            'example' => '書きました',
            'description' => 'Прошедшее время вежливого стиля. Выражает завершённое действие в прошлом.',
            'usage' => '本を読みました。 (Прочитал книгу.) / 学校へ行きました。 (Пошёл в школу.)'
        ],
        'masen_deshita' => [
            'name' => 'Прошедшее отрицательное (вежливая)',
            'example' => '書きませんでした',
            'description' => 'Отрицательная форма прошедшего времени вежливого стиля.',
            'usage' => '本を読みませんでした。 (Не читал книгу.) / 学校へ行きませんでした。 (Не пошёл в школу.)'
        ],
        'te' => [
            'name' => 'Te форма',
            'example' => '書いて',
            'description' => 'Многофункциональная форма. Используется для соединения действий, выражения просьбы, описания состояния.',
            'usage' => '本を読んで、勉強します。 (Читаю книгу и учусь.) / 窓を開けてください。 (Пожалуйста, откройте окно.)'
        ],
        'ta' => [
            'name' => 'Прошедшее (простая)',
            'example' => '書いた',
            'description' => 'Простая форма прошедшего времени. Используется в неформальном общении, повествовании.',
            'usage' => '本を読んだ。 (Прочитал книгу.) / 学校へ行った。 (Пошёл в школу.)'
        ],
        'nai' => [
            'name' => 'Отрицательная (простая)',
            'example' => '書かない',
            'description' => 'Отрицательная форма простого стиля настоящего времени.',
            'usage' => '本を読まない。 (Не читаю книгу.) / 学校へ行かない。 (Не иду в школу.)'
        ],
        'nakatta' => [
            'name' => 'Прошедшее отрицательное (простая)',
            'example' => '書かなかった',
            'description' => 'Отрицательная форма прошедшего времени простого стиля.',
            'usage' => '本を読まなかった。 (Не читал книгу.) / 学校へ行かなかった。 (Не пошёл в школу.)'
        ],
        'ba' => [
            'name' => 'Условная форма (ba)',
            'example' => '書けば',
            'description' => 'Условная форма. Выражает условие "если...". Более формальная, чем tara форма.',
            'usage' => '本を読めば、わかります。 (Если прочитаешь книгу, поймёшь.) / 勉強すれば、合格します。 (Если будешь учиться, сдашь.)'
        ],
        'tara' => [
            'name' => 'Условная форма (tara)',
            'example' => '書いたら',
            'description' => 'Условная форма. Выражает условие "если...". Более разговорная, чем ba форма. Также может выражать последовательность действий.',
            'usage' => '本を読んだら、わかる。 (Если прочитаешь книгу, поймёшь.) / 学校へ行ったら、電話します。 (Когда приду в школу, позвоню.)'
        ],
        'tai' => [
            'name' => 'Хочу делать (tai форма)',
            'example' => '書きたい',
            'description' => 'Выражает желание. "Хочу сделать что-то". Склоняется как прилагательное.',
            'usage' => '本を読みたい。 (Хочу прочитать книгу.) / 日本へ行きたい。 (Хочу поехать в Японию.)'
        ],
        'potential' => [
            'name' => 'Потенциальная форма',
            'example' => '書ける',
            'description' => 'Выражает возможность или способность. "Могу/умею делать что-то".',
            'usage' => '日本語を話せます。 (Могу говорить по-японски.) / 本を読める。 (Могу читать книгу.)'
        ],
        'passive' => [
            'name' => 'Пассивная форма',
            'example' => '書かれる',
            'description' => 'Пассивный залог. Выражает действие, совершаемое над субъектом. "Быть сделанным кем-то".',
            'usage' => '本が読まれました。 (Книга была прочитана.) / 私は先生に褒められました。 (Меня похвалил учитель.)'
        ],
        'causative' => [
            'name' => 'Побудительная форма',
            'example' => '書かせる',
            'description' => 'Выражает причинение действия. "Заставлять кого-то делать что-то" или "Разрешать кому-то делать что-то".',
            'usage' => '子供に本を読ませます。 (Заставляю ребёнка читать книгу.) / 学生に宿題をさせます。 (Заставляю учеников делать домашнее задание.)'
        ],
        'imperative' => [
            'name' => 'Повелительное наклонение',
            'example' => '書け',
            'description' => 'Выражает приказ или требование. Очень грубая форма, используется редко, в основном мужчинами или в военной обстановке.',
            'usage' => '早く行け！ (Иди быстрее!) / 静かにしろ！ (Будь тише!)'
        ],
        'volitional' => [
            'name' => 'Волевая форма',
            'example' => '書こう',
            'description' => 'Выражает намерение или предложение. "Давай сделаем" или "Я собираюсь сделать".',
            'usage' => '一緒に行こう。 (Давай пойдём вместе.) / 本を読もう。 (Прочитаю книгу / Давай прочитаем книгу.)'
        ],
    ];

    // Формы спряжения для прилагательных
    private const ADJECTIVE_FORMS = [
        'present' => [
            'name' => 'Настоящее',
            'example' => '高い',
            'description' => 'Базовая форма прилагательного. Используется как сказуемое или определительное слово.',
            'usage' => 'この本は高い。 (Эта книга дорогая.) / 高い本を買いました。 (Купил дорогую книгу.)'
        ],
        'present_negative' => [
            'name' => 'Отрицательное',
            'example' => '高くない',
            'description' => 'Отрицательная форма настоящего времени. Выражает отсутствие признака.',
            'usage' => 'この本は高くない。 (Эта книга недорогая.) / 彼は忙しくない。 (Он не занят.)'
        ],
        'past' => [
            'name' => 'Прошедшее',
            'example' => '高かった',
            'description' => 'Прошедшее время. Выражает признак в прошлом.',
            'usage' => '昨日は忙しかった。 (Вчера был занят.) / その時、寒かった。 (В то время было холодно.)'
        ],
        'past_negative' => [
            'name' => 'Прошедшее отрицательное',
            'example' => '高くなかった',
            'description' => 'Отрицательная форма прошедшего времени.',
            'usage' => '昨日は忙しくなかった。 (Вчера не был занят.) / その時、寒くなかった。 (В то время не было холодно.)'
        ],
        'adverb' => [
            'name' => 'Наречие',
            'example' => '高く',
            'description' => 'Наречная форма. Описывает способ выполнения действия. Для I-прилагательных убирается い и добавляется く.',
            'usage' => '早く走ります。 (Бегу быстро.) / 静かに話します。 (Говорю тихо.)'
        ],
        'te' => [
            'name' => 'Te форма',
            'example' => '高くて',
            'description' => 'Используется для соединения прилагательных или описания причины. Для I-прилагательных: убирается い, добавляется くて.',
            'usage' => 'この本は高くて、大きい。 (Эта книга дорогая и большая.) / 忙しくて、疲れました。 (Был занят и устал.)'
        ],
    ];

    /**
     * Получить случайный глагол
     */
    public function getRandomVerb($group = null)
    {
        $verbs = [];
        
        if ($group && isset(self::VERBS[$group])) {
            $verbs = self::VERBS[$group];
        } else {
            foreach (self::VERBS as $groupVerbs) {
                $verbs = array_merge($verbs, $groupVerbs);
            }
        }
        
        return $verbs[array_rand($verbs)];
    }

    /**
     * Получить случайное прилагательное
     */
    public function getRandomAdjective($type = null)
    {
        $adjectives = [];
        
        if ($type && isset(self::ADJECTIVES[$type])) {
            $adjectives = self::ADJECTIVES[$type];
        } else {
            foreach (self::ADJECTIVES as $typeAdjectives) {
                $adjectives = array_merge($adjectives, $typeAdjectives);
            }
        }
        
        return $adjectives[array_rand($adjectives)];
    }

    /**
     * Спряжение глагола группы I (Godan)
     */
    public function conjugateGroup1Verb($verb, $form)
    {
        $base = mb_substr($verb, 0, -1); // Убираем последний символ (у)
        $lastChar = mb_substr($verb, -1);
        
        // Специальные случаи
        if ($verb === '行く') {
            $special = match($form) {
                'te' => '行って',
                'ta' => '行った',
                'tara' => '行ったら',
                default => null,
            };
            if ($special !== null) {
                return $special;
            }
        }
        
        // Стебли для masu формы (i-stem)
        $masu_stems = [
            'か' => 'き', 'が' => 'ぎ', 'さ' => 'し', 'た' => 'ち', 'な' => 'に',
            'ば' => 'び', 'ま' => 'み', 'ら' => 'り', 'わ' => 'い', 'く' => 'き', 'ぐ' => 'ぎ',
        ];
        
        // Стебли для nai формы (a-stem)
        $nai_stems = [
            'か' => 'か', 'が' => 'が', 'さ' => 'さ', 'た' => 'た', 'な' => 'な',
            'ば' => 'ば', 'ま' => 'ま', 'ら' => 'ら', 'わ' => 'わ', 'く' => 'か', 'ぐ' => 'が',
        ];
        
        // Специальная обработка для te/ta формы
        $getTeForm = function($base, $lastChar) use ($verb) {
            // Специальный случай для 行く
            if ($verb === '行く') {
                return '行って';
            }
            
            return match($lastChar) {
                'う', 'つ', 'る' => $base . 'って',
                'く' => $base . 'いて',
                'ぐ' => $base . 'いで',
                'ぬ', 'ぶ', 'む' => $base . 'んで',
                'す' => $base . 'して',
                default => null,
            };
        };
        
        $getTaForm = function($base, $lastChar) use ($verb) {
            // Специальный случай для 行く
            if ($verb === '行く') {
                return '行った';
            }
            
            return match($lastChar) {
                'う', 'つ', 'る' => $base . 'った',
                'く' => $base . 'いた',
                'ぐ' => $base . 'いだ',
                'ぬ', 'ぶ', 'む' => $base . 'んだ',
                'す' => $base . 'した',
                default => null,
            };
        };
        
        $masu_stem = $masu_stems[$lastChar] ?? null;
        $nai_stem = $nai_stems[$lastChar] ?? null;
        
        if (!$masu_stem || !$nai_stem) {
            return null;
        }
        
        // Volitional форма зависит от последнего символа
        $volitionalEnd = match($lastChar) {
            'く' => 'こう',
            'ぐ' => 'ごう',
            'す' => 'そう',
            'つ' => 'とう',
            'ぬ' => 'のう',
            'ぶ' => 'ぼう',
            'む' => 'もう',
            'る' => 'ろう',
            'う' => 'おう',
            default => mb_substr($verb, -1) . 'う',
        };
        
        return match($form) {
            'masu' => $base . $masu_stem . 'ます',
            'masen' => $base . $masu_stem . 'ません',
            'mashita' => $base . $masu_stem . 'ました',
            'masen_deshita' => $base . $masu_stem . 'ませんでした',
            'te' => $getTeForm($base, $lastChar),
            'ta' => $getTaForm($base, $lastChar),
            'nai' => $base . $nai_stem . 'ない',
            'nakatta' => $base . $nai_stem . 'なかった',
            'ba' => $base . $masu_stem . 'れば',
            'tara' => ($taForm = $getTaForm($base, $lastChar)) ? $taForm . 'ら' : null,
            'tai' => $base . $masu_stem . 'たい',
            'potential' => $base . $masu_stem . 'れる',
            'passive' => $base . $nai_stem . 'れる',
            'causative' => $base . $nai_stem . 'せる',
            'imperative' => $base . $masu_stem . 'れ',
            'volitional' => $base . $volitionalEnd,
            default => null,
        };
    }

    /**
     * Спряжение глагола группы II (Ichidan)
     */
    public function conjugateGroup2Verb($verb, $form)
    {
        $base = mb_substr($verb, 0, -1); // Убираем る
        
        return match($form) {
            'masu' => $base . 'ます',
            'masen' => $base . 'ません',
            'mashita' => $base . 'ました',
            'masen_deshita' => $base . 'ませんでした',
            'te' => $base . 'て',
            'ta' => $base . 'た',
            'nai' => $base . 'ない',
            'nakatta' => $base . 'なかった',
            'ba' => $base . 'れば',
            'tara' => $base . 'たら',
            'tai' => $base . 'たい',
            'potential' => $base . 'られる',
            'passive' => $base . 'られる',
            'causative' => $base . 'させる',
            'imperative' => $base . 'ろ',
            'volitional' => $base . 'よう',
            default => null,
        };
    }

    /**
     * Спряжение глагола группы III (неправильные)
     */
    public function conjugateGroup3Verb($verb, $form)
    {
        if ($verb === 'する') {
            return match($form) {
                'masu' => 'します',
                'masen' => 'しません',
                'mashita' => 'しました',
                'masen_deshita' => 'しませんでした',
                'te' => 'して',
                'ta' => 'した',
                'nai' => 'しない',
                'nakatta' => 'しなかった',
                'ba' => 'すれば',
                'tara' => 'したら',
                'tai' => 'したい',
                'potential' => 'できる',
                'passive' => 'される',
                'causative' => 'させる',
                'imperative' => 'しろ',
                'volitional' => 'しよう',
                default => null,
            };
        } elseif ($verb === '来る') {
            return match($form) {
                'masu' => '来ます',
                'masen' => '来ません',
                'mashita' => '来ました',
                'masen_deshita' => '来ませんでした',
                'te' => '来て',
                'ta' => '来た',
                'nai' => '来ない',
                'nakatta' => '来なかった',
                'ba' => '来れば',
                'tara' => '来たら',
                'tai' => '来たい',
                'potential' => '来られる',
                'passive' => '来られる',
                'causative' => '来させる',
                'imperative' => '来い',
                'volitional' => '来よう',
                default => null,
            };
        }
        
        return null;
    }

    /**
     * Спряжение глагола по группе
     */
    public function conjugateVerb($verb, $group, $form)
    {
        return match($group) {
            'group1' => $this->conjugateGroup1Verb($verb, $form),
            'group2' => $this->conjugateGroup2Verb($verb, $form),
            'group3' => $this->conjugateGroup3Verb($verb, $form),
            default => null,
        };
    }

    /**
     * Спряжение I-прилагательного
     */
    public function conjugateIAdjective($adjective, $form)
    {
        $base = mb_substr($adjective, 0, -1); // Убираем い
        
        return match($form) {
            'present' => $adjective,
            'present_negative' => $base . 'くない',
            'past' => $base . 'かった',
            'past_negative' => $base . 'くなかった',
            'adverb' => $base . 'く',
            'te' => $base . 'くて',
            default => null,
        };
    }

    /**
     * Спряжение Na-прилагательного
     */
    public function conjugateNaAdjective($adjective, $form)
    {
        return match($form) {
            'present' => $adjective . 'だ',
            'present_negative' => $adjective . 'じゃない',
            'past' => $adjective . 'だった',
            'past_negative' => $adjective . 'じゃなかった',
            'adverb' => $adjective . 'に',
            'te' => $adjective . 'で',
            default => null,
        };
    }

    /**
     * Получить все формы спряжения для глаголов
     */
    public function getVerbForms()
    {
        return self::VERB_FORMS;
    }

    /**
     * Получить все формы спряжения для прилагательных
     */
    public function getAdjectiveForms()
    {
        return self::ADJECTIVE_FORMS;
    }

    /**
     * Получить описание формы глагола
     */
    public function getVerbFormDescription($formKey)
    {
        return self::VERB_FORMS[$formKey] ?? null;
    }

    /**
     * Получить описание формы прилагательного
     */
    public function getAdjectiveFormDescription($formKey)
    {
        return self::ADJECTIVE_FORMS[$formKey] ?? null;
    }

    /**
     * Определить группу глагола
     */
    public function detectVerbGroup($verb)
    {
        // Неправильные глаголы
        if (in_array($verb, ['する', '来る'])) {
            return 'group3';
        }
        
        // Проверяем, оканчивается ли на る
        if (mb_substr($verb, -1) === 'る') {
            // Это может быть группа II (Ichidan) или группа I (Godan)
            // Проверяем список известных глаголов группы II
            $group2Verbs = array_column(self::VERBS['group2'], 'word');
            if (in_array($verb, $group2Verbs)) {
                return 'group2';
            }
            
            // Также проверяем список группы I (например, 帰る, 切る, 知る)
            $group1Verbs = array_column(self::VERBS['group1'], 'word');
            if (in_array($verb, $group1Verbs)) {
                return 'group1';
            }
            
            // По умолчанию для ру-глаголов - группа II
            // Но это эвристика, могут быть исключения
            return 'group2';
        }
        
        // Все остальные - группа I
        return 'group1';
    }
}

