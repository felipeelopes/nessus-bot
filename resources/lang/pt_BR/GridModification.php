<?php

declare(strict_types = 1);

use Application\SessionsProcessor\GridModification\InitializationMoment;

return [
    'modificationUpdateNotify' => "\xE2\x9C\x93 :title\n\n" .
                                  "---\n\n" .
                                  ':structure',

    'modificationOptions' => [
        [ 'value' => InitializationMoment::REPLY_MODIFY_TITLE, 'description' => 'Modificar título...' ],
        [ 'value' => InitializationMoment::REPLY_MODIFY_SUBTITLE, 'description' => 'Modificar subtítulo...' ],
        [ 'value' => InitializationMoment::REPLY_MODIFY_REQUIREMENTS, 'description' => 'Modificar observações...' ],
        [ 'value' => InitializationMoment::REPLY_MODIFY_TIMING, 'description' => 'Modificar horário...' ],
        [ 'value' => InitializationMoment::REPLY_MODIFY_DURATION, 'description' => 'Modificar duração...' ],
        [ 'value' => InitializationMoment::REPLY_MODIFY_PLAYERS, 'description' => 'Modificar número de titulares...' ],
        [ 'value' => InitializationMoment::REPLY_TRANSFER_OWNER, 'description' => 'Redefinir organizador...' ],
    ],

    'modifyAdministrative' => "\xE2\x98\xA2 ",

    'modifyTitleOption'  => 'Modificar título...',
    'modifyTitleWizard'  => "*\xE2\x9E\x9C Qual será o novo título da missão?*\n" .
                            "Limite de 80 caracteres.\n\n" .
                            '*Título atual*: :current',
    'modifyTitleUpdated' => "*Título atualizado:* :value\n\n",

    'errorTitleTooLong' => "*O novo título ficou grande demais!*\n" .
                           "Tente não ultrapassar :max caracteres.\n\n" .
                           "*\xE2\x9E\x9C Qual será o novo título da missão?*",

    'modifySubtitleOption'  => 'Modificar subtítulo...',
    'modifySubtitleWizard'  => "*\xE2\x9E\x9C Qual será o novo subtítulo da missão?*\n" .
                               "Limite de 20 caracteres.\n\n" .
                               '*Subtítulo atual*: :current',
    'modifySubtitleNone'    => '_(nenhum)_',
    'modifySubtitleUpdated' => "*Subtítulo atualizado:* :value\n\n",

    'errorSubtitleTooLong' => "*O novo subtítulo ficou grande demais!*\n" .
                              "Tente não ultrapassar :max caracteres.\n\n" .
                              "*\xE2\x9E\x9C Qual será o novo subtítulo da missão?*",

    'modifyRequirementsOption'  => 'Modificar observações...',
    'modifyRequirementsWizard'  => "*\xE2\x9E\x9C Quais serão as novas observações da missão?*\n" .
                                   "Limite de :max caracteres.\n\n" .
                                   '*Observações atuais*: :current',
    'modifyRequirementsNone'    => '_(nenhuma)_',
    'modifyRequirementsUpdated' => "*Observações atualizada:* :value\n\n",

    'errorRequirementsTooLong' => "*São observações demais!*\n" .
                                  "Tente não ultrapassar :max caracteres.\n\n" .
                                  "*\xE2\x9E\x9C Quais serão as novas observações da missão?*\n" .
                                  'Limite de :max caracteres.',

    'modifyTimingOption'  => 'Modificar horário...',
    'modifyTimingWizard'  => "*\xE2\x9E\x9C Qual será o novo horário da missão?*\n" .
                             "Digite no formato HH:MM (ex. 10:30).\n\n" .
                             '*Horário atual:* :current',
    'modifyTimingUpdated' => "*Horário atualizado:* :value\n\n",

    'errorTimingInvalid'  => "*O formato informado não é válido!*\n" .
                             "Digite no formato HH:MM (ex. 10:30).\n\n" .
                             "*\xE2\x9E\x9C Qual será o novo horário da missão?*",
    'errorTimingTooShort' => "*O horário está muito próximo!*\n" .
                             "O tempo mínimo é de 15 minutos.\n\n" .
                             "*\xE2\x9E\x9C Qual será o novo horário da missão?*",

    'modifyDurationOption'  => 'Modificar duração...',
    'modifyDurationWizard'  => "*\xE2\x9E\x9C Qual será a nova duração da missão, em média?*\n" .
                               "Informe o novo valor.\n\n" .
                               '*Duração atual*: :current',
    'modifyDurationUpdated' => "*Duração atualizada:* cerca de :value\n\n",

    'errorDurationInvalid' => "*O valor de duração é inválido!*\n" .
                              "Digite o valor em horas ou escolha uma das opções predefinidas.\n\n" .
                              "*\xE2\x9E\x9C Qual será a nova duração da missão, em média?*",

    'modifyPlayersOption'  => 'Modificar número de titulares...',
    'modifyPlayersWizard'  => "*\xE2\x9E\x9C Qual será o novo número de titulares?*\n" .
                              "Digite um valor entre 2 e :max.\n\n" .
                              '*Número atual*: :current participantes',
    'modifyPlayersUpdated' => "*Número atualizado:* :value participantes\n\n",

    'errorPlayersInvalid' => "*O número de participantes informado não é válido!*\n" .
                             "Digite um valor entre 2 e :max.\n\n" .
                             "*\xE2\x9E\x9C Quantos participarão do esquadrão?*",
    'errorPlayersTooFew'  => "*O número de participantes não pode ser inferior a 2!*\n" .
                             "Digite um valor entre 2 e :max.\n\n" .
                             "*\xE2\x9E\x9C Quantos participarão do esquadrão?*",
    'errorPlayersTooMuch' => "*O número de participantes não pode ser superior a :max!*\n" .
                             "Digite um valor entre 2 e :max.\n\n" .
                             "*\xE2\x9E\x9C Quantos participarão do esquadrão?*",

    'transferOwnerOption'  => 'Redefinir organizador...',
    'transferOwnerIsEmpty' => "*Err... não há outro participante nesta grade além de você.*\n" .
                              'Para redefinir o organizador para outro participante, ' .
                              'é necessário que haja outros membros inscritos nela.',
    'transferOwnerWizard'  => "*\xE2\x9E\x9C Selecione o novo organizador da grade:*\n" .
                              'Após este processo você será redefinido como moderador da grade automaticamente.',
    'transferOwnerUpdated' => "*O novo organizador é:* :value\n\n",

    'errorTransferOwnerUnavailable' => "*Err... participante não encontrado.*\n" .
                                       "Vamos tentar novamente...\n\n" .
                                       "*\xE2\x9E\x9C Selecione o novo organizador da grade:*",

    'modifyManagersOption'      => 'Gerenciar moderadores...',
    'modifyManagersIsEmpty'     => "*Err... não há outro participante nesta grade além de você.*\n" .
                                   'Para gerenciar os moderadores é necessário que haja mais inscritos.',
    'modifyManagersWizard'      => "*\xE2\x9E\x9C Selecione a operação que será realizada:*",
    'modifyManagerAdd'          => "Definir moderador \xE2\x86\x92 *:gamertag*",
    'modifyManagerAddUpdate'    => "*Definido como moderador:* :value\n\n",
    'modifyManagerRevoke'       => "Revogar moderador \xE2\x86\x92 *:gamertag*",
    'modifyManagerRevokeUpdate' => "*Revogado as permissões de moderador:* :value\n\n",

    'errorModifyManagerUnavailable' => "*Err... participante não encontrado.*\n" .
                                       "Vamos tentar novamente...\n\n" .
                                       "*\xE2\x9E\x9C Selecione a operação que será realizada:*",

    'unsubscribeYouOption' => 'Sair',
    'unsubscribeYouUpdate' => "Você *saiu* desta grade.\n\n",

    'unsubscribeOwnerOption' => 'Sair...',
    'unsubscribeOwnerWizard' => "*Só mais um detalhe, guardião...*\n" .
                                'Como você é o organizador desta grade, será necessário indicar um organizador substituto. ' .
                                'Após selecioná-lo abaixo, você deixará de estar inscrito nesta grade.',
    'unsubscribeOwnerUpdate' => "Você *saiu* desta grade.\n\n",

    'errorUnsubscribeUserUnavailable' => "*Err... participante não encontrado.*\n" .
                                         "Vamos tentar novamente...\n\n" .
                                         "*\xE2\x9E\x9C Selecione um organizador substituto:*",

    'unsubscribeCancelWizard'         => "*Você é o último da grade...*\n" .
                                         'Nesse caso, vamos precisar cancelá-la. ' .
                                         'Digite um motivo público ou selecione uma das opções disponíveis abaixo.',
    'unsubscribeCancelUpdate'         => "Você *cancelou* esta grade.\n\n",
    'unsubscribeCancelPersonalReason' => 'Motivo pessoal (_imprevisto_)',
    'unsubscribeCancelLackPlayers'    => 'Falta de jogadores',
    'unsubscribeCancelLackInterest'   => 'Falta de interesse',
    'unsubscribeCancelAccessIssue'    => 'Problema de acesso ao jogo',
    'unsubscribeCancelOthers'         => 'Outros',

    'errorForbidden' => "*Err...*\n" .
                        'Você não é um organizador ou moderador desta grade.',
];
