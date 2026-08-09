<?php
// api/backup_db.php
// Genera un backup SQL completo de la base de datos (estructura + datos)
// y lo descarga como archivo .sql. Funciona en hosts compartidos sin
// depender de mysqldump: el dump se construye en PHP puro.

require_once 'config.php';

try {
    $pdo = getDBConnection();

    $db_name = $pdo->query("SELECT DATABASE()")->fetchColumn();

    // Encabezado del dump
    $dump = "-- Weiss Schwarz OS - Backup de la base de datos\n";
    $dump .= "-- Base de datos: {$db_name}\n";
    $dump .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
    $dump .= "-- Generado por: api/backup_db.php\n\n";
    $dump .= "SET NAMES utf8mb4;\n";
    $dump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    // Todas las tablas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $table = (string)$table;

        // Estructura de la tabla
        $row = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $create = end($row);

        $dump .= "-- ----------------------------\n";
        $dump .= "-- Table structure for {$table}\n";
        $dump .= "-- ----------------------------\n";
        $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $dump .= $create . ";\n\n";

        // Datos de la tabla
        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_NUM);
        if (count($rows) > 0) {
            $dump .= "-- ----------------------------\n";
            $dump .= "-- Records of {$table}\n";
            $dump .= "-- ----------------------------\n";
            $dump .= "INSERT INTO `{$table}` VALUES\n";

            $lines = [];
            foreach ($rows as $row) {
                $vals = [];
                foreach ($row as $val) {
                    // null -> NULL, el resto se escapa correctamente con PDO::quote
                    $vals[] = $val === null ? 'NULL' : $pdo->quote($val);
                }
                $lines[] = "(" . implode(", ", $vals) . ")";
            }
            $dump .= implode(",\n", $lines) . ";\n\n";
        }
    }

    $dump .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    // Descargar como archivo .sql
    $filename = $db_name . '_backup_' . date('Ymd_His') . '.sql';
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($dump));
    echo $dump;

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Error al generar el backup: " . $e->getMessage();
}
?>
