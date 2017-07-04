<?php

declare(strict_types = 1);

namespace Application\Services;

class FormattingService
{
    private const SUPERSCRIPTED_NUMBERS = [
        0 => "\xE2\x81\xB0",
        1 => "\xC2\xB9",
        2 => "\xC2\xB2",
        3 => "\xC2\xB3",
        4 => "\xE2\x81\xB4",
        5 => "\xE2\x81\xB5",
        6 => "\xE2\x81\xB6",
        7 => "\xE2\x81\xB7",
        8 => "\xE2\x81\xB8",
        9 => "\xE2\x81\xB9",
    ];

    /**
     * Limit a text by ellipsis.
     * @param string $text  Text to limit.
     * @param string $limit Limit size.
     * @return string
     */
    public static function ellipsis($text, $limit): string
    {
        if (strlen($text) >= $limit) {
            return substr($text, 0, $limit - 1) . "\xE2\x80\xA6";
        }

        return $text;
    }

    /**
     * Convert the number to superscripted number.
     * @param int $number Number to superscript.
     * @return string
     */
    public static function toSuperscript($number): string
    {
        $numbers = str_split($number);
        $result  = '';

        foreach ($numbers as $n) {
            $result .= self::SUPERSCRIPTED_NUMBERS[$n];
        }

        return $result;
    }
}
