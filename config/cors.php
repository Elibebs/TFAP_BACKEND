<?php

return [

    /*
     * A cors profile determines which origins, methods, headers are allowed for
     * a given requests. The `DefaultProfile` reads its configuration from this
     * config file.
     *
     * You can easily create your own cors profile.
     * More info: https://github.com/spatie/laravel-cors/#creating-your-own-cors-profile
     */
    'cors_profile' => Spatie\Cors\CorsProfile\DefaultProfile::class,

    /*
     * This configuration is used by `DefaultProfile`.
     */
    'default_profile' => [

        'allow_credentials' => false,

        'allow_origins' => [
            'http://localhost:3000',
            'http://localhost:3001',
            'http://localhost:8080',
            'https://qa.temafirstautoparts.com',
            'https://api.temafirstautoparts.com',
            'https://temafirstautoparts.com',
            'https://www.temafirstautoparts.com',
            'https://qa.backoffice.temafirstautoparts.com',
            'https://backoffice.temafirstautoparts.com',
        ],

        'allow_methods' => [
            'POST',
            'GET',
            'OPTIONS',
            'PUT',
            'PATCH',
            'DELETE',
        ],

        'allow_headers' => [
            'Content-Type',
            'X-Auth-Token',
            'Origin',
            'Authorization',
            'Access-Control-Allow-Origin',
            'access-token',
            'session-id'
        ],

        'expose_headers' => [
            'Cache-Control',
            'Content-Language',
            'Content-Type',
            'Expires',
            'Last-Modified',
            'Pragma',
        ],

        'forbidden_response' => [
            'message' => 'Forbidden (cors).',
            'status' => 403,
        ],

        /*
         * Preflight request will respond with value for the max age header.
         */
        'max_age' => 60 * 60 * 24,
    ],
];
