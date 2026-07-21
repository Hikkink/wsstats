<?php
// api/save_config.php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar que existan los campos
    $host = isset($_POST['host']) ? $_POST['host'] : '';
    $dbname = isset($_POST['dbname']) ? $_POST['dbname'] : '';
    $user = isset($_POST['user']) ? $_POST['user'] : '';
    $pass = isset($_POST['pass']) ? $_POST['pass'] : '';
    
    // Validar que no estén vacíos
    if (empty($host) || empty($dbname) || empty($user)) {
        echo "Error: Host, DB Name y User son obligatorios";
        exit;
    }
    
    $nuevos_datos = [
        "host" => $host,
        "dbname" => $dbname,
        "user" => $user,
        "pass" => $pass
    ];
    
    // Guardar en el JSON (ruta correcta)
    $ruta_config = __DIR__ . '/../config.json';
    
    if (file_put_contents($ruta_config, json_encode($nuevos_datos, JSON_PRETTY_PRINT))) {
        echo "Configuración guardada exitosamente.";
    } else {
        echo "Error al guardar la configuración. Verifica permisos de escritura.";
    }
}
?>