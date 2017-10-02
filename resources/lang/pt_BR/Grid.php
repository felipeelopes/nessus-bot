<?php

declare(strict_types = 1);

return [
    'header'            => "\xE2\x98\xA0 :title \xE2\x98\xA0\n\n",
    'headerBase'        => '*:title*:subtitle',
    'headerIconWrapper' => ":icon :header :icon\n\n",
    'headerSubtitle'    => ' (_:subtitle_)',

    'gridOwner'         => "\xE2\x9E\x9C *Organizador*: :value\n",
    'gridStatus'        => "\xE2\x9E\x9C *Status*: :value:details\n",
    'gridStatusDetails' => ' (_:details_)',
    'gridRequirements'  => "\xE2\x9E\x9C *Observações*: :value\n",
    'gridTiming'        => "\xE2\x9E\x9C *Horário*: :value\n",
    'gridDuration'      => "\xE2\x9E\x9C *Duração*: cerca de :value\n",
    'gridPlayers'       => "\n*Participantes*: até :value\n",

    'subscriberObservation' => ':gamertag (_:observation_)',

    'titularsHeader'     => "\n*Titulares:*\n",
    'titularItem'        => ":playerIcon \xE2\x9E\x9C :gamertag :icon\n",
    'titularItemEmpty'   => "\xF0\x9F\x8E\xAE \n",
    'titularDefaultIcon' => "\xF0\x9F\x8E\xAE` `",

    'reservesHeader' => "\n*Reservas:*\n",
    'reserveItem'    => ":playerIcon \xE2\x9E\x9C :gamertag :icon\n",

    'typeOwner'   => "\xE2\x86\x92 _organizador_",
    'typeManager' => "\xE2\x86\x92 _moderador_",
    'typeTop'     => "\xF0\x9F\x94\x9D",

    'statusWaiting'    => 'disponível',
    'statusGathering'  => 'reunindo',
    'statusPlaying'    => 'em andamento',
    'statusFinished'   => 'concluído',
    'statusUnreported' => 'concluído, mas não reportado',
    'statusCanceled'   => 'cancelado',

    'statusIconGathering'  => "\xF0\x9F\x8C\x80",
    'statusIconPlaying'    => "\xF0\x9F\x85\xBF",
    'statusIconFinished'   => "\xF0\x9F\x8F\x86",
    'statusIconUnreported' => "\xF0\x9F\x92\xA4",
    'statusIconCanceled'   => "\xF0\x9F\x9A\xAB",

    'timingToday'    => 'hoje, às :timing',
    'timingTomorrow' => 'amanhã, dia :day, às :timing',

    'durationHours'   => ':hours hora|:hours horas',
    'durationMinutes' => '[0,1]:minutes minuto|:minutes minutos',
    'durationBoth'    => ':hours e :minutes',
];
