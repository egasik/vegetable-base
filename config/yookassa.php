<?php

return [
    'ca_bundle' => env('YOOKASSA_CA_BUNDLE', resource_path('certificates/cacert.pem')),
    'vat_code' => env('YOOKASSA_VAT_CODE', 1),
    'shop_id' => env('YOOKASSA_SHOP_ID'),
    'secret_key' => env('YOOKASSA_SECRET_KEY'),
    'test_mode' => env('YOOKASSA_TEST_MODE', false),

    // URL возврата после оплаты
    'return_url' => env('APP_URL').'/payment/{id}/success',

    // Webhook URL для подтверждения оплаты
    'webhook_url' => env('APP_URL').'/payment/webhook',
];
