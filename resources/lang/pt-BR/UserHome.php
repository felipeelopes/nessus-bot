<?php

declare(strict_types = 1);

return [
    'homeCommands'        => "*Comandos disponíveis*:\n\n" .
                             "/comandos - Esta listagem dos comandos disponíveis.\n",
    'homeWelcomeBack'     => "Bem-vindo de volta, guardião.\n\n" .
                             ':homeCommands',

    // Cancel header.
    'cancelHeader'        => "*Operação cancelada!*\n\n" .
                             "Então que tal tentar algo diferente?\n\n" .
                             ':homeCommands',

    // Command not supported.
    'commandNotSupported' => "Desculpe, guardião, mas só posso responder através de comandos pré-programados em meu sistema. Culpe o Porta-voz!\n\n" .
                             ':homeCommands',
];
