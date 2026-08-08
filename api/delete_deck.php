<?php
// api/delete_deck.php
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

    // Verificar que el deck exista
    $stmt = $pdo->prepare("SELECT nombre FROM decks WHERE id = ?");
    $stmt->execute([$id]);
    $nombre = $stmt->fetchColumn();
    if (!$nombre) {
        echo json_encode(['error' => 'Deck no encontrado']);
        exit;
    }

    // Borrar el deck y todas sus partidas en una transacción
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("DELETE FROM matches WHERE deck1_id = ? OR deck2_id = ?");
        $stmt->execute([$id, $id]);

        $stmt = $pdo->prepare("DELETE FROM decks WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        echo json_encode(['success' => 'Deck "' . $nombre . '" y sus partidas eliminados correctamente']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Error al eliminar el deck: ' . $e->getMessage()]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
