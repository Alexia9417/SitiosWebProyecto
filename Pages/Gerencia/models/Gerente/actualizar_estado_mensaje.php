<?php
header('Content-Type: application/json');
require_once '../conexion.php'; // instancia PDO $pdo

// Leer JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Entrada inválida']);
    exit;
}

$idMensaje = isset($input['id_mensaje']) ? intval($input['id_mensaje']) : 0;
$estado    = isset($input['estado']) ? trim($input['estado']) : '';

if ($idMensaje <= 0 || $estado === '') {
    echo json_encode(['success' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

try {
    // Si prefieres llamar un SP: CALL sp_actualizar_estado_mensaje(:id, :estado)
    $sql = "UPDATE mensaje_empleado SET Estado = :estado WHERE IdMensaje = :idMensaje";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
    $stmt->bindParam(':idMensaje', $idMensaje, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Estado actualizado']);
    } else {
        // Puede que no exista el IdMensaje o ya tenga ese estado
        echo json_encode(['success' => false, 'error' => 'No se actualizó (Id no existe o mismo estado)']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
