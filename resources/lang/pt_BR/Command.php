<?php

declare(strict_types = 1);

return [
    'mainCommands'       => "*Principais comandos:*\n",
    'additionalCommands' => "\n*Comandos adicionais:*\n",
    'adminCommands'      => "\n*Comandos administrativos:*\n",
    'command'            => ":command - :description;\n",

    'callPrivate' => 'Resolveremos esse assunto no privado, :mention.',
    'cantContact' => "\xE2\x9A\xA0 *Err...*\n" .
                     "Não consigo enviar mensagem para você, guardião.\n" .
                     'Teremos um problema sério se eu tiver bloqueado...',

    'commands' => [
        'cancelCommand'  => 'Cancelar',
        'confirmCommand' => 'Confirmar',

        'commandsCommand'     => 'Comandos',
        'commandsDescription' => 'Esta listagem dos comandos disponíveis',

        'gtCommand'     => 'GT',
        'gtDescription' => 'Exibe a Gamertag de um usuário',

        'newGridCommand'     => 'NovaGrade',
        'newGridDescription' => 'Assistente de criação de grades',

        'newsCommand'     => 'Noticias',
        'newsDescription' => 'Exibe as últimas notícias do jogo',

        'listGridsCommand'     => 'ListarGrades',
        'listGridsDescription' => 'Listar as grades disponíveis',

        'myGridsCommand'     => 'MinhasGrades',
        'myGridsDescription' => 'Listar as grades criadas por você',

        'registerCommand'     => 'Registrar',
        'registerDescription' => 'Primeiro passo para fazer parte do clã',

        'adminsCommand'     => 'Administradores',
        'adminsDescription' => 'Lista os administradores do grupo',

        'rulesCommand'     => 'Regras',
        'rulesDescription' => 'Lista as regras do clã',

        'refreshCommand'     => 'Atualizar',
        'refreshDescription' => 'Atualiza os recursos de sistema',

        'gridShowShortCommand' => 'G',

        'subscribeTitularCommand'       => 'G:idT',
        'subscribeTitularCommandLetter' => 'T',
        'subscribeTitularDescription'   => 'Participar como *titular*',

        'subscribeTitularReserveCommand'       => 'G:idR',
        'subscribeTitularReserveCommandLetter' => 'R',
        'subscribeTitularReserveDescription'   => 'Participar como *titular-reserva*',

        'subscribeReserveCommand'       => 'G:idV',
        'subscribeReserveCommandLetter' => 'V',
        'subscribeReserveDescription'   => 'Participar como *reserva*',

        'subscribeUnsubscribeCommand'       => 'G:idS',
        'subscribeUnsubscribeCommandLetter' => 'S',
        'subscribeUnsubscribeDescription'   => 'Sair',

        'subscribeObservationCommand'       => 'G:idO',
        'subscribeObservationCommandLetter' => 'O',
        'subscribeObservationDescription'   => 'Acrescentar observação...',

        'gridManagerCommand'       => 'G:idP',
        'gridManagerCommandLetter' => 'P',
        'gridManagerDescription'   => 'Personalizar grade...',
    ],
];
