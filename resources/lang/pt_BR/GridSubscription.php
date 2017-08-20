<?php

declare(strict_types = 1);

return [
    'positionTitular'        => 'titular',
    'positionTitularReserve' => 'titular-reserva',
    'positionReserve'        => 'reserva',

    'alreadySubscribed' => 'Você já está registrado nesta grade como *:position*.',

    'errorNoVacancies'        => "*Desculpe, guardião...*\n" .
                                 'Não há mais vagas para titulares nesta grade.',
    'errorVacanciesAvailable' => "*Desculpe, guardião...*\n" .
                                 'Só será possível entrar como *titular-reserva* se as vagas de *titular* se esgotarem.',
    'errorAlreadyTitular'     => "*Desculpe, guardião...*\n" .
                                 'Você não pode se tornar um *titular-reserva*, pois já está inscrito como *titular* nesta grade.',
];
