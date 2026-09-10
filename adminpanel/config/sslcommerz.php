<?php

return [
    'apiCredentials' => [
        'store_id' => env('SSLCZ_STORE_ID'),
        'store_password' => env('SSLCZ_STORE_PASSWORD'),
    ],
    'apiDomain' => env('SSLCZ_TESTMODE', true)
        ? 'https://sandbox.sslcommerz.com'
        : 'https://securepay.sslcommerz.com',
    'apiUrl' => [
        'make_payment' => '/gwprocess/v4/api.php',
        'order_validate' => '/validator/api/validationserverAPI.php',
    ],
    'connect_from_localhost' => env('IS_LOCALHOST', false),
    'success_url' => '/api/payments/sslcommerz/success',
    'failed_url' => '/api/payments/sslcommerz/fail',
    'cancel_url' => '/api/payments/sslcommerz/cancel',
    'ipn_url' => '/api/payments/sslcommerz/ipn',
];