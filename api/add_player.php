<?php
// api/add_player.php
header('Content-Type: application/json');

// Incluir configuración
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Obtener datos del POST - ACEPTAR AMBOS NOMBRES
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        if (empty($nombre)) {
            $nombre = isset($_POST['nombre_jugador']) ? trim($_POST['nombre_jugador']) : '';
        }
        
        // Validar nombre
        if (empty($nombre)) {
            echo json_encode(['error' => 'El nombre del jugador es obligatorio']);
            exit;
        }
        
        // Conectar a BD
        $pdo = getDBConnection();
        
        // Verificar si ya existe el jugador
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM players WHERE nombre = ?");
        $stmt->execute([$nombre]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'El jugador ya existe']);
            exit;
        }
        
        // Insertar jugador
        $stmt = $pdo->prepare("INSERT INTO players (nombre) VALUES (?)");
        $resultado = $stmt->execute([$nombre]);
        
        if ($resultado) {
            echo json_encode([
                'success' => 'Jugador registrado exitosamente',
                'id' => $pdo->lastInsertId(),
                'nombre' => $nombre
            ]);
        } else {
            echo json_encode(['error' => 'Error al registrar el jugador']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
    }
}
?>