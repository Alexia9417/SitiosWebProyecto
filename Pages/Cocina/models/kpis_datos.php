<?php
require 'conexion.php';
require 'kpis.php';

$chef_id = isset($_GET['chef_id']) ? intval($_GET['chef_id']) : 1;

list($chef, $metricas) = obtenerChefYMetricas($conn, $chef_id);

try {
    $stmt = $conn->prepare("SELECT * FROM view_estaciones_por_chef WHERE chef_id = ?");
    $stmt->execute([$chef_id]);
    $estaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "chef" => $chef,
        "metricas" => $metricas,
        "estaciones" => $estaciones
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
