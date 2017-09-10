<?php

declare(strict_types = 1);

namespace Application\Services;

use Illuminate\Support\Str;

class KeyboardService
{
    private const FUZZY_KEYS = [
        'a' => 'a4e',
        'b' => 'b68',
        'c' => 'cxs',
        'e' => 'e3a',
        'f' => 'ft',
        'g' => 'gj9',
        'i' => 'ilj1y',
        'j' => 'jgi1l',
        'l' => 'liu',
        'm' => 'mn',
        'n' => 'nm',
        'o' => 'o0',
        'p' => 'p9',
        'q' => 'q9',
        's' => 'sxz5',
        't' => 'tf7',
        'u' => 'ul',
        'v' => 'vw',
        'w' => 'wv',
        'x' => 'xzc',
        'y' => 'yi',
        'z' => 'zsx',
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
        $letters = str_split(Str::lower(utf8_decode($message)));
        $result  = null;

        foreach ($letters as $letter) {
            if (array_key_exists($letter, self::FUZZY_KEYS)) {
                $result .= '[' . self::FUZZY_KEYS[$letter] . ']{1,2}';
                continue;
            }

            $result .= preg_quote($letter, '/') . '{1,2}';
        }

        return $result;
    }
}
