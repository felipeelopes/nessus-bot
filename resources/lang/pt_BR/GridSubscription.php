<?php

declare(strict_types = 1);

return [
    'positionTitular'        => 'titular',
    'positionTitularReserve' => 'titular-reserva',
    'positionReserve'        => 'reserva',

    'alreadySubscribed'   => 'Você já está registrado nesta grade como *:position*.',
    'alreadyUnsubscribed' => 'Você não faz parte desta grade.',

    'errorNoVacancies'        => "*Desculpe, guardião...*\n" .
                                 'Não há mais vagas para titulares nesta grade.',
    'errorVacanciesAvailable' => "*Desculpe, guardião...*\n" .
                                 'Só será possível entrar como *titular-reserva* se as vagas de *titular* se esgotarem.',
    'errorAlreadyTitular'     => "*Desculpe, guardião...*\n" .
                                 'Você não pode se tornar um *titular-reserva*, pois já está inscrito como *titular* nesta grade.',

    'observationDropped' => 'A sua observação foi removida desta grade.',
    'observationHowTo'   => "Informe ao lado do comando a observação.\n" .
                            '*Exemplo:* /:command caçador',

    'errorObservationTooLong' => "A observação não deve exceder 20 caracteres.\n" .
                                 'Você enviou :length caracteres.',

    'notifyMessage'             => "*Atenção, guardião!*\n" .
                                   "Você está inscrito como *:position* na grade :command (\":title\") das *:hours*, que começará em *:minutes*.\n\n" .
                                   ':observations',
    'observationTitular'        => '*Esteja pronto!*',
    'observationTitularReserve' => 'Como *titular-reserva*, você poderá tornar-se *titular* caso um dos titulares desista.',
    'observationReserve'        => 'Como *reserva*, você poderá ser convocado a qualquer momento caso sua presença seja necessária.',
];
