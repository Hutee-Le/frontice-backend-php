<?php

return [

    // 'credentials' => [
    //     'file' => storage_path('app/firebase/rfid-7f3b2-firebase-adminsdk-sf2b5-ff8731ee74.json'),
    // ],

    'credentials' => [
        'type' => env('FIREBASE_TYPE'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
        'private_key' => env('FIREBASE_PRIVATE_KEY'),
        'client_email' => env('FIREBASE_CLIENT_EMAIL'),
        'client_id' => env('FIREBASE_CLIENT_ID'),
        'auth_uri' => env('FIREBASE_AUTH_URI'),
        'token_uri' => env('FIREBASE_TOKEN_URI'),
        'auth_provider_x509_cert_url' => env('FIREBASE_AUTH_PROVIDER_X509_CERT_URL'),
        'client_x509_cert_url' => env('FIREBASE_CLIENT_X509_CERT_URL'),
        'universe_domain' => env('FIREBASE_UNIVERSE_DOMAIN')
    ],

    'storage' => [
        'bucket' => env('FIREBASE_STORAGE_BUCKET'),
    ],

];
