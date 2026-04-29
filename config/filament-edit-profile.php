<?php

return [
    'name_column' => 'name',
    'locale_column' => 'locale',
    'theme_color_column' => 'theme_color',
    'avatar_column' => 'avatar_url',
    'disk' => env('FILESYSTEM_DISK', 'public'),
    'visibility' => 'public', // or replace by filesystem disk visibility with fallback value
    'password_update_rules' => [
        'current_password' => ['required', 'current_password'],
        'password' => ['required', 'confirmed', 'string', 'min:8', 'mixedCase', 'numbers', 'symbols'], // Ovdje dodajete svoja pravila
    ],
];
