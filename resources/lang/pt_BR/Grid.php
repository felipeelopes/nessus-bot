<?php

declare(strict_types = 1);

return [
    'header'         => ":icon *:title*:subtitle :icon\n\n",
    'headerSubtitle' => ' (_:subtitle_)',

    'gridOwner'         => "\xE2\x9E\x9C *Organizador*: :value \xF0\x9F\x94\xB9\n",
    'gridStatus'        => "\xE2\x9E\x9C *Status*: :value:details\n",
    'gridStatusDetails' => ' (_:details_)',
    'gridRequirements'  => "\xE2\x9E\x9C *Exigências*: :value\n",
    'gridTiming'        => "\xE2\x9E\x9C *Horário*: :value\n",
    'gridDuration'      => "\xE2\x9E\x9C *Duração*: cerca de :value\n",
    'gridPlayers'       => "\n*Participantes*: até :value\n",

    'titularsHeader'   => "\n*Titulares:*\n",
    'titularItem'      => "\xF0\x9F\x8E\xAE :gamertag :icon\n",
    'titularItemEmpty' => "\xF0\x9F\x8E\xAE \n",

    'reservesHeader' => "\n*Reservas:*\n",
    'reserveItem'    => "\xF0\x9F\x95\xB9 :gamertag :icon\n",

    'typeOwner'   => "\xE2\xAD\x90",
    'typeManager' => "\xE2\x9A\x99",
    'typeTop'     => "\xF0\x9F\x94\x9D",

    'statusWaiting'    => 'disponível',
    'statusGathering'  => 'reunindo',
    'statusPlaying'    => 'em andamento',
    'statusFinished'   => 'concluído',
    'statusCanceled'   => 'cancelado',
    'statusUnreported' => 'não reportado',

    'statusIconDefault'  => "\xE2\x98\xA0",
    'statusIconCanceled' => "\xF0\x9F\x9A\xAB",

    'timingToday'    => 'hoje, às :timing',
    'timingTomorrow' => 'amanhã, dia :day, às :timing',

    'durationHours'   => ':hours hora|:hours horas',
    'durationMinutes' => ':minutes minuto|:minutes minutos',
    'durationBoth'    => ':hours e :minutes',
];
