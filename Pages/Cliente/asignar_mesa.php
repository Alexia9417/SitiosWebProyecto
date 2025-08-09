<?php
session_start();
include '../../conexion.php';

// Verifica si la sesión está iniciada
if (!isset($_SESSION['IdUsuario'])) {
    header("Location: ../sitio/login.php");
    echo "Error: Sesión no iniciada.";
    exit();
}

// Obtener ID de usuario y número de mesa
$IdUsuario = $_SESSION['IdUsuario'];
$numeroMesa = $_POST['mesa'] ?? null;

if (!$numeroMesa) {
    echo "Error: Número de mesa no proporcionado.";
    exit();
}

// Actualizar la mesa con el IdCliente
$stmt = $conn->prepare("UPDATE Mesa SET IdCliente = ? WHERE Numero = ?");
$stmt->bind_param("ii", $IdUsuario, $numeroMesa);

if ($stmt->execute()) {
    echo "Mesa asignada correctamente.";
} else {
    echo "Error al asignar la mesa.";
}

$stmt->close();
$conn->close();
?>
