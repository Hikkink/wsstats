<?php
// api/stats_player.php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDBConnection();
    $player_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($player_id <= 0) {
        echo json_encode(['error' => 'ID de jugador inválido']);
        exit;
    }

    // 1. Winrate general
    $qStats = "SELECT COUNT(*) as total,
                      SUM(CASE WHEN winner_player_id = ? THEN 1 ELSE 0 END) as wins
               FROM matches
               WHERE player1_id = ? OR player2_id = ?";
    $stmt = $pdo->prepare($qStats);
    $stmt->execute([$player_id, $player_id, $player_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Comportamiento de sus decks (con imagen de la serie)
    $qDecks = "SELECT d.nombre,
                      s.imagen_url,
                      COUNT(m.id) as jugados,
                      SUM(CASE WHEN m.winner_player_id = ? THEN 1 ELSE 0 END) as wins
               FROM matches m
               JOIN decks d ON (m.deck1_id = d.id AND m.player1_id = ?)
                            OR (m.deck2_id = d.id AND m.player2_id = ?)
               JOIN series s ON d.serie_id = s.id
               WHERE m.player1_id = ? OR m.player2_id = ?
               GROUP BY d.id";
    $stmtDecks = $pdo->prepare($qDecks);
    $stmtDecks->execute([$player_id, $player_id, $player_id, $player_id, $player_id]);
    $decks = $stmtDecks->fetchAll(PDO::FETCH_ASSOC);

    // 3. Némesis (decks contra los que pierde, con imagen de la serie)
    $qNemesis = "SELECT d.nombre,
                        s.imagen_url,
                        COUNT(m.id) as derrotas
                 FROM matches m
                 JOIN decks d ON m.winner_deck_id = d.id
                 JOIN series s ON d.serie_id = s.id
                 WHERE (m.player1_id = ? OR m.player2_id = ?)
                   AND m.winner_player_id != ?
                 GROUP BY d.id
                 ORDER BY derrotas DESC
                 LIMIT 3";
    $stmtNemesis = $pdo->prepare($qNemesis);
    $stmtNemesis->execute([$player_id, $player_id, $player_id]);
    $nemesis = $stmtNemesis->fetchAll(PDO::FETCH_ASSOC);

    // Respuesta
    echo json_encode([
        'stats'   => $stats,
        'decks'   => $decks,
        'nemesis' => $nemesis
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>