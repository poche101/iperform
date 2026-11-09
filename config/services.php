<?php

return [
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
    ],

    'kingschat' => [
        'client_id' => env('KINGSCHAT_CLIENT_ID'),
        'api_key'   => env('KINGSCHAT_API_KEY'),
        // The KingsChat login page address, copied from Step 4 of the Auth Flow Tutorial
        // (the part BEFORE "?client_id=..."). The app adds client_id and origin itself.
        'login_url' => env('KINGSCHAT_LOGIN_URL'),
        // Log the profile response on sign-in, to check field names. Keep false normally.
        'debug'     => env('KINGSCHAT_DEBUG', false),
    ],
];
