<?php
// api/stats_balance.php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDBConnection();

    // 1. Top Decks por Winrate (mínimo 3 partidas para ser considerado)
    $qDecks = "SELECT 
                d.nombre as deck_nombre, 
                s.nombre as serie, 
                s.imagen_url, 
                d.colores,
                COUNT(m.id) as total_jugados,
                SUM(CASE WHEN m.winner_deck_id = d.id THEN 1 ELSE 0 END) as victorias,
                ROUND((SUM(CASE WHEN m.winner_deck_id = d.id THEN 1 ELSE 0 END) / COUNT(m.id)) * 100, 1) as winrate
               FROM decks d
               JOIN series s ON d.serie_id = s.id
               JOIN matches m ON (m.deck1_id = d.id OR m.deck2_id = d.id)
               GROUP BY d.id
               HAVING total_jugados >= 3  -- Solo decks con al menos 3 partidas
               ORDER BY winrate DESC, total_jugados DESC 
               LIMIT 10";
    $decks = $pdo->query($qDecks)->fetchAll(PDO::FETCH_ASSOC);

    // 2. Series Más Dominantes (por victorias totales de sus decks)
    $qSeries = "SELECT 
                 s.nombre, 
                 s.imagen_url, 
                 COUNT(m.id) as victorias,
                 COUNT(DISTINCT d.id) as decks_ganadores
                FROM series s
                JOIN decks d ON s.id = d.serie_id
                JOIN matches m ON m.winner_deck_id = d.id
                GROUP BY s.id 
                ORDER BY victorias DESC 
                LIMIT 5";
    $series = $pdo->query($qSeries)->fetchAll(PDO::FETCH_ASSOC);

    // 3. Estadísticas Globales del Meta
    $qGlobal = "SELECT 
                 COUNT(DISTINCT m.id) as total_partidas,
                 COUNT(DISTINCT m.player1_id) + COUNT(DISTINCT m.player2_id) as jugadores_unicos,
                 COUNT(DISTINCT d.id) as decks_usados,
                 COUNT(DISTINCT s.id) as series_representadas
                FROM matches m
                JOIN decks d ON (m.deck1_id = d.id OR m.deck2_id = d.id)
                JOIN series s ON d.serie_id = s.id";
    $global = $pdo->query($qGlobal)->fetch(PDO::FETCH_ASSOC);

    // 4. Mejor y Peor Serie (por winrate)
    $qSerieStats = "SELECT 
                     s.nombre,
                     s.imagen_url,
                     COUNT(m.id) as total_jugados,
                     SUM(CASE WHEN m.winner_deck_id IN (SELECT id FROM decks WHERE serie_id = s.id) THEN 1 ELSE 0 END) as victorias,
                     ROUND((SUM(CASE WHEN m.winner_deck_id IN (SELECT id FROM decks WHERE serie_id = s.id) THEN 1 ELSE 0 END) / COUNT(m.id)) * 100, 1) as winrate
                    FROM series s
                    JOIN decks d ON s.id = d.serie_id
                    JOIN matches m ON (m.deck1_id = d.id OR m.deck2_id = d.id)
                    GROUP BY s.id
                    HAVING total_jugados >= 3
                    ORDER BY winrate DESC";
    $seriesStats = $pdo->query($qSerieStats)->fetchAll(PDO::FETCH_ASSOC);
    
    $mejor_serie = !empty($seriesStats) ? $seriesStats[0] : null;
    $peor_serie = !empty($seriesStats) ? end($seriesStats) : null;

    // Devolver todo en JSON
    echo json_encode([
        'decks' => $decks,
        'series' => $series,
        'global' => $global,
        'mejor_serie' => $mejor_serie,
        'peor_serie' => $peor_serie
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
}
?>