<?php

declare(strict_types = 1);

$preDownload = "\n\n\xE2\x9E\x9C Download prévio disponível na *Xbox Live* (_34.9GB_).";

return [
    'launchDays'  => "\xE2\x9E\x9C O *Destiny 2* deverá ser lançado em *:date* às *00:00*.\nFaltam apenas *:days dias*!{$preDownload}",
    'launchHours' => "\xE2\x9E\x9C O *Destiny 2* deverá ser lançado em *:date* às *00:00*.\nFaltam menos de *:hours horas*!{$preDownload}",
    'launchToday' => "\xE2\x9E\x9C O *Destiny 2* deverá ser lançado *hoje*.\nFaltam menos de *:hours horas*!{$preDownload}",
    'launchSoon'  => "\xE2\x9E\x9C O *Destiny 2* deverá ser lançado *hoje*.\nFaltam apenas alguns instantes!{$preDownload}",
    'launched'    => "\xE2\x9E\x9C O *Destiny 2* já deve estar a todo vapor!\nVamos recuperar nossa Luz, guardião!",

    'gtEmpty'              => "Para usar este comando, especifique os usuários via menção.\n" .
                              '*Digite:* /:command @...',
    'gtSingleRegistered'   => '*Gamertag:* :gamertag',
    'gtSingleUnregistered' => '_Gamertag não registrada._',
    'gtItemRegistered'     => ":mention\n\xE2\x86\x92 :gamertag\n\n",
    'gtItemUnregistered'   => ":mention\n\xE2\x86\x92 _Gamertag não registrada._\n\n",

    'searchGtEmpty'      => "*Nenhum resultado...*\n" .
                            'Não consegui achar uma Gamertag parecida.',
    'searchGtFewLetters' => "*Err...*\n" .
                            'Preciso de pelo menos três letras para pesquisar por alguém.',
    'searchGtSingle'     => "*Encontrei!*\n" .
                            "\xE2\x9E\x9C *GT:* :gamertag \xE2\x86\x92 :mention",
    'searchGtSimilarity' => "*Encontrei alguém parecido:*\n" .
                            "\xE2\x9E\x9C *GT:* :gamertag \xE2\x86\x92 :mention",

    'systemRefreshed' => 'Recursos de sistema atualizados.',

    'goodAfternoon' => '*Boa tarde, guardiões!*',
    'goodEarly'     => '*Boa madrugada, guardiões!*',
    'goodEvening'   => '*Boa noite, guardiões!*',
    'goodMorning'   => '*Bom dia, guardiões!*',

    'clanList' => "*Nossos clãs:*\n\n" .
                  "\xE2\x9E\x9C [Os Incursores](https://www.bungie.net/en/ClanV2/?groupid=1843400) (_vagas indisponíveis_)\n" .
                  "\xE2\x9E\x9C [Os Incursores II](https://www.bungie.net/en/ClanV2/?groupid=1866448) (_vagas indisponíveis_)\n" .
                  "\xE2\x9E\x9C [Os Incursores III](https://www.bungie.net/en/ClanV2/?groupid=1900391) (*vagas disponíveis*)\n\n" .
                  "*Convide um amigo:*\n\n" .
                  "\xE2\x9E\x9C [Convite do Telegram](https://t.me/joinchat/EZL8wkRSnDHaBcpv7KoQ4g)",

    'banEmpty'    => "*Err...*\n" .
                     'Após este comando, informe a *Gamertag completa* do usuário.',
    'banNotFound' => "*Err...*\n" .
                     'Não foi possível localizar esta Gamertag em nosso banco.',
    'banSuccess'  => 'Membro removido do grupo e de nosso banco de dados com sucesso!',
];
