<?php
// api/add_match.php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Usar la función getDBConnection()
        $pdo = getDBConnection();
        
        $p1 = isset($_POST['p1_id']) ? intval($_POST['p1_id']) : 0;
        $d1 = isset($_POST['p1_deck_id']) ? intval($_POST['p1_deck_id']) : 0;
        $p2 = isset($_POST['p2_id']) ? intval($_POST['p2_id']) : 0;
        $d2 = isset($_POST['p2_deck_id']) ? intval($_POST['p2_deck_id']) : 0;
        $winner_selection = isset($_POST['winner']) ? $_POST['winner'] : ''; // '1' o '2'
        
        // Validar datos
        if ($p1 <= 0 || $d1 <= 0 || $p2 <= 0 || $d2 <= 0 || empty($winner_selection)) {
            echo json_encode(['error' => 'Todos los campos son obligatorios']);
            exit;
        }
        
        // Determinar IDs ganadores
        $winner_player = ($winner_selection == '1') ? $p1 : $p2;
        $winner_deck = ($winner_selection == '1') ? $d1 : $d2;
        
        $stmt = $pdo->prepare("INSERT INTO matches (player1_id, deck1_id, player2_id, deck2_id, winner_player_id, winner_deck_id) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$p1, $d1, $p2, $d2, $winner_player, $winner_deck])) {
            echo json_encode(['success' => 'Enfrentamiento registrado correctamente']);
        } else {
            echo json_encode(['error' => 'Error al registrar el enfrentamiento']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
?>