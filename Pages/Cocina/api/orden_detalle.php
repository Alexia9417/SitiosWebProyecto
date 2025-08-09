<?php
require '../models/conexion.php';

$orden_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("
    SELECT 
        m.nombre AS nombre,
        ep.descripcion AS estado,
        op.tiempo
    FROM orden_plato op
    JOIN menu_item m ON op.menu_item_id = m.menu_item_id
    LEFT JOIN estado_plato ep ON op.estado_plato_id = ep.estado_plato_id
    WHERE op.orden_id = ?
");
$stmt->execute([$orden_id]);

$platos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode(['platos' => $platos]);
