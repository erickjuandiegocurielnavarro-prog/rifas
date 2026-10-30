<?php
/**
 * ====================================================================
 * CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS MYSQL (AppServ / localhost)
 * ====================================================================
 * Si tu MySQL de AppServ tiene una contraseña personalizada, cambia la
 * constante DB_PASS a continuación.
 */

define('DB_HOST', 'localhost');    // Servidor MySQL (localhost o 127.0.0.1)
define('DB_PORT', '3306');         // Puerto de MySQL (estándar 3306)
define('DB_USER', 'root');         // Usuario de MySQL (por defecto 'root')
define('DB_PASS', '');             // <--- COLOCA AQUÍ LA CONTRASEÑA DE TU MYSQL (Si la tiene)
define('DB_NAME', 'agencia_auto'); // Nombre de la base de datos importada

function getDBConnection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Conexión PDO a MySQL localhost
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Intentar crear la BD en MySQL si no existiera
        try {
            $pdoNoDb = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdoNoDb->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        } catch (PDOException $e2) {
            // Respaldar a SQLite local para evitar caída del sitio si MySQL no responde
            $sqlitePath = __DIR__ . '/agencia_auto.sqlite';
            $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        }
    }
}
