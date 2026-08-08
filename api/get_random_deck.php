<?php
// api/get_random_deck.php
// Devuelve UN deck al azar con su serie, imagen y decklist.
// Soporta ?exclude=ID para no repetir el mismo deck en el siguiente "Otro".
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDBConnection();

    $exclude = isset($_GET['exclude']) && is_numeric($_GET['exclude']) ? (int)$_GET['exclude'] : 0;

    $sql = "SELECT d.id, d.nombre, d.colores, d.decklist, d.serie_id,
                   s.nombre AS serie_nombre, s.imagen_url
            FROM decks d
            JOIN series s ON d.serie_id = s.id";
    $params = [];

    if ($exclude > 0) {
        $sql .= " WHERE d.id != ?";
        $params[] = $exclude;
    }

    $sql .= " ORDER BY RAND() LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $deck = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deck) {
        echo json_encode(['error' => 'No hay decks guardados']);
        exit;
    }

    echo json_encode($deck);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
