<?php

return [

    'test-mail' => [
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'hello@mallria.com'),
            'name' => env('MAIL_FROM_NAME', 'Hello'),
        ],
    ],

    'mail' => [
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'hello@mallria.com'),
            'name' => env('MAIL_FROM_NAME', 'Mallria'),
        ],
    ],

    //$recipients = array_filter($recipients, fn($r) => !shouldExcludeEmail($r->email));
    'exclude_email_patterns' => [
        // === 精确匹配邮箱地址 ===
        '/^herilan@hotmail\.com$/i',
        '/^bruce\.lu@live\.cn$/i',

        // === 特定组织/品牌的邮箱域（匹配任意后缀） ===
        '/@airwallex\./i',
        '/@mallria\./i',
        '/@boreshijia\./i',
        '/@brsj\./i',

        // === 保留域名（RFC 2606） ===
        '/@example\./i',
        '/@test\./i',
        '/@invalid\./i',

        // === 所有 .test 和 .local 域结尾的邮箱 ===
        '/@[\w.-]+\.test$/i',
        '/@[\w.-]+\.local$/i',

        // === 假数据生成器和临时邮箱服务商 ===
        '/@fakeemail\./i',
        '/@faker\./i',
        '/@mailinator\./i',
        '/@10minutemail\./i',
        '/@tempmail\./i',
        '/@guerrillamail\./i',
        '/@dispostable\./i',
        '/@yopmail\./i',
        '/@trashmail\./i',
        '/@throwawaymail\./i',
    ],


    // 使用哪个邮件引擎，支持 'mailgun' 或 'postmark'
    'engine' => env('MAIL_ENGINE', 'mailgun'),

    // Mailgun 配置
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'api_key' => env('MAILGUN_API_KEY'),
    ],

    // Postmark 配置
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
];
