<?php
require 'conexion.php'; // Asegúrate que define $conn

try {
    // Consulta a la tabla catalogo_solicitud_rapida del esquema chef
    $sql = "SELECT * FROM accion";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devuelve como JSON (útil si lo usas vía AJAX)
    echo json_encode([
        "success" => true,
        "datos" => $resultados
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error en la consulta: " . $e->getMessage()
    ]);
}
