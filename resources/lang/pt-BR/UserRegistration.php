<?php

declare(strict_types = 1);

return [
    'welcome'           => "*Olá, guardião!*\n\n" .
                           "Antes de enviar novas mensagens:groupTitle, precisamos saber de apenas uma coisa:\n\n" .
                           ':whichGamertag',
    'welcomeGroupTitle' => ' para o grupo *:group*',
    'welcomeToGroup'    => "*Vamos celebrar!*\n" .
                           'Nosso novo guardião, *:fullname* (vulgo *:gamertag*), estará conosco nas próximas missões.',

    // Need to talk in private.
    'toPrivate'         => "Seja bem-vindo, *:fullname*!\n\n" .
                           "Antes de enviar novas mensagens aqui no grupo precisamos falar com você em privado primeiro.\n\n" .
                           '*Fale comigo por aqui:* @:botname',

    // Gamertag request.
    'whichGamertag'     => "*\xE2\xAE\x9E Qual a sua Gamertag na Xbox Live?*",

    // Checking process.
    'checking'          => "Certo, *:gamertag*...\nDeixa eu verificar aqui... e...",
    'checkingInvalid'   => "*Segure a emoção, guardião!*\n\n" .
                           "Essa Gamertag pareceu meio confusa...\n" .
                           "Vamos tentar mais uma vez?\n\n" .
                           "Digite sua Gamertag do jeitinho que aparece no *Xbox Live*.\n\n" .
                           ':whichGamertag',
    'checkingFail'      => "*Guardião, tente se lembrar melhor...*\n\n" .
                           "A Gamertag *:gamertag* não foi encontrada. Bem, pelo menos eu não consegui achar aqui nos arquivos da Vanguarda.\n\n" .
                           "Podemos tentar de novo?\n\n" .
                           "Digite sua Gamertag do jeitinho que aparece no *Xbox Live*.\n\n" .
                           ':whichGamertag',
    'checkingSuccess'   => "Está anotado, guardião *:gamertag*!\n" .
                           'Agora você já pode enviar novas mensagens para o grupo.',
];
