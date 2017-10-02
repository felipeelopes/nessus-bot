<?php

declare(strict_types = 1);

return [
    'titleStarter'   => 'Aprendiz',
    'titleDecorated' => 'Condecorado',
    'titleBronze'    => 'Bronze',
    'titleSilver'    => 'Prata',
    'titleGold'      => 'Ouro',
    'titleDiamond'   => 'Diamante',
    'titlePatron'    => 'Patrono',

    'iconStarter'   => "\xF0\x9F\xA5\x9A",
    'iconDecorated' => "\xF0\x9F\x8E\x96",
    'iconBronze'    => "\xF0\x9F\xA5\x89",
    'iconSilver'    => "\xF0\x9F\xA5\x88",
    'iconGold'      => "\xF0\x9F\xA5\x87",
    'iconDiamond'   => "\xF0\x9F\x92\x8E",
    'iconPatron'    => "\xF0\x9F\x91\x91",

    'rankingGrid' => "*Status de :gamertag:*\n" .
                     "_Considerando os últimos 45 dias!_\n\n" .
                     "`:title`\n" .
                     "`[:bar] :percent`\n" .
                     "` :xp XP`\n\n" .
                     "*Próximo nível:* :nextLevel\n" .
                     "*Atividades:* :activities atividades em grupo\n" .
                     "*Horas de jogo:* :hours horas em grupo\n" .
                     "*Interações:* :interactions membros\n" .
                     '*Registrado há:* :days dias',

    'barFilled' => "\xE2\x96\xA0",
    'barEmpty'  => "\xE2\x96\xA1",

    'nextLevelRequirement' => 'requer `:xp XP`',
    'nextLevelLimited'     => '_impossível_',

    'errorNotFound' => "*Err... desculpe guardião!*\n" .
                       'Não consegui localizar suas atividades no momento.',
];
