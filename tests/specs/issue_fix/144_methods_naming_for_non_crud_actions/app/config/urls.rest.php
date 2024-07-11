<?php
/**
 * OpenAPI UrlRules
 *
 * This file is auto generated.
 */
return [
    'POST payments/invoice/<invoice:\d+>' => 'payments/create-invoice',
    'GET payments/invoice-payment' => 'payment/invoice-payment',
    'GET abc/xyz' => 'abc/xyz',
    'POST abc/xyz' => 'abc/create-xyz',
    'payments/invoice/<invoice:\d+>' => 'payments/options',
    'payments/invoice-payment' => 'payment/options',
    'abc/xyz' => 'abc/options',
];
