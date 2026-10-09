<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Analytics publik (issue #20)
    |--------------------------------------------------------------------------
    |
    | ID analytics TIDAK PERNAH di-hardcode di template. Semua dibaca dari
    | environment lewat config ini, lalu diteruskan ke frontend Inertia
    | sebagai props. Kosong berarti fitur nonaktif (tidak ada script
    | pihak ketiga yang dimuat).
    |
    */

    'ga_id' => env('GA_ID'),

    'search_console_verification' => env('SEARCH_CONSOLE_VERIFICATION'),

];
