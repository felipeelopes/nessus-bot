<?php

declare(strict_types = 1);

$preDownload = "\n\n\xE2\x9E\x9C Download prévio disponível na *Xbox Live* (_29.1GB_).";

return [
    'launchDays'  => "\xE2\x9E\x9C O *Destiny 2* deverá ser lançado em *:date*.\nFaltam apenas :days dias!{$preDownload}",
    'launchHours' => "\xE2\x9E\x9C O *Destiny 2* deverá ser lançado em *:date*.\nFaltam menos de :hours horas!{$preDownload}",
    'launchToday' => "\xE2\x9E\x9C O *Destiny 2* deverá ser lançado *hoje*.\nFaltam menos de :hours horas!{$preDownload}",
    'launchSoon'  => "\xE2\x9E\x9C O *Destiny 2* deverá ser lançado *hoje*.\nFaltam apenas alguns instantes!{$preDownload}",
    'launched'    => "\xE2\x9E\x9C O *Destiny 2* já deve estar a todo vapor!\nVamos recuperar nossa Luz, guardião!",

    'gtEmpty'              => "Para usar este comando, especifique os usuários via menção.\n" .
                              '*Digite:* /:command @...',
    'gtSingleRegistered'   => '*Gamertag:* :gamertag',
    'gtSingleUnregistered' => '_Gamertag não registrada._',
    'gtItemRegistered'     => "*:mention*\n\xE2\x86\x92 :gamertag\n\n",
    'gtItemUnregistered'   => "*:mention*\n\xE2\x86\x92 _Gamertag não registrada._\n\n",

    'searchGtEmpty'                    => "*Nenhum resultado...*\n" .
                                          'Não consegui achar uma Gamertag parecida.',
    'searchGtSingle'                   => "*Encontrei!*\n" .
                                          "\xE2\x86\x92 :mention",
    'searchGtSimilaritySingle'         => "*Encontrei alguém parecido:*\n" .
                                          "\xE2\x9E\x9C :gamertag \xE2\x86\x92 :mention",
    'searchGtSimilarityMultipleHeader' => "*Encontrei alguns similares:*\n" .
                                          ':items',
    'searchGtSimilarityMultipleItem'   => "\xE2\x9E\x9C :gamertag \xE2\x86\x92 :mention\n",

    'systemRefreshed' => 'Recursos de sistema atualizados.',
];
