<?php

declare(strict_types = 1);

return [
    'welcome'               => "*Olá, guardião!*\n" .
                               "Precisamos de uma informação antes de tornar possível enviar mensagens no grupo *:groupTitle*.\n\n" .
                               ':whichGamertag',
    'welcomeToGroupSticker' => 'CAADAQADAgADwvySEW6F5o6Z1x05Ag',
    'welcomeToGroup'        => "*Vamos celebrar!*\n" .
                               'Nosso novo guardião, *:fullname* (Gamertag *:gamertag*), estará conosco nas próximas missões.',
    'welcomeAgain'          => "*Vamos celebrar, de novo!*\n" .
                               'Nosso guardião, *:fullname* (Gamertag *:gamertag*), está conosco novamente.',

    // Need to talk in private.
    'toPrivate'             => "Seja bem-vindo, *:fullname*!\n\n" .
                               "Antes de enviar novas mensagens aqui no grupo, precisamos falar com você em privado primeiro.\n\n" .
                               "\xE2\x9E\x9C *Fale comigo por aqui:* :botUsername",

    // Gamertag request.
    'whichGamertag'         => "*\xE2\x9E\x9C Qual a sua Gamertag na Xbox Live?*",
    'checkingGamertag'      => 'Deixe-me localizar, guardião...',

    // Checking process.
    'checking'              => "Certo, *:gamertag*...\nDeixa eu verificar aqui... e...",
    'checkingInvalid'       => "*Não consegui entender essa Gamertag, guardião...*\n" .
                               "Digite sua Gamertag do jeitinho que aparece no *Xbox Live*.\n\n" .
                               ':whichGamertag',
    'checkingFail'          => "*Não encontrei essa Gamertag, guardião...*\n" .
                               "Digite sua Gamertag do jeitinho que aparece no *Xbox Live*.\n\n" .
                               ':whichGamertag',
    'checkingSuccess'       => "*Está anotado, guardião*!\n" .
                               "Agora você já pode enviar mensagens no grupo.\n\n" .
                               ':rules:admins',

    // Already registered.
    'alreadyRegistered'     => "*Calma lá, guardião!*\n\n" .
                               "Você já faz parte do clã.\n" .
                               'E nem pense em sair!',
];
