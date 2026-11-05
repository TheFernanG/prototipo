<?php
// Usa las variables de Render o los valores locales de XAMPP.
return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: 3306,
    'database' => getenv('DB_NAME') ?: 'comunidad_escolar',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => (string) getenv('DB_PASSWORD'),
    'ssl_ca' => getenv('DB_SSL_CA') ?: null,
    'timezone' => 'America/Mexico_City',
];
