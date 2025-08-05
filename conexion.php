<?php
$host = "localhost";       // o 127.0.0.1
$usuario = "root";         // normalmente "root" en XAMPP
$contrasena = "";          // por defecto está vacío en XAMPP
$base_datos = "swprueba"; // Nombre de BD

$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar si hay error de conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
