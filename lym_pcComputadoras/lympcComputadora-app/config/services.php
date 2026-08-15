<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
    ],

    'assistant_ai' => [
        // Valores admitidos: gemini, qwen o groq. La clave jamás debe ir al cliente.
        'provider' => env('AI_PROVIDER', 'gemini'),
        'gemini_key' => env('GEMINI_API_KEY'),
        'gemini_model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'qwen_key' => env('QWEN_API_KEY'),
        'qwen_model' => env('QWEN_MODEL', 'qwen-plus'),
        'qwen_base_url' => env('QWEN_BASE_URL', 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1'),
        'groq_key' => env('GROQ_API_KEY', env('QWEN_API_KEY')),
        'groq_model' => env('GROQ_MODEL', env('QWEN_MODEL', 'qwen/qwen3.6-27b')),
        'groq_base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    ],

];
