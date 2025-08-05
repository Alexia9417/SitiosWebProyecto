<?php
header('Content-Type: application/json');
require_once 'conexion.php';  // asume $pdo

// Recibe JSON con solicitud_id
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['solicitud_id'])) {
    echo json_encode(['success' => false, 'error' => 'Falta solicitud_id']);
    exit;
}

$solicitudId = intval($input['solicitud_id']);

try {
    $stmt = $pdo->prepare("CALL sp_completar_solicitud_chef(:id)");
    $stmt->bindParam(':id', $solicitudId, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
