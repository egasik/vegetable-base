<?php

return [
    'shop_id' => env('YOOKASSA_SHOP_ID'),
    'secret_key' => env('YOOKASSA_SECRET_KEY'),
    'test_mode' => env('YOOKASSA_TEST_MODE', false),
    
    // URL возврата после оплаты
    'return_url' => env('APP_URL') . '/payment/{id}/success',
    
    // Webhook URL для подтверждения оплаты
    'webhook_url' => env('APP_URL') . '/payment/webhook',
];