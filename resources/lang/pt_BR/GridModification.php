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
        [ 'value' => InitializationMoment::REPLY_MODIFY_REQUIREMENTS, 'description' => 'Modificar exigências...' ],
        [ 'value' => InitializationMoment::REPLY_MODIFY_TIMING, 'description' => 'Modificar horário...' ],
        [ 'value' => InitializationMoment::REPLY_MODIFY_DURATION, 'description' => 'Modificar duração...' ],
    ],

    'modifyTitleWizard'  => "*\xE2\x9E\x9C Qual será o novo título da missão?*\n" .
                            "Limite de 80 caracteres.\n\n" .
                            '*Título atual*: :current',
    'modifyTitleUpdated' => "*Título atualizado:* :value\n\n",

    'errorTitleTooLong' => "*O novo título ficou grande demais!*\n" .
                           "Tente não ultrapassar :max caracteres.\n\n" .
                           "*\xE2\x9E\x9C Qual será o novo título da missão?*",

    'modifySubtitleWizard'  => "*\xE2\x9E\x9C Qual será o novo subtítulo da missão?*\n" .
                               "Limite de 20 caracteres.\n\n" .
                               '*Subtítulo atual*: :current',
    'modifySubtitleNone'    => '_(nenhum)_',
    'modifySubtitleUpdated' => "*Subtítulo atualizado:* :value\n\n",

    'errorSubtitleTooLong' => "*O novo subtítulo ficou grande demais!*\n" .
                              "Tente não ultrapassar :max caracteres.\n\n" .
                              "*\xE2\x9E\x9C Qual será o novo subtítulo da missão?*",

    'modifyRequirementsWizard'  => "*\xE2\x9E\x9C Quais serão as novas exigências da missão?*\n" .
                                   "Limite de :max caracteres.\n\n" .
                                   '*Exigências atual*: :current',
    'modifyRequirementsNone'    => '_(nenhuma)_',
    'modifyRequirementsUpdated' => "*Exigências atualizada:* :value\n\n",

    'errorRequirementsTooLong' => "*São exigências demais!*\n" .
                                  "Tente não ultrapassar :max caracteres.\n\n" .
                                  "*\xE2\x9E\x9C Quais serão as novas exigências da missão?*\n" .
                                  'Limite de :max caracteres.',

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

    'modifyDurationWizard'  => "*\xE2\x9E\x9C Qual será a nova duração da missão, em média?*\n" .
                               "Informe o novo valor.\n\n" .
                               '*Duração atual*: :current',
    'modifyDurationUpdated' => "*Duração atualizada:* cerca de :value\n\n",

    'errorDurationInvalid' => "*O valor de duração é inválido!*\n" .
                              "Digite o valor em horas ou escolha uma das opções predefinidas.\n\n" .
                              "*\xE2\x9E\x9C Qual será a nova duração da missão, em média?*",
];
