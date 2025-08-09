<?php
include '../../conexion.php';

$idUsuario = $_POST['idUsuario'];
$comentario = $_POST['comentario'];

$sql = "INSERT INTO queja (IdUsuario, Comentario, FechaHora, Estado) VALUES (?, ?, NOW(), 'Pendiente de revisar')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $idUsuario, $comentario);

if ($stmt->execute()) {
    echo "Queja guardada correctamente";
} else {
    echo "Error al guardar la queja: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
