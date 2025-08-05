<?php
header('Content-Type: application/json');

require_once 'conexion.php'; // Debe crear la variable $pdo (PDO instance)

try {
    // Validar entrada
    if (!isset($_GET['chef_id']) || !isset($_GET['accion'])) {
        throw new Exception("Faltan parámetros requeridos (chef_id o accion)");
    }

    $chefId = intval($_GET['chef_id']);
    $accion = trim($_GET['accion']);

    // Solo traer la última solicitud PENDIENTE
    $stmt = $pdo->prepare("
        SELECT *
        FROM vw_solicitud_chef_detalle
        WHERE chef_id = :chef_id
          AND accion = :accion
          AND estado = 'pendiente'
        ORDER BY solicitud_id DESC
        LIMIT 1
    ");
    $stmt->execute([
        ':chef_id' => $chefId,
        ':accion' => $accion,
    ]);

    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'datos' => $datos
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
