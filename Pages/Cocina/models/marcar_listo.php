<?php
header('Content-Type: application/json');
require 'conexion.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !isset($data['id_orden'], $data['id_platillo']) ||
        !is_numeric($data['id_orden']) ||
        !is_numeric($data['id_platillo'])
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos']);
        exit;
    }

    $idOrden = intval($data['id_orden']);
    $idPlatillo = intval($data['id_platillo']);

    $stmt = $pdo->prepare("CALL sp_marcar_platillo_listo(:orden, :platillo)");
    $stmt->execute([
        ':orden' => $idOrden,
        ':platillo' => $idPlatillo
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Error del motor SQL
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error en marcar_platillo_listo: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

