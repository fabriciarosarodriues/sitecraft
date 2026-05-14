<?php

declare(strict_types=1);

// Crea y reutiliza una unica conexion PDO para toda la peticion actual.
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Parametros de conexion locales para entorno XAMPP.
    $host = '127.0.0.1';
    $port = 3306;
    $database = 'sitecraft';
    $username = 'root';
    $password = '';
    $charset = 'utf8mb4';

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);

    // Configuracion segura: excepciones activas y prepared statements nativos.
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

    return $pdo;
}
