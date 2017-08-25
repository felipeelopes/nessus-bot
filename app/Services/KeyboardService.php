<?php

declare(strict_types = 1);

namespace Application\Services;

use Illuminate\Support\Str;

class KeyboardService
{
    private const FUZZY_KEYS = [
        'a' => 'aqwsxz4e',
        'b' => 'bvfghn 68',
        'c' => 'cxsdfv ',
        'd' => 'dswerfvcx',
        'e' => 'ew234rfds3a',
        'f' => 'fdertgbvc',
        'g' => 'gfrtyhnbv9',
        'h' => 'hgtyujmnb',
        'i' => 'iu789olkj1y',
        'j' => 'jhyuikmn1l',
        'k' => 'kjuiolm',
        'l' => 'lkiopç',
        'm' => 'mnhjk',
        'n' => 'nbghjm',
        'o' => 'oi890pçlk',
        'p' => 'po90çl',
        'q' => 'q12wsa9',
        'r' => 're345tgfd',
        's' => 'saqwedcxz5',
        't' => 'tr456yhgf7',
        'u' => 'uy678ikjha',
        'v' => 'vcdfgb ',
        'w' => 'wq123edsa',
        'x' => 'xzasdc ',
        'y' => 'yt567ujhgi',
        'z' => 'zasxe',
        ' ' => ' zxcvbnm',
    ];

    /**
     * @inheritdoc
     */
    public static function getInstance(): KeyboardService
    {
        return MockupService::getInstance()->instance(static::class);
    }

    /**
     * Generate a fuzzy regular expression from a message.
     * It should help found users with common errors on mobile keyboard.
     * @param string $message Message to fuzzy.
     * @return string
     */
    public function generateFuzzyExpression(string $message): string
    {
        $letters = str_split(Str::lower($message));
        $result  = null;

        foreach ($letters as $letter) {
            if (array_key_exists($letter, self::FUZZY_KEYS)) {
                $result .= '[' . self::FUZZY_KEYS[$letter] . ']{1,2}';
                continue;
            }

            $result .= preg_quote($letter, '/');
        }

        return $result;
    }
}
