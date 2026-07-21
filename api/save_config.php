<?php
// api/save_config.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar que existan los campos
    $host = isset($_POST['host']) ? trim($_POST['host']) : '';
    $port = isset($_POST['port']) ? trim($_POST['port']) : '3306';  // 👈 AGREGAR PUERTO
    $dbname = isset($_POST['dbname']) ? trim($_POST['dbname']) : '';
    $user = isset($_POST['user']) ? trim($_POST['user']) : '';
    $pass = isset($_POST['pass']) ? $_POST['pass'] : '';
    
    // Validar que no estén vacíos
    if (empty($host) || empty($dbname) || empty($user)) {
        echo "Error: Host, DB Name y User son obligatorios";
        exit;
    }
    
    // Validar que el puerto sea un número
    if (!is_numeric($port) || $port < 1 || $port > 65535) {
        echo "Error: El puerto debe ser un número entre 1 y 65535";
        exit;
    }
    
    $nuevos_datos = [
        "host" => $host,
        "port" => (int)$port,  // 👈 AGREGAR PUERTO
        "dbname" => $dbname,
        "user" => $user,
        "pass" => $pass
    ];
    
    // Guardar en el JSON (ruta correcta)
    $ruta_config = __DIR__ . '/../config.json';
    
    // Verificar permisos de escritura
    if (!is_writable(dirname($ruta_config))) {
        echo "Error: No hay permisos de escritura en el directorio.";
        exit;
    }
    
    if (file_put_contents($ruta_config, json_encode($nuevos_datos, JSON_PRETTY_PRINT))) {
        echo "✅ Configuración guardada exitosamente.";
    } else {
        echo "❌ Error al guardar la configuración. Verifica permisos de escritura.";
    }
}
?>