<?php
// api/migrate_db.php
// Verifica que la base de datos tenga el esquema esperado (tablas y columnas)
// y crea lo que falte. Es idempotente: se puede ejecutar varias veces sin romper nada.
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDBConnection();

    // --- ESQUEMA OBJETIVO ---
    // Definición DDL de cada tabla (para crearla si no existe)
    $ddl = [
        'series' => "CREATE TABLE IF NOT EXISTS `series` (
            `id` int NOT NULL AUTO_INCREMENT,
            `nombre` varchar(100) NOT NULL,
            `imagen_url` varchar(255) NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        'players' => "CREATE TABLE IF NOT EXISTS `players` (
            `id` int NOT NULL AUTO_INCREMENT,
            `nombre` varchar(50) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        'decks' => "CREATE TABLE IF NOT EXISTS `decks` (
            `id` int NOT NULL AUTO_INCREMENT,
            `serie_id` int NOT NULL,
            `nombre` varchar(100) NOT NULL,
            `colores` varchar(20) NULL DEFAULT NULL,
            `decklist` text NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            INDEX `serie_id` (`serie_id`),
            CONSTRAINT `decks_ibfk_1` FOREIGN KEY (`serie_id`) REFERENCES `series`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",

        'matches' => "CREATE TABLE IF NOT EXISTS `matches` (
            `id` int NOT NULL AUTO_INCREMENT,
            `fecha` datetime NULL DEFAULT CURRENT_TIMESTAMP,
            `player1_id` int NOT NULL,
            `deck1_id` int NOT NULL,
            `player2_id` int NOT NULL,
            `deck2_id` int NOT NULL,
            `winner_player_id` int NOT NULL,
            `winner_deck_id` int NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `player1_id` (`player1_id`),
            INDEX `deck1_id` (`deck1_id`),
            INDEX `player2_id` (`player2_id`),
            INDEX `deck2_id` (`deck2_id`),
            INDEX `winner_player_id` (`winner_player_id`),
            INDEX `winner_deck_id` (`winner_deck_id`),
            CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`player1_id`) REFERENCES `players`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`deck1_id`) REFERENCES `decks`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`player2_id`) REFERENCES `players`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`deck2_id`) REFERENCES `decks`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `matches_ibfk_5` FOREIGN KEY (`winner_player_id`) REFERENCES `players`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `matches_ibfk_6` FOREIGN KEY (`winner_deck_id`) REFERENCES `decks`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    ];

    // Columnas requeridas por tabla (para añadirlas a tablas ya existentes)
    // CREATE TABLE IF NOT EXISTS no añade columnas a tablas viejas, por eso
    // se hace un chequeo por separado de cada columna objetivo.
    $required_columns = [
        'series' => [
            'id'         => 'int NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'nombre'     => 'varchar(100) NOT NULL',
            'imagen_url' => 'varchar(255) NULL DEFAULT NULL'
        ],
        'players' => [
            'id'     => 'int NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'nombre' => 'varchar(50) NOT NULL'
        ],
        'decks' => [
            'id'       => 'int NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'serie_id' => 'int NOT NULL',
            'nombre'   => 'varchar(100) NOT NULL',
            'colores'  => 'varchar(20) NULL DEFAULT NULL',
            'decklist' => 'text NULL DEFAULT NULL'
        ],
        'matches' => [
            'id'                => 'int NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'fecha'             => 'datetime NULL DEFAULT CURRENT_TIMESTAMP',
            'player1_id'        => 'int NOT NULL',
            'deck1_id'          => 'int NOT NULL',
            'player2_id'        => 'int NOT NULL',
            'deck2_id'          => 'int NOT NULL',
            'winner_player_id'  => 'int NOT NULL',
            'winner_deck_id'    => 'int NOT NULL'
        ]
    ];

    // Orden de creación según dependencias de FK
    $order = ['series', 'players', 'decks', 'matches'];

    $created_tables = [];
    $added_columns = [];

    // Nombre de la base de datos a la que estamos conectados
    $db_name = $pdo->query("SELECT DATABASE()")->fetchColumn();

    // 1) Crear las tablas que falten
    foreach ($order as $table) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?"
        );
        $stmt->execute([$db_name, $table]);
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec($ddl[$table]);
            $created_tables[] = $table;
        }
    }

    // 2) Añadir las columnas que falten a tablas ya existentes
    foreach ($required_columns as $table => $columns) {
        $stmt = $pdo->prepare(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?"
        );
        $stmt->execute([$db_name, $table]);
        $existing = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'COLUMN_NAME');

        foreach ($columns as $col_name => $col_def) {
            if (!in_array($col_name, $existing)) {
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col_name` $col_def");
                $added_columns[] = ['table' => $table, 'column' => $col_name];
            }
        }
    }

    // Respuesta
    $message = (empty($created_tables) && empty($added_columns))
        ? 'Base de datos en orden'
        : 'Base de datos verificada y actualizada';

    echo json_encode([
        'success'        => true,
        'created_tables' => $created_tables,
        'added_columns'  => $added_columns,
        'message'        => $message
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
