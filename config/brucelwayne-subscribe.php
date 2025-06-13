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
        // 精确测试邮箱
        '/^herilan@hotmail\.com$/i',
        '/^bruce\.lu@live\.cn$/i',  // 修正点号转义
//        '/^xxx@example\.com$/i',

        //确定的一些邮箱地址
        // 匹配任何 airwallex 相关邮箱（如 airwallex.com、airwallex.net、airwallex.cn 等）
        '/@airwallex\..*$/i',

        // 匹配任何 mallria 相关邮箱
        '/@mallria\..*$/i',
        '/@boreshijia\..*$/i',
        '/@brsj\..*$/i',

        // RFC 2606 标准保留域名（常用于示例和文档）
        '/@example\.com$/i',
        '/@example\.net$/i',
        '/@example\.org$/i',
        '/@test\.com$/i',
        '/@invalid\.com$/i',
        '/@local\.test$/i',

        // 假数据生成器和临时邮箱服务常见域名
        '/@fakeemail\.com$/i',
        '/@faker\..*$/i',
        '/@mailinator\.com$/i',
        '/@10minutemail\.com$/i',
        '/@tempmail\..*$/i',
        '/@guerrillamail\.com$/i',
        '/@dispostable\.com$/i',
        '/@yopmail\.com$/i',
        '/@trashmail\..*$/i',
        '/@throwawaymail\.com$/i',
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
