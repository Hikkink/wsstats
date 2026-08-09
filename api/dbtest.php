<?php
// api/test_db_connection.php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para responder en JSON
function responderJSON($data) {
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Cargar configuración
$config_file = __DIR__ . '/../config.json';

if (!file_exists($config_file)) {
    responderJSON([
        'success' => false,
        'error' => 'Archivo config.json no encontrado',
        'path' => $config_file
    ]);
}

$config = json_decode(file_get_contents($config_file), true);

if (!$config) {
    responderJSON([
        'success' => false,
        'error' => 'Error al leer config.json',
        'content' => file_get_contents($config_file)
    ]);
}

// Mostrar configuración (ocultando contraseña)
$config_mostrar = $config;
$config_mostrar['pass'] = '******';

// Intentar conectar
try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
    
    $pdo = new PDO(
        $dsn,
        $config['user'],
        $config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Probar consulta
    $stmt = $pdo->query("SELECT 1 as test, NOW() as time, DATABASE() as db, VERSION() as version");
    $result = $stmt->fetch();
    
    responderJSON([
        'success' => true,
        'connection' => '✅ Conectado exitosamente',
        'database' => $result['db'],
        'version' => $result['version'],
        'server_time' => $result['time'],
        'config' => $config_mostrar
    ]);
    
} catch (PDOException $e) {
    responderJSON([
        'success' => false,
        'error' => 'Error de conexión: ' . $e->getMessage(),
        'dsn' => $dsn,
        'config' => $config_mostrar,
        'suggestions' => [
            'Verifica que el host sea correcto',
            'Verifica que el puerto sea correcto',
            'Verifica que la base de datos exista',
            'Verifica que el usuario tenga permisos',
            'Si estás en Railway, usa mysql.railway.internal como host'
        ]
    ]);
}
?>