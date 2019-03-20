<?php
/**
 * Created by PhpStorm.
 * User: APB
 * Date: 3/12/2019
 * Time: 11:45 PM
 */

namespace App\Helper;


class TranslitHelper
{
    private const DATA = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'g', 'з' => 'z', 'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '',
        'ы' => 'i', 'ь' => '', 'э' => 'e', 'ю' => 'u', 'я' => 'ya', ''
    ];

    public static function translit(string $text)
    {
        $text = str_replace(' ', '-', mb_strtolower($text));

        foreach (self::DATA as $ru => $en) {
            $text = str_replace($ru, $en, $text);
        }

        return $text;
    }
}