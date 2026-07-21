<?php
// api/get_decks_by_series.php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    // Caso 1: Obtener un deck por ID
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM decks WHERE id = ?");
        $stmt->execute([$id]);
        $deck = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($deck) {
            echo json_encode($deck);
        } else {
            echo json_encode(['error' => 'Deck no encontrado']);
        }
        exit;
    }
    
    // Caso 2: Obtener decks por serie_id
    if (isset($_GET['serie_id']) && is_numeric($_GET['serie_id'])) {
        $serie_id = (int)$_GET['serie_id'];
        $stmt = $pdo->prepare("SELECT id, nombre, colores FROM decks WHERE serie_id = ? ORDER BY nombre ASC");
        $stmt->execute([$serie_id]);
        $decks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($decks);
        exit;
    }
    
    // Caso 3: Obtener todos los decks (sin parámetros)
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