<?php
session_start();
include '../../conexion.php';

if (!isset($_SESSION['IdUsuario'])) {
    echo "Usuario no autenticado";
    exit;
}

$IdUsuario = $_SESSION['IdUsuario'];
$estrellas = $_POST['estrellas'] ?? null;
$comentario = $_POST['comentario'] ?? '';

if (!$estrellas || $estrellas < 1 || $estrellas > 5) {
    echo "Calificación inválida";
    exit;
}

$fechaHora = date('Y-m-d H:i:s');

$stmt = $conn->prepare("INSERT INTO Calificacion (IdUsuario, Estrellas, Comentario, FechaHora) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $IdUsuario, $estrellas, $comentario, $fechaHora);

if ($stmt->execute()) {
    echo "¡Calificación guardada con éxito!";
} else {
    echo "Error al guardar calificación";
}
