<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM series WHERE id = ?");
        $stmt->execute([$id]);
        $serie = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($serie) {
            echo json_encode($serie);
        } else {
            echo json_encode(['error' => 'Serie no encontrada']);
        }
    } else {
        $stmt = $pdo->query("SELECT id, nombre, imagen_url FROM series ORDER BY nombre");
        $series = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($series);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>