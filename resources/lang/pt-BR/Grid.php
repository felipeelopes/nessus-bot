<?php

declare(strict_types = 1);

return [
    'header'            => "\xE2\x98\xA0 *:title*:subtitle \xE2\x98\xA0\n\n",
    'headerSubtitle'    => ' (_:subtitle_)',

    // Grid properties.
    'gridOwner'         => "\xE2\x9E\x9C *Organizador*: :value \xF0\x9F\x94\xB9\n",
    'gridStatus'        => "\xE2\x9E\x9C *Status*: :value:details\n",
    'gridStatusDetails' => ' (_:details_)',
    'gridRequirements'  => "\xE2\x9E\x9C *Exigências*: :value\n",
    'gridTiming'        => "\xE2\x9E\x9C *Horário*: :value\n",
    'gridPlayers'       => "\n*Participantes*: até :value\n",

    // Grid titulars.
    'titularsHeader'    => "\n*Titulares:*\n",
    'titularItem'       => "\xF0\x9F\x8E\xAE :gamertag:icon\n",
    'titularItemEmpty'  => "\xF0\x9F\x8E\xAE \n",

    // Grid reserves.
    'reservesHeader'    => "\n*Reservas:*\n",
    'reserveItem'       => "\xF0\x9F\x95\xB9 :gamertag:icon\n",

    // Grid user type.
    'typeOwner'         => "\xF0\x9F\x94\xB9",
    'typeManager'       => "\xF0\x9F\x94\xB8",
    'typeTop'           => "\xF0\x9F\x94\x9D",

    // Grid statuses.
    'statusWaiting'     => 'disponível',
    'statusGathering'   => 'reunindo...',
    'statusPlaying'     => 'em andamento',
    'statusFinished'    => 'concluído',
    'statusCanceled'    => 'cancelado',

    // Timing properties.
    'timingToday'       => 'hoje, às :timing',
    'timingTomorrow'    => 'amanhã, dia :day às :timing',
];
