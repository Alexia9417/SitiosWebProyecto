<?php
header('Content-Type: application/json');

require '../conexion.php';

try {
    $stmt = $pdo->query("SELECT * FROM vw_calificaciones");
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "datos" => $resultados
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error al consultar las calificaciones: " . $e->getMessage()
    ]);
}
