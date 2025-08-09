<?php
include 'conexion.php';

$opcion = isset($_GET['id']) ? 2 : 1;
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("CALL sp_order_report(:opcion, :order_id)");
$stmt->bindParam(':opcion', $opcion, PDO::PARAM_INT);
$stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
$stmt->execute();

$result = [];
do {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        $result = array_merge($result, $rows);
    }
} while ($stmt->nextRowset());

if ($result) {
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'No encontrado']);
}
