<?php
header('Content-Type: application/json');

require '../conexion.php'; // Asegúrate de que este archivo configure correctamente $pdo (PDO)

try {
    $stmt = $pdo->prepare("SELECT * FROM vw_metrica_general");
    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'datos' => $datos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error en la consulta: ' . $e->getMessage()
    ]);
}
