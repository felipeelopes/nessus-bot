<?php

declare(strict_types = 1);

return [
    'isEmpty'     => "*Aparentemente tudo tranquilo, guardião...*\n" .
                     'Não há nenhuma grade em aberto.',
    'isEmptyYour' => "*Aparentemente tudo tranquilo, guardião...*\n" .
                     'Não há nenhuma grade *sua* em aberto.',

    'titleBase'     => "*:title:*\n",
    'titlePlaying'  => "*Em andamento:*\n",
    'titleToday'    => "*Próximos eventos:*\n",
    'titleTomorrow' => "*Somente amanhã:*\n",

    'item'         => ":command :timing \xE2\x9E\x9C [[:players/:maxPlayers]] :reserves - *:title*:subtitle :incomplete\n",
    'itemSubtitle' => ' (_:subtitle_)',

    'incompleteIcon' => "\xF0\x9F\x94\x8D",

    'errorGridNotFound' => "*Err...*\n" .
                           'Não consegui localizar a grade solicitada.',
];
