<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    $idUsuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($idUsuario <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID inválido']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM vw_metrica_usuario WHERE IdUsuario = :id");
    $stmt->execute([':id' => $idUsuario]);

    $metricas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'datos' => $metricas]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
