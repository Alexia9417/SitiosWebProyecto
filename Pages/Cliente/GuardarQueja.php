<?php
session_start();
include '../../conexion.php';

if (!isset($_SESSION['IdUsuario'])) {
    header("Location: ../sitio/login.php");
    exit;
}

$IdUsuario = $_SESSION['IdUsuario'];
$comentario = $_POST['comentario'] ?? '';

if (empty($comentario)) {
    echo "El comentario no puede estar vacío";
    exit;
}

$fechaHora = date('Y-m-d H:i:s');
$estado = 'Pendiente de Revisar'; // Almacenar el estado en una variable

$stmt = $conn->prepare("INSERT INTO Queja (IdUsuario, Comentario, FechaHora, Estado) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $IdUsuario, $comentario, $fechaHora, $estado); // Pasar la variable por referencia

if ($stmt->execute()) {
    echo "¡Queja guardada con éxito!";
} else {
    echo "Error al guardar la queja: " . htmlspecialchars($stmt->error);
}

$stmt->close();
$conn->close();
?>
