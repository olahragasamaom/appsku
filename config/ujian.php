<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inactivity Timeout
    |--------------------------------------------------------------------------
    |
    | Durasi (dalam menit) toleransi inaktif peserta yang sedang ujian.
    | Jika peserta tidak melakukan aktivitas (heartbeat/save answer) selama
    | durasi ini, maka attempt akan otomatis difinalisasi (status: selesai).
    |
    | Default: 240 menit (4 jam) - mengakomodasi kasus laptop sleep,
    | internet terputus, atau lupa logout.
    |
    */

    'inactivity_timeout_minutes' => env('UJIAN_INACTIVITY_TIMEOUT_MINUTES', 240),

];
