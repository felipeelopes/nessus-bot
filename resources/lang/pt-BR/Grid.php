<?php

declare(strict_types = 1);

return [
    'header'           => "\xE2\x98\xA0 *:title*:subtitle \xE2\x98\xA0\n\n",
    'headerSubtitle'   => ' (_:subtitle_)',

    // Grid properties.
    'gridOwner'        => "\xE2\x9E\x9C *Organizador*: :value\n",
    'gridObservations' => "\xE2\x9E\x9C *Observações*: :value\n",
    'gridTiming'       => "\xE2\x9E\x9C *Horário*: :value\n",
    'gridPlayers'      => "\n*Participantes*: até :value\n",

    // Grid titulars.
    'titularsHeader'   => "\n*Titulares:*\n",
    'titularItem'      => "\xF0\x9F\x8E\xAE :gamertag :icon\n",

    // Grid reserves.
    'reservesHeader'   => "\n*Reservas:*\n",
    'reserveItem'      => "\xF0\x9F\x95\xB9 \n",

    // Grid user type.
    'typeOwner'        => "\xF0\x9F\x94\xB9",

    // Timing properties.
    'timingTomorrow'   => ' (*amanhã*, dia :day)',
];
