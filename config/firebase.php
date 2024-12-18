<?php

return [

    'credentials' => [
        'json' => json_decode(env('FIREBASE_CREDENTIALS_JSON'), true),
    ],

    'storage' => [
        'bucket' => env('FIREBASE_STORAGE_BUCKET'),
    ],

];
