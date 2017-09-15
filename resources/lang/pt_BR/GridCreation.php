<?php

declare(strict_types = 1);

use Application\Services\CommandService;

$confirmTimingYes   = 'Confirmar';
$confirmCreationYes = 'Confirmar';

return [
    'creationWizard'        => "*Percebi a presença de inimigos, guardião!*\n" .
                               "Precisamos definir um plano de missão para não deixá-los avançar.\n\n" .
                               "*\xE2\x9E\x9C Qual será o título da missão?*\n\n" .
                               'Você pode *escolher uma das opções abaixo* ou *digitar um título diferente* com até 80 caracteres.',
    'creationWizardOptions' => [
        [ 'value' => 'Leviatã', 'description' => '*Incursão*: Leviatã' ],
        [ 'value' => 'Anoitecer', 'description' => '*Semanal*: Anoitecer' ],
        [ 'value' => 'Assalto', 'description' => '*Semanal*: Assalto' ],
        [ 'value' => 'Desafio dos Nove', 'description' => '*Semanal*: Desafio dos Nove' ],
        [ 'value' => 'Modo Campanha', 'description' => 'Modo Campanha' ],
    ],

    'creationWizardSubtitle'        => "*\xE2\x9E\x9C Qual será o subtítulo da missão?*\n" .
                                       'Opcional (até 20 caracteres).',
    'creationWizardSubtitleOptions' => [
        [ 'description' => 'Nenhum' ],
        [ 'value' => 'Normal' ],
        [ 'value' => 'Prestígio' ],
    ],

    'creationWizardRequirements'        => "*\xE2\x9E\x9C Quais serão as observações da missão?*\n" .
                                           'Opcional (até :max caracteres).',
    'creationWizardRequirementsOptions' => [
        [ 'description' => 'Nenhuma' ],
    ],

    'creationWizardTiming' => "*\xE2\x9E\x9C Que horas será a missão?*\n" .
                              'Digite no formato HH:MM (ex. 10:30).',

    'creationWizardTimingConfirm'         => "*\xE2\x9E\x9C Confirmar o horário para :timing?*\n" .
                                             'Se estiver errado, digite um novo horário.',
    'creationWizardTimingConfirmToday'    => 'hoje, às :timing',
    'creationWizardTimingConfirmTomorrow' => 'amanhã, dia :day, às :timing',
    'creationWizardTimingConfirmOptions'  => [
        [ 'command' => CommandService::COMMAND_CONFIRM, 'value' => $confirmTimingYes ],
    ],
    'creationWizardTimingConfirmYes'      => $confirmTimingYes,

    'creationWizardDuration'        => "*\xE2\x9E\x9C Quantas horas durará a missão, em média?*\n" .
                                       'Informe o valor.',
    'creationWizardDurationOptions' => [
        [ 'value' => '0.5', 'description' => 'menos de uma hora', 'command' => '10' ],
        [ 'value' => '1', 'description' => 'cerca de uma hora', 'command' => '20' ],
        [ 'value' => '2', 'description' => 'cerca de duas horas', 'command' => '30' ],
        [ 'value' => '4', 'description' => 'cerca de quatro horas', 'command' => '40' ],
        [ 'value' => '6', 'description' => 'cerca de seis horas', 'command' => '50' ],
    ],

    'creationWizardPlayers'        => "*\xE2\x9E\x9C Quantos participarão do esquadrão?*\n" .
                                      'Digite um valor entre 2 e :max.',
    'creationWizardPlayersOptions' => [
        [ 'value' => '6', 'description' => '6 (_ex. incursões_)', 'command' => '6' ],
        [ 'value' => '4', 'description' => '4 (_ex. crisol_)', 'command' => '4' ],
        [ 'value' => '3', 'description' => '3 (_ex. anoitecer, assalto_)', 'command' => '3' ],
    ],

    'creationWizardConfirmCreationHeader'  => "*Esta é a última etapa, guardião!*\n" .
                                              "Se tudo estiver certo, basta confirmar.\n\n" .
                                              "---\n\n" .
                                              ":structure\n\n" .
                                              "---\n\n",
    'creationWizardConfirmCreationOptions' => [
        [ 'command' => CommandService::COMMAND_CONFIRM, 'value' => $confirmCreationYes, 'description' => 'Confirmar e publicar no grupo' ],
    ],
    'creationWizardConfirmCreationYes'     => $confirmCreationYes,

    'creationWizardPublished' => "*Está pronto, guardião!*\n" .
                                 'A grade foi publicada no grupo.',

    'errorTitleTooLong'        => "*O título ficou grande demais!*\n" .
                                  "Tente não ultrapassar :max caracteres.\n\n" .
                                  "*\xE2\x9E\x9C Qual será o título da missão?*",
    'errorSubtitleTooLong'     => "*O subtítulo ficou grande demais!*\n" .
                                  "Tente não ultrapassar :max caracteres.\n\n" .
                                  "*\xE2\x9E\x9C Qual será o subtítulo da missão?*",
    'errorRequirementsTooLong' => "*São observações demais!*\n" .
                                  "Tente não ultrapassar :max caracteres.\n\n" .
                                  "*\xE2\x9E\x9C Quais serão as observações da missão?*\n" .
                                  'Opcional (até :max caracteres).',
    'errorTimingInvalid'       => "*O formato informado não é válido!*\n" .
                                  "Digite no formato HH:MM (ex. 10:30).\n\n" .
                                  "*\xE2\x9E\x9C Que horas será a missão?*",
    'errorTimingTooShort'      => "*O horário está muito próximo!*\n" .
                                  "O tempo mínimo é de 15 minutos.\n\n" .
                                  "*\xE2\x9E\x9C Que horas será a missão?*",
    'errorDurationInvalid'     => "*O valor de duração é inválido!*\n" .
                                  "Digite o valor em horas ou escolha uma das opções predefinidas.\n\n" .
                                  "*\xE2\x9E\x9C Quantas horas durará a missão, em média?*",
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
