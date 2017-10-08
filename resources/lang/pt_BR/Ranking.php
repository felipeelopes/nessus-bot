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
                     "_Registrado há :days dias._\n\n" .
                     "*:title*\n" .
                     "`[:bar] :percent`\n" .
                     "` :xp XP`\n\n" .
                     "*Próximo nível:* :nextLevel\n\n" .
                     "*Atividades em grupo:* :activities\n" .
                     "*Horas em grupo:* :hours\n" .
                     '*Interações:* :interactions membros',

    'levelAdvanced' => "\xF0\x9F\x8F\xB5 *:gamertag* agora é `:level`.",

    'dailyReport'        => "*Aqui está seu relatório diário, guardião...*\n\n" .
                            "_Você só receberá este relatório nos dias em que jogar Destiny 2. O relatório é gerado sempre após as 20h30 de cada dia._\n\n" .
                            "*Experiência adquirida:* `+:xpAdded XP`\n" .
                            "*Experiência total:* `:xpTotal XP`\n\n",
    'dailyLevelAdvanced' => "\xF0\x9F\x8F\xB5 Agora você é `:level`.",
    'dailyLevelSame'     => "\xF0\x9F\x94\x85 Você ainda é `:level`, mas faltam apenas `:xpRequired XP` para o próximo nível.",

    'barFilled' => "\xE2\x96\xA0",
    'barEmpty'  => "\xE2\x96\xA1",

    'nextLevelRequirement' => 'requer `:xp XP`',
    'nextLevelLimited'     => '_impossível_',

    'errorNotFound' => "*Err... desculpe guardião!*\n" .
                       'Não consegui localizar suas atividades no momento.',
];
