<?php
// api/add_series.php
header('Content-Type: application/json');
require_once 'config.php';

// Solo permitir POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    
    // Si no viene por POST, intentar JSON raw
    if (empty($nombre)) {
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $data = json_decode($rawInput, true);
            $nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
        }
    }

    // Validar nombre
    if (empty($nombre)) {
        echo json_encode(['error' => 'El nombre de la serie es obligatorio']);
        exit;
    }

    // Conectar a BD y verificar duplicado
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM series WHERE nombre = ?");
    $stmt->execute([$nombre]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['error' => 'La serie ya existe']);
        exit;
    }

    // Procesar imagen
    $imagen_url = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['imagen'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensiones_validas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $extensiones_validas)) {
            echo json_encode(['error' => 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP)']);
            exit;
        }

        // Tamaño máximo 5MB (ajusta según necesites)
        if ($archivo['size'] > 5 * 1024 * 1024) {
            echo json_encode(['error' => 'La imagen no puede superar los 5MB']);
            exit;
        }

        // Generar nombre único
        $nombre_archivo = uniqid() . '.' . $extension;
        
        // Ruta de destino: /img/series/
        $upload_dir = __DIR__ . '/../img/series/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ruta_destino = $upload_dir . $nombre_archivo;

        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            echo json_encode(['error' => 'Error al guardar la imagen']);
            exit;
        }

        // Guardar ruta relativa (desde la raíz del sitio)
        $imagen_url = 'img/series/' . $nombre_archivo;
    }

    // Insertar serie
    $stmt = $pdo->prepare("INSERT INTO series (nombre, imagen_url) VALUES (?, ?)");
    $resultado = $stmt->execute([$nombre, $imagen_url]);

    if ($resultado) {
        echo json_encode([
            'success' => 'Serie agregada exitosamente',
            'id' => $pdo->lastInsertId(),
            'nombre' => $nombre,
            'imagen' => $imagen_url
        ]);
    } else {
        echo json_encode(['error' => 'Error al insertar la serie']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
}
?>