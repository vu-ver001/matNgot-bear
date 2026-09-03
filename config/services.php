<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'origin_address' => env('SHOP_ORIGIN_ADDRESS', 'Số 41A, Phú Diễn, Bắc Từ Liêm, Hà Nội'),
        'origin_lat' => env('SHOP_ORIGIN_LAT', 21.0456),
        'origin_lng' => env('SHOP_ORIGIN_LNG', 105.7628),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/auth/google/callback'),
    ],

    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE', 'DKEKANL1'),
        'hash_secret' => env('VNPAY_HASH_SECRET', 'ODQMSSMZLVNQZMMITMJHFEUUQZWQYYEW'),
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'merchant_name' => env('VNPAY_MERCHANT_NAME', 'MẬT NGỌT BEAR'),
        'return_url' => env('VNPAY_RETURN_URL'),
        'ipn_url' => env('VNPAY_IPN_URL'),
    ],

    'momo' => [
        'partner_code' => env('MOMO_PARTNER_CODE', 'MOMO'),
        'access_key' => env('MOMO_ACCESS_KEY', 'F8BBA842ECF85'),
        'secret_key' => env('MOMO_SECRET_KEY', 'K951B6PE1wa8ngf4S01Jn72DY142l39z'),
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'redirect_url' => env('MOMO_REDIRECT_URL'),
        'ipn_url' => env('MOMO_IPN_URL'),
        'phone' => env('MOMO_PHONE', '0377466205'),
        'name' => env('MOMO_NAME', 'NGUYỄN NGỌC ANH'),
    ],

    'sepay' => [
        'api_key' => env('SEPAY_API_KEY', ''),
        'webhook_token' => env('SEPAY_WEBHOOK_TOKEN', ''),
    ],

    'vietqr' => [
        'bank_code' => env('VIETQR_BANK_CODE', 'MB'),
        'bank_name' => env('VIETQR_BANK_NAME', 'MB Bank (Ngân hàng Quân Đội)'),
        'account_number' => env('VIETQR_ACCOUNT_NUMBER', '0377466205'),
        'account_name' => env('VIETQR_ACCOUNT_NAME', 'NGUYỄN NGỌC ANH'),
    ],
];
