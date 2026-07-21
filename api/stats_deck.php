<?php
// api/stats_deck.php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDBConnection();
    $deck_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($deck_id <= 0) {
        echo json_encode(['error' => 'ID de deck inválido']);
        exit;
    }

    // 1. Información Básica del Deck
    $qInfo = "SELECT 
               d.nombre as deck_nombre,
               d.colores,
               s.nombre as serie_nombre,
               s.imagen_url
              FROM decks d
              JOIN series s ON d.serie_id = s.id
              WHERE d.id = ?";
    $stmtInfo = $pdo->prepare($qInfo);
    $stmtInfo->execute([$deck_id]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        echo json_encode(['error' => 'Deck no encontrado']);
        exit;
    }

    // 2. Rendimiento General W/L del Deck
    $qStats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN winner_deck_id = ? THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN winner_deck_id != ? THEN 1 ELSE 0 END) as losses,
                CASE 
                    WHEN COUNT(*) > 0 
                    THEN ROUND((SUM(CASE WHEN winner_deck_id = ? THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) 
                    ELSE 0 
                END as winrate
               FROM matches 
               WHERE deck1_id = ? OR deck2_id = ?";
    $stmtStats = $pdo->prepare($qStats);
    $stmtStats->execute([$deck_id, $deck_id, $deck_id, $deck_id, $deck_id]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

    // Si no hay partidas
    if ($stats['total'] == 0) {
        echo json_encode([
            'info' => $info,
            'stats' => $stats,
            'fuerte' => [],
            'debil' => [],
            'evolution' => [],
            'players' => [],
            'mensaje' => 'Este deck aún no tiene partidas registradas'
        ]);
        exit;
    }

    // 3. Matchups - Contra quién es FUERTE (nombres consistentes)
    $qFuerte = "SELECT 
                 d.nombre as nombre,
                 s.imagen_url,
                 COUNT(*) as wins
                FROM matches m
                JOIN decks d ON (m.deck1_id = d.id AND m.deck2_id = ?) OR (m.deck2_id = d.id AND m.deck1_id = ?)
                JOIN series s ON d.serie_id = s.id
                WHERE m.winner_deck_id = ?
                GROUP BY d.id
                ORDER BY wins DESC
                LIMIT 5";
    $stmtFuerte = $pdo->prepare($qFuerte);
    $stmtFuerte->execute([$deck_id, $deck_id, $deck_id]);
    $fuerte = $stmtFuerte->fetchAll(PDO::FETCH_ASSOC);

    // 4. Matchups - Contra quién es DÉBIL (nombres consistentes)
    $qDebil = "SELECT 
                d.nombre as nombre,
                s.imagen_url,
                COUNT(*) as losses
               FROM matches m
               JOIN decks d ON m.winner_deck_id = d.id
               JOIN series s ON d.serie_id = s.id
               WHERE (m.deck1_id = ? OR m.deck2_id = ?) 
                 AND m.winner_deck_id != ?
               GROUP BY d.id
               ORDER BY losses DESC
               LIMIT 5";
    $stmtDebil = $pdo->prepare($qDebil);
    $stmtDebil->execute([$deck_id, $deck_id, $deck_id]);
    $debil = $stmtDebil->fetchAll(PDO::FETCH_ASSOC);

    // 5. Evolución temporal
    $evolution = [];
    try {
        $qEvolution = "SELECT 
                        m.id as match_id,
                        CASE WHEN m.winner_deck_id = ? THEN 'Victoria' ELSE 'Derrota' END as resultado
                       FROM matches m
                       WHERE m.deck1_id = ? OR m.deck2_id = ?
                       ORDER BY m.id DESC
                       LIMIT 10";
        $stmtEvolution = $pdo->prepare($qEvolution);
        $stmtEvolution->execute([$deck_id, $deck_id, $deck_id]);
        $evolution = $stmtEvolution->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $evolution = [];
    }

    // 6. Estadísticas por jugador
    $qPlayers = "SELECT 
                  p.nombre as jugador_nombre,
                  COUNT(m.id) as partidas_jugadas,
                  SUM(CASE WHEN m.winner_deck_id = ? THEN 1 ELSE 0 END) as wins,
                  ROUND((SUM(CASE WHEN m.winner_deck_id = ? THEN 1 ELSE 0 END) / COUNT(m.id)) * 100, 1) as winrate
                 FROM matches m
                 JOIN players p ON (m.player1_id = p.id AND m.deck1_id = ?) OR (m.player2_id = p.id AND m.deck2_id = ?)
                 WHERE m.deck1_id = ? OR m.deck2_id = ?
                 GROUP BY p.id
                 ORDER BY partidas_jugadas DESC
                 LIMIT 5";
    $stmtPlayers = $pdo->prepare($qPlayers);
    $stmtPlayers->execute([$deck_id, $deck_id, $deck_id, $deck_id, $deck_id, $deck_id]);
    $players = $stmtPlayers->fetchAll(PDO::FETCH_ASSOC);

    // Devolver todo en JSON
    echo json_encode([
        'info' => $info,
        'stats' => $stats,
        'fuerte' => $fuerte,
        'debil' => $debil,
        'evolution' => $evolution,
        'players' => $players
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
}
?>