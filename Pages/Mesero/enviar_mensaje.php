<?php
include '../../conexion.php'; // Asegúrate de que la ruta sea correcta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idOrden = $_POST['id_orden'];
    $idMesero = $_POST['id_mesero'];
    $mensaje = $_POST['mensaje'];

    $sql = "INSERT INTO mensajes_cocina (id_orden, id_mesero, mensaje) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $idOrden, $idMesero, $mensaje);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
