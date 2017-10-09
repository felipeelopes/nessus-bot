<?php

declare(strict_types = 1);

return [
    'statsHeader' => "\xF0\x9F\x8F\x86 *Estatísticas do clã (PvE):*\n\n" .
                     ":contents\n\n" .
                     '*Última atualização:* :datetime',
    'statsGroup'  => "\n*:title*:\n",
    'statsItem'   => "\xE2\x9E\x9C *:title*: :value \xE2\x86\x92 _:gamertag_\n",

    'statsHeaderSelf' => "\xF0\x9F\x8F\x86 *Estatísticas de :gamertag:*\n\n" .
                         ":contents\n\n" .
                         '*Total:* `:points` pontos (_soma de todos os percentuais_)',
    'statsItemSelf'   => "`:percent` \xE2\x9E\x9C *:title*: :value\n",

    'rankingHeader'    => "*Ranking por Atividade:*\n\n" .
                          ":pointers\n",
    'rankingPointer'   => ":rankingº `:icon` _:gamertag_ (`:xp` xp):you\n",
    'rankingSeparator' => "---\n",
    'rankingYou'       => " \xF0\x9F\x91\x88",

    'groupAdventures'  => 'Aventureiros do Clã',
    'groupIluminateds' => 'Iluminados do Clã',
    'groupAssists'     => 'Assistentes do Clã',
    'groupHawkEye'     => 'Olhos-de-águia do Clã',
    'groupTriggers'    => 'Gatilhos-rápidos do Clã',
    'groupInvencibles' => 'Invencíveis do Clã',

    'titleSecondsPlayed'               => 'Tempo de jogo',
    'titleAverageLifespan'             => 'Tempo médio de vida',
    'titleLongestSingleLife'           => 'Tempo de vida',
    'titlePublicEventsCompleted'       => 'Eventos públicos concluídos',
    'titleHeroicPublicEventsCompleted' => 'Eventos públicos heróicos concluídos',
    'titleAdventuresCompleted'         => 'Jornadas concluídas',
    'titleActivitiesCleared'           => 'Atividades concluídas',
    'titleBestSingleGameKills'         => 'Baixas em uma única atividade',

    'titleWeaponKillsSuper'   => 'Baixas com Super',
    'titleWeaponKillsGrenade' => 'Baixas com Granada',
    'titleWeaponKillsMelee'   => 'Baixas corpo-a-corpo',
    'titleOrbsDropped'        => 'Orbes geradas',
    'titleOrbsGathered'       => 'Orbes coletadas',

    'titleAssists'                => 'Baixas por assistência',
    'titleResurrectionsPerformed' => 'Ressureições performadas',
    'titleKillsDeathsAssists'     => 'Relação K/D assistenciais',

    'titleLongestKillDistance' => 'Baixa mais distante',
    'titleAverageKillDistance' => 'Distância média de baixas',
    'titleTotalKillDistance'   => 'Distância acumulada das baixas',
    'titlePrecisionKills'      => 'Baixas precisas',
    'titleMostPrecisionKills'  => 'Baixas hit-kills',

    'titleKills'                     => 'Total de baixas',
    'titleWeaponKillsAutoRifle'      => 'Baixas com fuzís automáticos',
    'titleWeaponKillsFusionRifle'    => 'Baixas com fuzís de fusão',
    'titleWeaponKillsHandCannon'     => 'Baixas com canhões de mão',
    'titleWeaponKillsMachinegun'     => 'Baixas com metralhadoras',
    'titleWeaponKillsPulseRifle'     => 'Baixas com fuzís de pulso',
    'titleWeaponKillsRocketLauncher' => 'Baixas com lança-mísseis',
    'titleWeaponKillsScoutRifle'     => 'Baixas com fuzís de batedor',
    'titleWeaponKillsShotgun'        => 'Baixas com escopetas',
    'titleWeaponKillsSniper'         => 'Baixas com snipers',
    'titleWeaponKillsSubmachinegun'  => 'Baixas com submetralhadoras',
    'titleWeaponKillsSideArm'        => 'Baixas com pistolas',
    'titleWeaponKillsSword'          => 'Baixas com espadas',
    'titleWeaponKillsRelic'          => 'Baixas com armas de ambiente',
    'titleLongestKillSpree'          => 'Baixas em sequência',
    'titleKillsDeathsRatio'          => 'Relação K/D PvE',

    'titleDeaths'                => 'Menor número de mortes',
    'titleSuicides'              => 'Menor número de suicídios',
    'titleResurrectionsReceived' => 'Menor número de ressureições recebidas',

    'typeHours'   => 'horas',
    'typeMinutes' => 'minutos',
    'typeMeters'  => 'metros',

    'modeDaily' => ':value por dia',

    'activitiesRequest' => "*Estamos verificando suas últimas atividades, guardião!*\n" .
                           'Aguarde alguns segundos...',
];
