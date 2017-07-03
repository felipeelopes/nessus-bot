<?php

declare(strict_types = 1);

$confirmTimingYes   = 'Confirmar';
$confirmCreationYes = 'Confirmar';

return [
    'creationBeta' => "\xE2\x9A\xA0 *Atenção, guardião...*\n" .
                      "Esta é uma versão de testes do *Sistema de Inscrição de Grades da Vanguarda* (_SIGV_).\n\n" .
                      "\xE2\x9E\x9C No momento, o sistema apenas criará a grade e publicará no grupo. " .
                      "Os recursos de notificação e participação ainda estão em desenvolvimento.\n\n" .
                      "\xE2\x9E\x9C Se tiver alguma sugestão, este será o melhor momento para discutí-lo.",

    'creationWizard'        => "*Percebi a presença de inimigos, guardião!*\n" .
                               "Precisamos definir um plano de missão para não deixá-los avançar.\n\n" .
                               "*\xE2\x9E\x9C Qual será o título da missão?*\n" .
                               'Obrigatório (até 80 caracteres).',
    'creationWizardOptions' => [
        [ 'value' => 'Câmara de Cristal', 'description' => '*Incursão*: Câmara de Cristal' ],
        [ 'value' => 'Fim de Crota', 'description' => '*Incursão*: Fim de Crota' ],
        [ 'value' => 'Queda do Rei', 'description' => '*Incursão*: Queda do Rei' ],
        [ 'value' => 'Ira da Máquina', 'description' => '*Incursão*: Ira da Máquina' ],
        [ 'value' => 'Anoitecer', 'description' => '*Semanal*: Anoitecer' ],
    ],

    'creationWizardSubtitle'        => "*\xE2\x9E\x9C Qual será o subtítulo da missão?*\n" .
                                       'Opcional (até 20 caracteres).',
    'creationWizardSubtitleOptions' => [
        [ 'description' => 'Nenhum' ],
        [ 'value' => 'Normal' ],
        [ 'value' => 'Heróico' ],
        [ 'value' => 'Heróico com Desafio' ],
    ],

    'creationWizardObservations'        => "*\xE2\x9E\x9C Quais serão as exigências da missão?*\n" .
                                           'Opcional (até 400 caracteres).',
    'creationWizardObservationsOptions' => [
        [ 'description' => 'Nenhuma' ],
    ],

    'creationWizardTiming' => "*\xE2\x9E\x9C Que horas será a missão?*\n" .
                              'Digite no formato HH:MM (ex. 10:30).',

    'creationWizardTimingConfirm'         => "*\xE2\x9E\x9C Confirmar o horário para :timing?*\n" .
                                             'Se estiver errado, digite um novo horário.',
    'creationWizardTimingConfirmToday'    => 'hoje, às :timing',
    'creationWizardTimingConfirmTomorrow' => 'amanhã, dia :day às :timing',
    'creationWizardTimingConfirmOptions'  => [
        [ 'value' => $confirmTimingYes ],
    ],
    'creationWizardTimingConfirmYes'      => $confirmTimingYes,

    'creationWizardPlayers'        => "*\xE2\x9E\x9C Quantos participarão do esquadrão?*\n" .
                                      'Digite um valor entre 2 e :max.',
    'creationWizardPlayersOptions' => [
        [ 'value' => '6', 'description' => '6 (_ex. incursões_)' ],
        [ 'value' => '4', 'description' => '4 (_ex. crisol_)' ],
        [ 'value' => '3', 'description' => '3 (_ex. anoitecer_)' ],
    ],

    'creationWizardConfirmCreationHeader'  => "*Esta é a última etapa, guardião!*\n" .
                                              'Se tudo estiver certo, basta confirmar.',
    'creationWizardConfirmCreationOptions' => [
        [ 'value' => $confirmCreationYes, 'description' => 'Confirmar e publicar no grupo' ],
    ],
    'creationWizardConfirmCreationYes'     => $confirmCreationYes,

    'creationWizardPublished' => "*Está pronto, guardião!*\n" .
                                 'A grade foi publicada no grupo.',

    'errorTitleTooLong'        => "*O título ficou grande demais!*\n" .
                                  "Tente não ultrapassar :length caracteres.\n\n" .
                                  "*\xE2\x9E\x9C Qual será o título da missão?*",
    'errorSubtitleTooLong'     => "*O subtítulo ficou grande demais!*\n" .
                                  "Tente não ultrapassar :length caracteres.\n\n" .
                                  "*\xE2\x9E\x9C Qual será o subtítulo da missão?*",
    'errorObservationsTooLong' => "*São exigências demais!*\n" .
                                  "Tente não ultrapassar :length caracteres.\n\n" .
                                  "*\xE2\x9E\x9C Quais serão as exigências da missão?*",
    'errorTimingInvalid'       => "*O formato informado não é válido!*\n" .
                                  "Digite no formato HH:MM (ex. 10:30).\n\n" .
                                  "*\xE2\x9E\x9C Que horas será a missão?*",
    'errorTimingTooShort'      => "*O horário está muito próximo!*\n" .
                                  "O tempo mínimo é de 15 minutos.\n\n" .
                                  "*\xE2\x9E\x9C Que horas será a missão?*",
    'errorPlayersInvalid'      => "*O número de participantes informado não é válido!*\n" .
                                  "Digite um valor entre 2 e :max.\n\n" .
                                  "*\xE2\x9E\x9C Quantos participarão do esquadrão?*",
    'errorPlayersTooFew'       => "*O número de participantes não pode ser inferior a 2!*\n" .
                                  "Digite um valor entre 2 e :max.\n\n" .
                                  "*\xE2\x9E\x9C Quantos participarão do esquadrão?*",
    'errorPlayersTooMuch'      => "*O número de participantes não pode ser superior a :max!*\n" .
                                  "Digite um valor entre 2 e :max.\n\n" .
                                  "*\xE2\x9E\x9C Quantos participarão do esquadrão?*",
    'errorPublishInvalid'      => "*As opções aqui são poucas, guardião...*\n" .
                                  'Podemos publicar ou cancelar. Você decide!',
];
