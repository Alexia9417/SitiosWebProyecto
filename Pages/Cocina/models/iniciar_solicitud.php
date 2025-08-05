<?php
require 'conexion.php'; // Debe crear la variable $conn como instancia PDO

header('Content-Type: application/json');

// 1) Validar parámetros
if (!isset($_POST['chef_id'], $_POST['nombre'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'Faltan parámetros requeridos (chef_id, nombre)'
    ]);
    exit;
}

$chef_id = intval($_POST['chef_id']);
$nombre  = trim($_POST['nombre']);

try {
    // 2) Llamar al SP para insertar la solicitud
    $stmt = $conn->prepare("CALL sp_iniciar_solicitud_rapida(:chef_id, :nombre)");
    $stmt->bindParam(':chef_id', $chef_id, PDO::PARAM_INT);
    $stmt->bindParam(':nombre',  $nombre,  PDO::PARAM_STR);
    $stmt->execute();

    // 3) Obtener el ID recién insertado
    $solicitud_id = $conn->lastInsertId();

    // 4) Devolver JSON con éxito y el ID
    echo json_encode([
        'success'       => true,
        'solicitud_id'  => $solicitud_id
    ]);
} catch (PDOException $e) {
    // 5) En caso de error, devolverlo en JSON
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
