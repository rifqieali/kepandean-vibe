<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Jalur panel admin Filament
    |--------------------------------------------------------------------------
    | Awalan URL untuk halaman admin (bawaan: admin -> /admin/login).
    | Ubah lewat FILAMENT_PATH bila jalur bawaan ingin disembunyikan,
    | mis. FILAMENT_PATH=ruang-admin menghasilkan /ruang-admin/login.
    */

    'path' => env('FILAMENT_PATH', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Domain panel admin Filament
    |--------------------------------------------------------------------------
    | Kosongkan untuk memakai domain aplikasi yang sama. Isi bila panel
    | admin dipisah ke subdomain, mis. FILAMENT_DOMAIN=admin.kepandean.desa.id
    */

    'domain' => env('FILAMENT_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Nama merek panel admin
    |--------------------------------------------------------------------------
    */

    'brand_name' => env('FILAMENT_BRAND_NAME', 'Desa Kepandean'),

];
