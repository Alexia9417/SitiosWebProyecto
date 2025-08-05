<?php
include 'conexion.php';

$estacion_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM vw_orden_plato_estacion_resumen WHERE estacion_ID = :id");
$stmt->bindParam(':id', $estacion_id, PDO::PARAM_INT);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($data) {
    echo json_encode($data);
} else {
    echo json_encode(["error" => "No se encontraron platillos."]);
}
?>