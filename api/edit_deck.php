<?php
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
    $stmt = $pdo->prepare("SELECT * FROM decks WHERE id = ?");
    $stmt->execute([$id]);
    $deck = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$deck) {
        echo json_encode(['error' => 'Deck no encontrado']);
        exit;
    }

    $nuevo_nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $actualizar_nombre = !empty($nuevo_nombre);
    $nuevos_colores = isset($_POST['colores']) ? $_POST['colores'] : null;
    $actualizar_colores = isset($_POST['colores']); // permite vacío
    $nuevo_decklist = isset($_POST['decklist']) ? trim($_POST['decklist']) : '';
    $actualizar_decklist = isset($_POST['decklist']); // permite vacío

    if ($actualizar_nombre) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM decks WHERE nombre = ? AND id != ?");
        $stmt->execute([$nuevo_nombre, $id]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'Ya existe otro deck con ese nombre']);
            exit;
        }
    }

    $set = [];
    $params = [];
    if ($actualizar_nombre) { $set[] = "nombre = ?"; $params[] = $nuevo_nombre; }
    if ($actualizar_colores) {
        $colores_str = is_array($nuevos_colores) ? implode(',', $nuevos_colores) : '';
        $set[] = "colores = ?";
        $params[] = $colores_str;
    }
    if ($actualizar_decklist) {
        $set[] = "decklist = ?";
        $params[] = $nuevo_decklist;
    }
    if (empty($set)) {
        echo json_encode(['error' => 'No hay datos para actualizar']);
        exit;
    }
    $params[] = $id;
    $sql = "UPDATE decks SET " . implode(', ', $set) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => 'Deck actualizado',
        'id' => $id,
        'nombre' => $actualizar_nombre ? $nuevo_nombre : $deck['nombre'],
        'colores' => $actualizar_colores ? (is_array($nuevos_colores) ? implode(',', $nuevos_colores) : '') : $deck['colores']
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>