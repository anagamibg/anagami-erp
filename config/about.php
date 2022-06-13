<?php

return [

    // Application identity
    'name'    => env('ABOUT_APP_NAME', 'Anagami ERP'),
    'tagline' => env('ABOUT_APP_TAGLINE', 'SaaS Business Management Platform'),
    'url'     => env('ABOUT_APP_URL', 'https://backoffix.com'),

    // License issued by the author
    'license' => env('APP_LICENSE_KEY', '8e8f2f03-1baa-4315-ba9f-104c925f32ae'),
    'license_issuer' => env('APP_LICENSE_ISSUER', 'Pixadoro Ltd.'),

    // Author of the application
    'author' => [
        'name'    => env('ABOUT_AUTHOR_NAME', 'Пиксадоро ЕООД (Pixadoro Ltd.)'),
        'website' => env('ABOUT_AUTHOR_URL', 'https://pixadoro.com'),
        'email'   => env('ABOUT_AUTHOR_EMAIL', 'hello@pixadoro.com'),
    ],

    // Owner / operator of this installation
    'owner' => [
        'name'    => env('ABOUT_OWNER_NAME', 'Аутсорса ЕООД (Autsorsa Ltd.)'),
        'website' => env('ABOUT_OWNER_URL', 'https://backoffix.com'),
        'email'   => env('ABOUT_OWNER_EMAIL', 'office@autsorsa.com'),
    ],

];
