<?php
// api/config.php

// Cargar configuración desde config.json
$config_file = __DIR__ . '/../config.json';

if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
    
    define('DB_HOST', $config['host'] ?? 'localhost');
    define('DB_PORT', $config['port'] ?? '3306');
    define('DB_NAME', $config['dbname'] ?? 'weiss');
    define('DB_USER', $config['user'] ?? 'root');
    define('DB_PASS', $config['pass'] ?? '');
} else {
    // Valores por defecto
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'weiss');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Función de conexión a BD
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Error de conexión a BD: ' . $e->getMessage()]));
    }
}
?>