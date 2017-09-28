<?php

declare(strict_types = 1);

return [
    'welcome'               => "*Olá, guardião!*\n" .
                               "Precisamos de uma informação antes de tornar possível enviar mensagens no grupo *:groupTitle*.\n\n" .
                               ':whichGamertag',
    'welcomeToGroupSticker' => 'CAADAQADAgADwvySEW6F5o6Z1x05Ag',
    'welcomeToGroup'        => "*Vamos celebrar!*\n" .
                               'Nosso novo guardião, :mention (Gamertag *:gamertag*), estará conosco nas próximas missões.',
    'welcomeAgain'          => "*Vamos celebrar, de novo!*\n" .
                               'Nosso guardião, :mention (Gamertag *:gamertag*), está conosco novamente!',

    'toPrivate'       => "Seja bem-vindo, :mention!\n\n" .
                         'Antes de enviar novas mensagens aqui no grupo, preciso falar com você em particular.',
    'toPrivateButton' => "\xC2\xAB Clique aqui para iniciarmos a conversa... \xC2\xBB",
    'toPrivateLink'   => 'https://t.me/:botname',

    'whichGamertag'    => "*\xE2\x9E\x9C Qual a sua Gamertag na Xbox Live?*",
    'checkingGamertag' => 'Deixe-me localizar, guardião...',

    'checking'          => "Certo, *:gamertag*...\nDeixa eu verificar aqui... e...",
    'checkingInvalid'   => "*Não consegui entender essa Gamertag, guardião...*\n" .
                           "Digite sua Gamertag do jeitinho que aparece no *Xbox Live*.\n\n" .
                           ':whichGamertag',
    'checkingFail'      => "*Não encontrei essa Gamertag, guardião...*\n" .
                           "Digite sua Gamertag do jeitinho que aparece no *Xbox Live*.\n\n" .
                           ':whichGamertag',
    'checkingSuccess'   => "*Está anotado, guardião*!\n" .
                           "Agora você já pode enviar mensagens no grupo.\n\n" .
                           ':important:rules:admins',
    'checkingImportant' => "\xE2\x9A\xA0 *Atenção*: você precisará efetuar a alteração de clã nas próximas *12 horas*. \n" .
                           "Caso não seja possível no momento, informe a um de nossos /Administradores imediatamente, caso contrário sua inscrição no clã será revogada.\n\n",

    'alreadyRegistered' => "*Calma lá, guardião!*\n\n" .
                           "Você já faz parte do clã.\n" .
                           'E nem pense em sair!',
];
