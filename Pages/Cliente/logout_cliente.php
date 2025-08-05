<?php
session_start();
include '../../conexion.php';

// Verifica si la sesión está iniciada y si el usuario es un cliente
if (isset($_SESSION['IdUsuario']) && $_SESSION['IdTipoUsuario'] == 3) {
    $IdUsuario = $_SESSION['IdUsuario'];
    // Eliminar el IdCliente de la mesa
    $stmt = $conn->prepare("UPDATE Mesa SET IdCliente = NULL WHERE IdCliente = ?");
    $stmt->bind_param("i", $IdUsuario);
    $stmt->execute();
    $stmt->close();
}

// Cerrar la sesión
session_unset();
session_destroy();

// Redirigir a la página de inicio de sesión
header("Location: ../sitio/login.html");
exit();
?>
