<?php
header('Content-Type: application/json');
require 'conexion.php'; // Asegúrate de que crea $conn como PDO

try {
    // 1) Ejecutar la consulta con ROLLUP
    $sql = "
        SELECT
          estado,
          COUNT(*) AS cantidad
        FROM orden
        GROUP BY estado WITH ROLLUP
    ";
    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) Preparar la salida
    // En MySQL, el ROLLUP pondrá NULL en estado para la fila total
    $output = [];
    foreach ($rows as $row) {
        $estado = $row['estado'] === null ? 'TOTAL' : $row['estado'];
        $output[] = [
            'estado'   => $estado,
            'cantidad' => (int)$row['cantidad']
        ];
    }

    // 3) Devolver JSON
    echo json_encode([
        'success' => true,
        'datos'   => $output
    ]);
} catch (PDOException $e) {
    // 4) En caso de error
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
