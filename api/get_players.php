<?php
// api/get_players.php
header('Content-Type: application/json');
require_once 'config.php';

try {
    // Usar la función getDBConnection()
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("SELECT id, nombre FROM players ORDER BY nombre ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>