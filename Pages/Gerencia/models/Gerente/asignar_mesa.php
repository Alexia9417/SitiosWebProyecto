<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    $idArea    = isset($_POST['id_area'])    ? intval($_POST['id_area'])    : 0;
    $idUsuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
    // Esto convierte 'true'/'false' o cualquier cadena en 1 o 0
    $asignar   = isset($_POST['asignar']) && $_POST['asignar'] !== ''
        ? (int) filter_var($_POST['asignar'], FILTER_VALIDATE_BOOLEAN)
        : 0;

    $stmt = $pdo->prepare("CALL sp_GestionMeseroArea(:area, :usuario, :asignar)");
    $stmt->bindValue(':area',    $idArea,    PDO::PARAM_INT);
    $stmt->bindValue(':usuario', $idUsuario, PDO::PARAM_INT);
    $stmt->bindValue(':asignar', $asignar,   PDO::PARAM_INT);  // aquí forzamos entero
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
