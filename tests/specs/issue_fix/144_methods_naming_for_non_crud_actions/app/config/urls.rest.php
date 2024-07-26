<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'GET fruit/mango' => 'fruits/mango',
    'GET fruits/mango' => 'fruit/mango',
    'POST fruits/mango' => 'fruit/create-mango',
    'GET animal/goat' => 'animal/goat',
    'POST animal/goat' => 'animal/create-goat',
    'POST payments/invoice/<invoice:\d+>' => 'payments/invoice',
    'GET payments/invoice-payment' => 'payment/invoice-payment',
    'GET a1/b1' => 'abc/xyz',
    'POST a1/b1' => 'abc/xyz',
    'GET aa2/bb2' => 'payments/xyz2',
    'fruit/mango' => 'fruits/options',
    'fruits/mango' => 'fruit/options',
    'animal/goat' => 'animal/options',
    'payments/invoice/<invoice:\d+>' => 'payments/options',
    'payments/invoice-payment' => 'payment/options',
    'a1/b1' => 'abc/options',
    'aa2/bb2' => 'payments/options',
];
