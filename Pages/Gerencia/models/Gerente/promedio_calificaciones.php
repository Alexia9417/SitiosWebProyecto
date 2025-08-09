<?php
header('Content-Type: application/json');
require '../conexion.php'; // Asegúrate de tener la conexión configurada correctamente

try {
    $stmt = $pdo->query("
        SELECT
          ROUND(AVG(c.Estrellas), 2) AS PromedioEstrellas,
          COUNT(*) AS TotalComentarios
        FROM calificacion c
    ");

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "datos" => [$resultado]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error en la consulta: " . $e->getMessage()
    ]);
}
