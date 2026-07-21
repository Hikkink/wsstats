<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("
        SELECT d.id, d.nombre, d.colores, s.nombre as serie_nombre
        FROM decks d
        LEFT JOIN series s ON d.serie_id = s.id
        ORDER BY s.nombre, d.nombre
    ");
    $decks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($decks);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>