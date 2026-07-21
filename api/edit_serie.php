<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['error' => 'ID inválido']);
        exit;
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM series WHERE id = ?");
    $stmt->execute([$id]);
    $serie = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$serie) {
        echo json_encode(['error' => 'Serie no encontrada']);
        exit;
    }

    $nuevo_nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $actualizar_nombre = !empty($nuevo_nombre);

    if ($actualizar_nombre) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM series WHERE nombre = ? AND id != ?");
        $stmt->execute([$nuevo_nombre, $id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'Ya existe otra serie con ese nombre']);
            exit;
        }
    }

    $nueva_imagen = false;
    $imagen_url = $serie['imagen_url'];

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['imagen'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $validas = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($extension, $validas)) {
            echo json_encode(['error' => 'Formato no permitido']);
            exit;
        }
        if ($archivo['size'] > 5*1024*1024) {
            echo json_encode(['error' => 'Máximo 5MB']);
            exit;
        }
        $nombre_archivo = uniqid() . '.' . $extension;
        $upload_dir = __DIR__ . '/../img/series/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ruta_destino = $upload_dir . $nombre_archivo;
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            echo json_encode(['error' => 'Error al guardar la imagen']);
            exit;
        }
        // Eliminar la anterior
        if (!empty($serie['imagen_url'])) {
            $ruta_vieja = __DIR__ . '/../' . $serie['imagen_url'];
            if (file_exists($ruta_vieja)) unlink($ruta_vieja);
        }
        $imagen_url = 'img/series/' . $nombre_archivo;
        $nueva_imagen = true;
    }

    $set = [];
    $params = [];
    if ($actualizar_nombre) { $set[] = "nombre = ?"; $params[] = $nuevo_nombre; }
    if ($nueva_imagen) { $set[] = "imagen_url = ?"; $params[] = $imagen_url; }
    if (empty($set)) {
        echo json_encode(['error' => 'No hay datos para actualizar']);
        exit;
    }
    $params[] = $id;
    $sql = "UPDATE series SET " . implode(', ', $set) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => 'Serie actualizada',
        'id' => $id,
        'nombre' => $actualizar_nombre ? $nuevo_nombre : $serie['nombre'],
        'imagen' => $imagen_url
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>