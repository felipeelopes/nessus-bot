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
    ],

    'modifyTitleWizard'  => "*\xE2\x9E\x9C Qual será o novo título da missão?*\n" .
                            "Limite de 80 caracteres.\n\n" .
                            '*Título atual*: :current',
    'modifyTitleUpdated' => "*Título salvo:* :value\n\n",

    'errorTitleTooLong' => "*O novo título ficou grande demais!*\n" .
                           "Tente não ultrapassar :max caracteres.\n\n" .
                           "*\xE2\x9E\x9C Qual será o novo título da missão?*",

    'modifySubtitleWizard'  => "*\xE2\x9E\x9C Qual será o novo subtítulo da missão?*\n" .
                               "Limite de 20 caracteres.\n\n" .
                               '*Subtítulo atual*: :current',
    'modifySubtitleNone'    => '_(nenhum)_',
    'modifySubtitleUpdated' => "*Subtítulo salvo:* :value\n\n",

    'errorSubtitleTooLong' => "*O novo subtítulo ficou grande demais!*\n" .
                              "Tente não ultrapassar :max caracteres.\n\n" .
                              "*\xE2\x9E\x9C Qual será o novo subtítulo da missão?*",

    'modifyRequirementsWizard'  => "*\xE2\x9E\x9C Quais serão as novas exigências da missão?*\n" .
                                   "Limite de :max caracteres.\n\n" .
                                   '*Exigências atual*: :current',
    'modifyRequirementsNone'    => '_(nenhuma)_',
    'modifyRequirementsUpdated' => "*Exigências salva:* :value\n\n",

    'errorRequirementsTooLong' => "*São exigências demais!*\n" .
                                  "Tente não ultrapassar :max caracteres.\n\n" .
                                  "*\xE2\x9E\x9C Quais serão as novas exigências da missão?*\n" .
                                  'Limite de :max caracteres.',
];
