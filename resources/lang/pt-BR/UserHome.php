<?php

declare(strict_types = 1);

use Application\Services\CommandService;

return [
    'homeCommands'        => "*Comandos disponíveis*:\n" .
                             CommandService::COMMAND_COMMANDS . " - Esta listagem dos comandos disponíveis.\n\n" .
                             "*Outros comandos:*\n" .
                             CommandService::COMMAND_REGISTER . " - Primeiro passo para fazer parte do clã.\n" .
                             CommandService::COMMAND_RULES . " - Regras do clã.\n",
    'homeWelcomeBack'     => "Bem-vindo de volta, guardião.\n\n" .
                             ':homeCommands',

    // Cancel header.
    'cancelHeader'        => "*Operação cancelada!*\n" .
                             "Então, que tal tentar algo diferente?\n\n" .
                             ':homeCommands',

    // Command not supported.
    'commandNotSupported' => "*Err...*\n" .
                             "Desculpe, guardião, mas só posso responder através de comandos pré-programados em meu sistema. Culpe o Porta-voz!\n\n" .
                             ':homeCommands',
];
