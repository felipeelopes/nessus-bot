<?php

declare(strict_types = 1);

return [
    'isEmpty' => "*Aparentemente tudo tranquilo, guardião...*\n" .
                 'Não há nenhuma grade de missão em aberto.',

    'titlePlaying'  => "*Em andamento:*\n",
    'titleToday'    => "*Próximos eventos:*\n",
    'titleTomorrow' => "*Somente amanhã:*\n",

    'item'         => ":command :timing \xE2\x9E\x9C [[:players/:maxPlayers]] :reserves - *:title*:subtitle\n",
    'itemSubtitle' => ' (_:subtitle_)',

    'errorGridNotFound' => "*Err...*\n" .
                           'Não consegui localizar a grade solicitada.',
];
