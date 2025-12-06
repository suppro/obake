<?php

namespace Database\Seeders;

use App\Models\Kanji;
use Illuminate\Database\Seeder;

class KanjiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // N5 кандзи (начальный уровень)
        $n5Kanji = [
            ['kanji' => '今', 'translation_ru' => 'сейчас', 'stroke_count' => 4, 'jlpt_level' => 5],
            ['kanji' => '本', 'translation_ru' => 'книга', 'stroke_count' => 5, 'jlpt_level' => 5],
            ['kanji' => '日', 'translation_ru' => 'день', 'stroke_count' => 4, 'jlpt_level' => 5],
            ['kanji' => '月', 'translation_ru' => 'месяц', 'stroke_count' => 4, 'jlpt_level' => 5],
            ['kanji' => '年', 'translation_ru' => 'год', 'stroke_count' => 6, 'jlpt_level' => 5],
            ['kanji' => '人', 'translation_ru' => 'человек', 'stroke_count' => 2, 'jlpt_level' => 5],
            ['kanji' => '大', 'translation_ru' => 'большой', 'stroke_count' => 3, 'jlpt_level' => 5],
            ['kanji' => '小', 'translation_ru' => 'маленький', 'stroke_count' => 3, 'jlpt_level' => 5],
            ['kanji' => '中', 'translation_ru' => 'середина', 'stroke_count' => 4, 'jlpt_level' => 5],
            ['kanji' => '上', 'translation_ru' => 'верх', 'stroke_count' => 3, 'jlpt_level' => 5],
            ['kanji' => '下', 'translation_ru' => 'низ', 'stroke_count' => 3, 'jlpt_level' => 5],
            ['kanji' => '水', 'translation_ru' => 'вода', 'stroke_count' => 4, 'jlpt_level' => 5],
            ['kanji' => '火', 'translation_ru' => 'огонь', 'stroke_count' => 4, 'jlpt_level' => 5],
            ['kanji' => '木', 'translation_ru' => 'дерево', 'stroke_count' => 4, 'jlpt_level' => 5],
            ['kanji' => '金', 'translation_ru' => 'золото', 'stroke_count' => 8, 'jlpt_level' => 5],
            ['kanji' => '土', 'translation_ru' => 'земля', 'stroke_count' => 3, 'jlpt_level' => 5],
            ['kanji' => '山', 'translation_ru' => 'гора', 'stroke_count' => 3, 'jlpt_level' => 5],
            ['kanji' => '川', 'translation_ru' => 'река', 'stroke_count' => 3, 'jlpt_level' => 5],
            ['kanji' => '田', 'translation_ru' => 'поле', 'stroke_count' => 5, 'jlpt_level' => 5],
            ['kanji' => '口', 'translation_ru' => 'рот', 'stroke_count' => 3, 'jlpt_level' => 5],
            ['kanji' => '目', 'translation_ru' => 'глаз', 'stroke_count' => 5, 'jlpt_level' => 5],
            ['kanji' => '耳', 'translation_ru' => 'ухо', 'stroke_count' => 6, 'jlpt_level' => 5],
            ['kanji' => '手', 'translation_ru' => 'рука', 'stroke_count' => 4, 'jlpt_level' => 5],
            ['kanji' => '足', 'translation_ru' => 'нога', 'stroke_count' => 7, 'jlpt_level' => 5],
            ['kanji' => '心', 'translation_ru' => 'сердце', 'stroke_count' => 4, 'jlpt_level' => 5],
            ['kanji' => '生', 'translation_ru' => 'жизнь', 'stroke_count' => 5, 'jlpt_level' => 5],
            ['kanji' => '見', 'translation_ru' => 'видеть', 'stroke_count' => 7, 'jlpt_level' => 5],
            ['kanji' => '行', 'translation_ru' => 'идти', 'stroke_count' => 6, 'jlpt_level' => 5],
            ['kanji' => '来', 'translation_ru' => 'приходить', 'stroke_count' => 7, 'jlpt_level' => 5],
            ['kanji' => '多', 'translation_ru' => 'много', 'stroke_count' => 6, 'jlpt_level' => 5],
            ['kanji' => '少', 'translation_ru' => 'мало', 'stroke_count' => 4, 'jlpt_level' => 5],
        ];

        // N4 кандзи (базовый уровень)
        $n4Kanji = [
            ['kanji' => '車', 'translation_ru' => 'машина', 'stroke_count' => 7, 'jlpt_level' => 4],
            ['kanji' => '電', 'translation_ru' => 'электричество', 'stroke_count' => 13, 'jlpt_level' => 4],
            ['kanji' => '話', 'translation_ru' => 'разговор', 'stroke_count' => 13, 'jlpt_level' => 4],
            ['kanji' => '語', 'translation_ru' => 'язык', 'stroke_count' => 14, 'jlpt_level' => 4],
            ['kanji' => '学', 'translation_ru' => 'учение', 'stroke_count' => 8, 'jlpt_level' => 4],
            ['kanji' => '校', 'translation_ru' => 'школа', 'stroke_count' => 10, 'jlpt_level' => 4],
            ['kanji' => '食', 'translation_ru' => 'еда', 'stroke_count' => 9, 'jlpt_level' => 4],
            ['kanji' => '飲', 'translation_ru' => 'пить', 'stroke_count' => 12, 'jlpt_level' => 4],
            ['kanji' => '聞', 'translation_ru' => 'слышать', 'stroke_count' => 14, 'jlpt_level' => 4],
            ['kanji' => '読', 'translation_ru' => 'читать', 'stroke_count' => 14, 'jlpt_level' => 4],
            ['kanji' => '書', 'translation_ru' => 'писать', 'stroke_count' => 10, 'jlpt_level' => 4],
            ['kanji' => '帰', 'translation_ru' => 'возвращаться', 'stroke_count' => 10, 'jlpt_level' => 4],
            ['kanji' => '買', 'translation_ru' => 'покупать', 'stroke_count' => 12, 'jlpt_level' => 4],
            ['kanji' => '売', 'translation_ru' => 'продавать', 'stroke_count' => 7, 'jlpt_level' => 4],
            ['kanji' => '新', 'translation_ru' => 'новый', 'stroke_count' => 13, 'jlpt_level' => 4],
            ['kanji' => '古', 'translation_ru' => 'старый', 'stroke_count' => 5, 'jlpt_level' => 4],
            ['kanji' => '高', 'translation_ru' => 'высокий', 'stroke_count' => 10, 'jlpt_level' => 4],
            ['kanji' => '低', 'translation_ru' => 'низкий', 'stroke_count' => 7, 'jlpt_level' => 4],
            ['kanji' => '長', 'translation_ru' => 'длинный', 'stroke_count' => 8, 'jlpt_level' => 4],
            ['kanji' => '短', 'translation_ru' => 'короткий', 'stroke_count' => 12, 'jlpt_level' => 4],
        ];

        $allKanji = array_merge($n5Kanji, $n4Kanji);

        foreach ($allKanji as $kanji) {
            Kanji::firstOrCreate(
                ['kanji' => $kanji['kanji']],
                [
                    'translation_ru' => $kanji['translation_ru'],
                    'stroke_count' => $kanji['stroke_count'],
                    'jlpt_level' => $kanji['jlpt_level'],
                    'is_active' => true,
                ]
            );
        }
    }
}
