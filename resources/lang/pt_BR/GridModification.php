<?php

declare(strict_types = 1);

use Application\SessionsProcessor\GridModification\InitializationMoment;

return [
    'modificationUpdateNotify' => "\xE2\x9C\x93 :title\n\n" .
                                  "---\n\n" .
                                  ':structure',

    'modificationOptions' => [
        [ 'value' => InitializationMoment::REPLY_MODIFY_TITLE, 'description' => 'Modificar título...' ],
    ],

    'modifyTitleWizard'  => "*\xE2\x9E\x9C Qual será o novo título da missão?*\n" .
                            "Limite de 80 caracteres.\n\n" .
                            '*Título atual*: :current',
    'modifyTitleUpdated' => "*Título salvo:* :value\n\n",

    'errorTitleTooLong' => "*O novo título ficou grande demais!*\n" .
                           "Tente não ultrapassar :max caracteres.\n\n" .
                           "*\xE2\x9E\x9C Qual será o novo título da missão?*",
];
