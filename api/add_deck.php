<?php
// api/add_deck.php
header('Content-Type: application/json');

// Incluir configuración
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Obtener datos del POST
        $serie_id = isset($_POST['serie_id']) ? intval($_POST['serie_id']) : 0;
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        
        // Los colores vienen como un Array (ej: ['RED', 'BLUE']) desde los checkboxes
        $colores = isset($_POST['colores']) ? $_POST['colores'] : [];
        
        // Validar que sea un array y limpiar
        if (!is_array($colores)) {
            $colores = [];
        }
        
        // Filtrar colores válidos
        $colores_validos = ['RED', 'BLUE', 'GREEN', 'YELLOW'];
        $colores_filtrados = array_intersect($colores, $colores_validos);
        $colores_string = implode(',', $colores_filtrados);
        
        // Validar serie_id
        if ($serie_id <= 0) {
            echo json_encode(['error' => 'Debes seleccionar una serie']);
            exit;
        }
        
        // Validar nombre
        if (empty($nombre)) {
            echo json_encode(['error' => 'El nombre del deck es obligatorio']);
            exit;
        }
        
        // Validar que tenga al menos un color
        if (empty($colores_filtrados)) {
            echo json_encode(['error' => 'Debes seleccionar al menos un color para el deck']);
            exit;
        }
        
        // Conectar a BD
        $pdo = getDBConnection();
        
        // Verificar que la serie existe
        $stmt = $pdo->prepare("SELECT id FROM series WHERE id = ?");
        $stmt->execute([$serie_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'La serie seleccionada no existe']);
            exit;
        }
        
        // Verificar si ya existe un deck con el mismo nombre en la misma serie
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM decks WHERE nombre = ? AND serie_id = ?");
        $stmt->execute([$nombre, $serie_id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'Ya existe un deck con ese nombre en esta serie']);
            exit;
        }
        
        // Insertar deck
        $stmt = $pdo->prepare("INSERT INTO decks (serie_id, nombre, colores) VALUES (?, ?, ?)");
        $resultado = $stmt->execute([$serie_id, $nombre, $colores_string]);
        
        if ($resultado) {
            // Obtener el nombre de la serie para la respuesta
            $stmt = $pdo->prepare("SELECT nombre FROM series WHERE id = ?");
            $stmt->execute([$serie_id]);
            $serie_nombre = $stmt->fetchColumn();
            
            echo json_encode([
                'success' => 'Deck creado exitosamente',
                'id' => $pdo->lastInsertId(),
                'nombre' => $nombre,
                'serie' => $serie_nombre,
                'colores' => $colores_filtrados
            ]);
        } else {
            echo json_encode(['error' => 'Error al crear el deck']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
    }
}
?>