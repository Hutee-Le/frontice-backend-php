<?php

return [

    'credentials' => [
        'file' => storage_path('app/firebase/rfid-7f3b2-firebase-adminsdk-sf2b5-ff8731ee74.json'),
    ],

    'storage' => [
        'bucket' => env('FIREBASE_STORAGE_BUCKET'),
    ],

];
